<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use App\Models\Hotel;
use Filament\Resources\Pages\CreateRecord;

class CreateHotel extends CreateRecord
{
    protected static string $resource = HotelResource::class;

    /** Bootstrap a new tenant with the two default rates so it is bookable immediately. */
    protected function afterCreate(): void
    {
        /** @var Hotel $hotel */
        $hotel = $this->record;

        if ($hotel->rates()->doesntExist()) {
            $hotel->rates()->createMany([
                ['key' => 'saver', 'label' => 'Spar-Vorteil', 'sublabel' => 'Nicht erstattbar · −10%', 'discount_percent' => 10, 'deposit_percent' => 100, 'is_default' => true, 'sort' => 1,
                    'rules' => ['100% Anzahlung bei Buchung', 'Keine kostenfreie Stornierung']],
                ['key' => 'flex', 'label' => 'Standard', 'sublabel' => 'Flexibel', 'discount_percent' => 0, 'deposit_percent' => 50, 'sort' => 2,
                    'rules' => ['50% Anzahlung als Buchungsgarantie', 'Stornobedingungen laut Richtlinie']],
            ]);
        }
    }
}
