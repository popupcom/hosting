<?php

namespace Tests\Feature\Booking;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Hotels\Pages\CreateHotel;
use App\Filament\Resources\Hotels\Pages\EditHotel;
use App\Filament\Resources\Hotels\Pages\ListHotels;
use App\Filament\Resources\Hotels\RelationManagers\UnitsRelationManager;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HotelAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_hotel_list_page_mounts(): void
    {
        Livewire::test(ListHotels::class)->assertOk();
    }

    public function test_hotel_create_form_mounts_and_saves(): void
    {
        Livewire::test(CreateHotel::class)
            ->assertOk()
            ->fillForm([
                'name' => 'Seehotel Demo',
                'slug' => 'seehotel-demo',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $hotel = Hotel::where('slug', 'seehotel-demo')->firstOrFail();
        // afterCreate() bootstraps the two default rates so the tenant is bookable.
        $this->assertSame(2, $hotel->rates()->count());
    }

    public function test_units_relation_manager_mounts(): void
    {
        $hotel = Hotel::create(['name' => 'RM Hotel', 'slug' => 'rm-hotel', 'status' => 'active']);

        Livewire::test(UnitsRelationManager::class, [
            'ownerRecord' => $hotel,
            'pageClass' => EditHotel::class,
        ])->assertOk();
    }

    public function test_booking_list_page_mounts(): void
    {
        Livewire::test(ListBookings::class)->assertOk();
    }
}
