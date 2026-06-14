<?php

namespace App\Http\Controllers\Booking;

use App\Booking\PriceCalculator;
use App\Booking\TrackingScriptBuilder;
use App\Enums\BookingEventType;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\BookingRate;
use App\Models\BookingUnit;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BookingFlowController extends Controller
{
    public function __construct(private readonly PriceCalculator $calculator) {}

    /** Render the white-label booking flow for a tenant. */
    public function show(Hotel $hotel)
    {
        abort_unless($hotel->isBookable(), Response::HTTP_NOT_FOUND);

        $hotel->load([
            'units' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->orderBy('id'),
            'rates' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->orderBy('id'),
            'extras' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->orderBy('id'),
        ]);

        $sessionId = (string) Str::uuid();

        // First-party page-view event, independent of any ad-platform tag.
        $this->record($hotel, $sessionId, BookingEventType::PageView, null, request());

        $tracking = new TrackingScriptBuilder($hotel);

        return view('booking.flow', [
            'hotel' => $hotel,
            'brand' => $hotel->brand(),
            'sessionId' => $sessionId,
            'config' => $this->flowConfig($hotel),
            'trackingHead' => $tracking->headTags(),
            'trackingConfig' => $tracking->clientConfig(),
        ]);
    }

    /** Persist a funnel event from the browser (fire-and-forget). */
    public function storeEvent(Request $request, Hotel $hotel): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string'],
            'step' => ['nullable', 'integer', 'min:1', 'max:10'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'payload' => ['nullable', 'array'],
            'attribution' => ['nullable', 'array'],
        ]);

        $type = BookingEventType::tryFrom($data['type']);
        if ($type === null) {
            return response()->json(['ok' => false], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->record(
            $hotel,
            $data['session_id'],
            $type,
            $data['value'] ?? null,
            $request,
            $data['step'] ?? null,
            $data['payload'] ?? null,
            $data['attribution'] ?? null,
        );

        return response()->json(['ok' => true], Response::HTTP_ACCEPTED);
    }

    /** Live server-authoritative quote for the current selection. */
    public function quote(Request $request, Hotel $hotel): JsonResponse
    {
        [$unit, $rate, $data] = $this->resolveSelection($request, $hotel);
        $totals = $this->calculator->calculate(
            $hotel,
            $unit,
            $rate,
            Carbon::parse($data['check_in']),
            Carbon::parse($data['check_out']),
            $data['adults'],
            $data['child_ages'],
            $data['extras'],
            $data['insurance'],
        );

        return response()->json($totals);
    }

    /** Create a pending booking and hand back a payment reference. */
    public function store(Request $request, Hotel $hotel): JsonResponse
    {
        abort_unless($hotel->isBookable(), Response::HTTP_NOT_FOUND);

        [$unit, $rate, $data] = $this->resolveSelection($request, $hotel);

        if ($unit === null || $rate === null) {
            throw ValidationException::withMessages([
                'unit' => 'Bitte Unterkunft und Rate wählen.',
            ]);
        }

        $guest = $request->validate([
            'guest.first_name' => ['required', 'string', 'max:255'],
            'guest.last_name' => ['required', 'string', 'max:255'],
            'guest.email' => ['required', 'email', 'max:255'],
            'guest.phone' => ['nullable', 'string', 'max:64'],
            'guest.address' => ['nullable', 'string', 'max:255'],
            'guest.zip' => ['nullable', 'string', 'max:16'],
            'guest.city' => ['nullable', 'string', 'max:255'],
            'guest.country' => ['nullable', 'string', 'max:255'],
            'guest.notes' => ['nullable', 'string', 'max:1000'],
        ])['guest'];

        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);

        $totals = $this->calculator->calculate(
            $hotel,
            $unit,
            $rate,
            $checkIn,
            $checkOut,
            $data['adults'],
            $data['child_ages'],
            $data['extras'],
            $data['insurance'],
        );

        $booking = $hotel->bookings()->create([
            'reference' => $this->reference($hotel),
            'status' => BookingStatus::PendingPayment,
            'booking_unit_id' => $unit->id,
            'booking_rate_id' => $rate->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => $totals['nights'],
            'adults' => $data['adults'],
            'children' => count($data['child_ages']),
            'child_ages' => $data['child_ages'],
            'board' => $request->input('board'),
            'package' => $request->input('package'),
            'extras' => $totals['extra_lines'],
            'insurance' => $data['insurance'],
            'guest' => $guest,
            'units_total' => $totals['units_total'],
            'extras_total' => $totals['extras_total'],
            'insurance_total' => $totals['insurance_total'],
            'grand_total' => $totals['grand_total'],
            'deposit_due' => $totals['deposit_due'],
            'currency' => $hotel->currency,
            'payment_method' => $request->input('payment_method', 'card'),
            'payment_status' => 'pending',
            'session_id' => $data['session_id'],
            'locale' => $hotel->locale,
            'attribution' => $request->input('attribution'),
        ]);

        $this->record($hotel, $data['session_id'], BookingEventType::AddPaymentInfo, $totals['grand_total'], $request, 5, null, null, $booking);

        // Payment is stubbed for the kickoff slice: in production this hands off
        // to the hobex hosted payment page and finalises via webhook + the
        // Casablanca reservation call. See docs/booking-saas.md.
        $amountDue = $rate->deposit_percent < 100 ? $totals['deposit_due'] : $totals['grand_total'];

        return response()->json([
            'reference' => $booking->reference,
            'amount_due' => $amountDue,
            'grand_total' => $totals['grand_total'],
            'complete_url' => route('booking.complete', ['hotel' => $hotel, 'reference' => $booking->reference]),
        ], Response::HTTP_CREATED);
    }

    /** Stand-in payment confirmation (replaces the hobex webhook for the demo). */
    public function complete(Request $request, Hotel $hotel, string $reference): JsonResponse
    {
        $booking = $hotel->bookings()->where('reference', $reference)->firstOrFail();

        if ($booking->status === BookingStatus::PendingPayment) {
            $booking->update([
                'status' => BookingStatus::Confirmed,
                'payment_status' => 'paid',
                'payment_reference' => 'SIM-'.Str::upper(Str::random(10)),
                'confirmed_at' => now(),
            ]);

            $this->record($hotel, $booking->session_id, BookingEventType::Purchase, $booking->grand_total, $request, 6, [
                'transaction_id' => $booking->reference,
            ], $booking->attribution, $booking);
        }

        return response()->json([
            'reference' => $booking->reference,
            'redirect' => route('booking.confirmation', ['hotel' => $hotel, 'reference' => $booking->reference]),
        ]);
    }

    public function confirmation(Hotel $hotel, string $reference)
    {
        $booking = $hotel->bookings()->where('reference', $reference)->firstOrFail();

        return view('booking.confirmation', [
            'hotel' => $hotel,
            'brand' => $hotel->brand(),
            'booking' => $booking,
            'trackingConfig' => (new TrackingScriptBuilder($hotel))->clientConfig(),
        ]);
    }

    /**
     * Normalise + validate the stay selection shared by quote() and store().
     *
     * @return array{0: ?BookingUnit, 1: ?BookingRate, 2: array<string,mixed>}
     */
    private function resolveSelection(Request $request, Hotel $hotel): array
    {
        $data = $request->validate([
            'session_id' => ['nullable', 'string', 'max:64'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'child_ages' => ['nullable', 'array'],
            'child_ages.*' => ['integer', 'min:0', 'max:17'],
            'unit_id' => ['nullable', 'integer'],
            'rate_id' => ['nullable', 'integer'],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['integer'],
            'insurance' => ['nullable', 'boolean'],
        ]);

        $unit = isset($data['unit_id'])
            ? $hotel->units()->whereKey($data['unit_id'])->first()
            : null;
        $rate = isset($data['rate_id'])
            ? $hotel->rates()->whereKey($data['rate_id'])->first()
            : null;

        return [$unit, $rate, [
            'session_id' => $data['session_id'] ?? (string) Str::uuid(),
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'adults' => $data['adults'],
            'child_ages' => array_map('intval', $data['child_ages'] ?? []),
            'extras' => array_map('intval', $data['extras'] ?? []),
            'insurance' => (bool) ($data['insurance'] ?? false),
        ]];
    }

    private function record(
        Hotel $hotel,
        string $sessionId,
        BookingEventType $type,
        ?float $value,
        Request $request,
        ?int $step = null,
        ?array $payload = null,
        ?array $attribution = null,
        ?Booking $booking = null,
    ): BookingEvent {
        return $hotel->events()->create([
            'booking_id' => $booking?->id,
            'session_id' => $sessionId,
            'type' => $type,
            'step' => $step,
            'value' => $value,
            'currency' => $value !== null ? $hotel->currency : null,
            'payload' => $payload,
            'attribution' => $attribution,
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().'|'.$hotel->id) : null,
        ]);
    }

    private function reference(Hotel $hotel): string
    {
        $prefix = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $hotel->slug) ?: 'BK', 0, 2));

        do {
            $reference = sprintf('%s-%s-%04d', $prefix, now()->year, random_int(1000, 9999));
        } while ($hotel->bookings()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Serialisable config consumed by the booking flow front-end.
     *
     * @return array<string,mixed>
     */
    private function flowConfig(Hotel $hotel): array
    {
        return [
            'hotel' => [
                'name' => $hotel->name,
                'location' => $hotel->location,
                'currency' => $hotel->currency,
                'locale' => $hotel->locale,
                'cityTax' => (float) $hotel->city_tax_per_person_night,
                'insuranceRate' => (float) $hotel->insurance_rate,
                'taxPercent' => $hotel->tax_percent,
                'supportPhone' => $hotel->support_phone,
                'legal' => $hotel->legal,
            ],
            'units' => $hotel->units->map(fn (BookingUnit $u) => [
                'id' => $u->id,
                'category' => $u->category,
                'name' => $u->name,
                'maxPax' => $u->max_pax,
                'size' => $u->size,
                'rooms' => $u->rooms,
                'desc' => $u->description,
                'image' => $u->image_url,
                'badge' => $u->badge,
                'pricePerNight' => (float) $u->price_per_night,
            ])->values(),
            'rates' => $hotel->rates->map(fn (BookingRate $r) => [
                'id' => $r->id,
                'key' => $r->key,
                'label' => $r->label,
                'sublabel' => $r->sublabel,
                'discount' => (float) $r->discount_percent / 100,
                'depositPercent' => (float) $r->deposit_percent / 100,
                'rules' => $r->rules ?? [],
                'isDefault' => $r->is_default,
            ])->values(),
            'extras' => $hotel->extras->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'desc' => $e->description,
                'price' => (float) $e->price,
                'unit' => $e->pricing_unit->value,
                'unitLabel' => $e->pricing_unit->label(),
            ])->values(),
            'endpoints' => [
                'events' => route('booking.events', ['hotel' => $hotel]),
                'quote' => route('booking.quote', ['hotel' => $hotel]),
                'store' => route('booking.store', ['hotel' => $hotel]),
            ],
        ];
    }
}
