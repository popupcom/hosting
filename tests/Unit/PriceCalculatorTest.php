<?php

namespace Tests\Unit;

use App\Booking\PriceCalculator;
use App\Models\BookingRate;
use App\Models\BookingUnit;
use App\Models\Hotel;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private function hotel(): Hotel
    {
        return new Hotel([
            'currency' => 'EUR',
            'insurance_rate' => 0.055,
            'city_tax_per_person_night' => 3.50,
        ]);
    }

    public function test_child_multiplier_brackets(): void
    {
        $calc = new PriceCalculator;

        $this->assertSame(0.0, $calc->childMultiplier(2));
        $this->assertSame(0.5, $calc->childMultiplier(6));
        $this->assertSame(0.7, $calc->childMultiplier(14));
        $this->assertSame(1.0, $calc->childMultiplier(15));
    }

    public function test_saver_rate_applies_discount_and_full_deposit(): void
    {
        $calc = new PriceCalculator;
        $unit = new BookingUnit(['price_per_night' => 200]);
        $rate = new BookingRate(['discount_percent' => 10, 'deposit_percent' => 100]);

        $totals = $calc->calculate(
            $this->hotel(), $unit, $rate,
            Carbon::parse('2026-07-15'), Carbon::parse('2026-07-20'),
            2, [], [], false,
        );

        // 200 * 0.9 * 5 nights = 900
        $this->assertSame(5, $totals['nights']);
        $this->assertSame(900.0, $totals['units_total']);
        $this->assertSame(900.0, $totals['grand_total']);
        $this->assertSame(900.0, $totals['deposit_due']);
    }

    public function test_flex_rate_half_deposit_with_insurance(): void
    {
        $calc = new PriceCalculator;
        $unit = new BookingUnit(['price_per_night' => 100]);
        $rate = new BookingRate(['discount_percent' => 0, 'deposit_percent' => 50]);

        $totals = $calc->calculate(
            $this->hotel(), $unit, $rate,
            Carbon::parse('2026-07-15'), Carbon::parse('2026-07-17'),
            2, [], [], true,
        );

        // units 100*2=200, insurance 200*0.055=11, grand 211, deposit 105.5
        $this->assertSame(200.0, $totals['units_total']);
        $this->assertSame(11.0, $totals['insurance_total']);
        $this->assertSame(211.0, $totals['grand_total']);
        $this->assertSame(105.5, $totals['deposit_due']);
    }
}
