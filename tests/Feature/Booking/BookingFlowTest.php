<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\HotelStatus;
use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function hotel(array $overrides = []): Hotel
    {
        $hotel = Hotel::create(array_merge([
            'name' => 'Test Resort',
            'slug' => 'test-resort',
            'status' => HotelStatus::Active,
            'currency' => 'EUR',
            'insurance_rate' => 0.055,
            'city_tax_per_person_night' => 3.50,
            'tax_percent' => 10,
        ], $overrides));

        $hotel->rates()->create([
            'key' => 'saver', 'label' => 'Spar', 'discount_percent' => 10,
            'deposit_percent' => 100, 'is_default' => true,
        ]);
        $hotel->rates()->create([
            'key' => 'flex', 'label' => 'Standard', 'discount_percent' => 0,
            'deposit_percent' => 50,
        ]);
        $hotel->units()->create([
            'name' => 'Studio S', 'slug' => 'studio-s', 'category' => 'Studios',
            'max_pax' => 2, 'price_per_night' => 200,
        ]);
        $hotel->extras()->create([
            'name' => 'Garage', 'slug' => 'garage', 'price' => 12, 'pricing_unit' => 'per_night',
        ]);

        return $hotel;
    }

    public function test_active_hotel_renders_flow(): void
    {
        $hotel = $this->hotel();

        $this->get("/book/{$hotel->slug}")
            ->assertOk()
            ->assertSee('Test Resort')
            ->assertSee('Studio S');

        $this->assertDatabaseHas('booking_events', [
            'hotel_id' => $hotel->id,
            'type' => 'page_view',
        ]);
    }

    public function test_paused_hotel_is_not_bookable(): void
    {
        $hotel = $this->hotel(['slug' => 'paused', 'status' => HotelStatus::Paused]);

        $this->get("/book/{$hotel->slug}")->assertNotFound();
    }

    public function test_events_are_captured(): void
    {
        $hotel = $this->hotel();

        $this->postJson("/book/{$hotel->slug}/events", [
            'session_id' => 'sess-1',
            'type' => 'select_unit',
            'step' => 2,
            'value' => 1000,
        ])->assertAccepted();

        $event = BookingEvent::where('type', 'select_unit')->firstOrFail();
        $this->assertSame('sess-1', $event->session_id);
        $this->assertNotNull($event->ip_hash);
    }

    public function test_booking_is_created_with_server_authoritative_totals(): void
    {
        $hotel = $this->hotel();
        $unit = $hotel->units()->first();
        $saver = $hotel->rates()->where('key', 'saver')->first();
        $extra = $hotel->extras()->first();

        $response = $this->postJson("/book/{$hotel->slug}/bookings", [
            'session_id' => 'sess-2',
            'check_in' => '2026-07-15',
            'check_out' => '2026-07-20',
            'adults' => 2,
            'child_ages' => [5],
            'unit_id' => $unit->id,
            'rate_id' => $saver->id,
            'extras' => [$extra->id],
            'insurance' => true,
            'guest' => [
                'first_name' => 'Maria',
                'last_name' => 'Mustermann',
                'email' => 'maria@example.at',
            ],
        ])->assertCreated();

        $booking = Booking::firstOrFail();
        // units: 200 * 0.9 * 5 = 900; extra garage per_night 12*5 = 60
        $this->assertSame('900.00', $booking->units_total);
        $this->assertSame('60.00', $booking->extras_total);
        // insurance: (900+60)*0.055 = 52.80; grand = 1012.80; saver deposit = full
        $this->assertSame('52.80', $booking->insurance_total);
        $this->assertSame('1012.80', $booking->grand_total);
        $this->assertSame('1012.80', $booking->deposit_due);
        $this->assertSame(BookingStatus::PendingPayment, $booking->status);
        $this->assertSame($hotel->currency, $booking->currency);

        $response->assertJsonStructure(['reference', 'amount_due', 'complete_url']);
    }

    public function test_completing_payment_confirms_booking_and_fires_purchase(): void
    {
        $hotel = $this->hotel();
        $unit = $hotel->units()->first();
        $flex = $hotel->rates()->where('key', 'flex')->first();

        $create = $this->postJson("/book/{$hotel->slug}/bookings", [
            'session_id' => 'sess-3',
            'check_in' => '2026-07-15',
            'check_out' => '2026-07-17',
            'adults' => 2,
            'unit_id' => $unit->id,
            'rate_id' => $flex->id,
            'guest' => [
                'first_name' => 'Max', 'last_name' => 'Muster', 'email' => 'max@example.at',
            ],
        ])->assertCreated();

        $reference = $create->json('reference');

        // flex: deposit is 50% of 400 = 200
        $this->assertEquals(200, $create->json('amount_due'));

        $this->postJson("/book/{$hotel->slug}/bookings/{$reference}/complete")
            ->assertOk()
            ->assertJsonStructure(['reference', 'redirect']);

        $booking = Booking::where('reference', $reference)->firstOrFail();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertNotNull($booking->confirmed_at);

        $this->assertDatabaseHas('booking_events', [
            'booking_id' => $booking->id,
            'type' => 'purchase',
        ]);

        $this->get("/book/{$hotel->slug}/confirmation/{$reference}")
            ->assertOk()
            ->assertSee($reference);
    }

    public function test_booking_rejects_invalid_guest(): void
    {
        $hotel = $this->hotel();
        $unit = $hotel->units()->first();
        $saver = $hotel->rates()->where('key', 'saver')->first();

        $this->postJson("/book/{$hotel->slug}/bookings", [
            'check_in' => '2026-07-15',
            'check_out' => '2026-07-20',
            'adults' => 2,
            'unit_id' => $unit->id,
            'rate_id' => $saver->id,
            'guest' => ['first_name' => 'NoEmail'],
        ])->assertStatus(422);
    }
}
