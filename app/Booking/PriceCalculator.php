<?php

namespace App\Booking;

use App\Enums\ExtraPricingUnit;
use App\Models\BookingExtra;
use App\Models\BookingRate;
use App\Models\BookingUnit;
use App\Models\Hotel;
use Carbon\CarbonInterface;

/**
 * Authoritative, server-side price calculation. The browser may show a live
 * estimate, but the binding total is always recomputed here from database
 * prices so a tampered client payload can never change what is charged.
 *
 * Ports the pricing rules from the original Grünwald booking prototype:
 *  - children 0–2 free, 3–6 −50%, 7–14 −30%, 15+ full price
 *  - rate discount + deposit percentage
 *  - per-person / per-night / one-off extras
 *  - optional travel-cancellation insurance as a % of the stay price
 */
class PriceCalculator
{
    /**
     * @param  array{age:int}|int[]  $childAges
     * @param  array<int|string,int|string>  $extraIds  selected BookingExtra ids
     * @return array<string,mixed>
     */
    public function calculate(
        Hotel $hotel,
        ?BookingUnit $unit,
        ?BookingRate $rate,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        int $adults,
        array $childAges,
        array $extraIds,
        bool $insurance,
    ): array {
        $nights = max(1, (int) $checkIn->diffInDays($checkOut));
        $adults = max(1, $adults);

        $discount = $rate ? (float) $rate->discount_percent / 100 : 0.0;
        $baseNightly = $unit ? (float) $unit->price_per_night : 0.0;
        $unitNightly = round($baseNightly * (1 - $discount), 2);
        $unitsTotal = round($unitNightly * $nights, 2);

        $effectiveGuests = $this->effectiveGuestCount($adults, $childAges);
        $totalGuests = $adults + count($childAges);

        $extrasTotal = 0.0;
        $extraLines = [];
        if ($extraIds !== [] && $unit !== null) {
            $extras = $hotel->extras()->whereIn('id', $extraIds)->where('is_active', true)->get();
            foreach ($extras as $extra) {
                $line = $this->extraLineTotal($extra, $nights, $effectiveGuests, $totalGuests);
                $extrasTotal += $line;
                $extraLines[] = [
                    'id' => $extra->id,
                    'name' => $extra->name,
                    'unit' => $extra->pricing_unit->value,
                    'price' => (float) $extra->price,
                    'total' => $line,
                ];
            }
        }
        $extrasTotal = round($extrasTotal, 2);

        $subtotal = round($unitsTotal + $extrasTotal, 2);
        $insuranceTotal = $insurance ? round($subtotal * (float) $hotel->insurance_rate, 2) : 0.0;
        $grandTotal = round($subtotal + $insuranceTotal, 2);

        $depositPercent = $rate ? (float) $rate->deposit_percent / 100 : 1.0;
        $depositDue = round($grandTotal * $depositPercent, 2);

        return [
            'nights' => $nights,
            'unit_nightly' => $unitNightly,
            'units_total' => $unitsTotal,
            'extras_total' => $extrasTotal,
            'extra_lines' => $extraLines,
            'insurance_total' => $insuranceTotal,
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
            'deposit_due' => $depositDue,
            'city_tax' => round((float) $hotel->city_tax_per_person_night * $totalGuests * $nights, 2),
            'currency' => $hotel->currency,
        ];
    }

    public function childMultiplier(int $age): float
    {
        return match (true) {
            $age <= 2 => 0.0,
            $age <= 6 => 0.5,
            $age <= 14 => 0.7,
            default => 1.0,
        };
    }

    /** Adults count fully; children are weighted by their age multiplier. */
    private function effectiveGuestCount(int $adults, array $childAges): float
    {
        $count = $adults;
        foreach ($childAges as $age) {
            $count += $this->childMultiplier((int) $age);
        }

        return $count;
    }

    private function extraLineTotal(BookingExtra $extra, int $nights, float $effectiveGuests, int $totalGuests): float
    {
        $price = (float) $extra->price;

        return round(match ($extra->pricing_unit) {
            ExtraPricingUnit::PerPersonPerDay => $price * $effectiveGuests * $nights,
            ExtraPricingUnit::PerPersonPerEvening => $price * $effectiveGuests * min($nights, 4),
            ExtraPricingUnit::PerPerson => $price * $totalGuests,
            ExtraPricingUnit::PerNight => $price * $nights,
            ExtraPricingUnit::Once => $price,
        }, 2);
    }
}
