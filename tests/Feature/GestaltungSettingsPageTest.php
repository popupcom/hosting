<?php

namespace Tests\Feature;

use App\Filament\Pages\GestaltungSettingsPage;
use App\Models\DesignSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestaltungSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mount_gestaltung_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(GestaltungSettingsPage::class)
            ->assertOk()
            ->assertFormSet([
                'app_name' => config('app.name'),
            ]);
    }

    public function test_non_admin_cannot_mount_gestaltung_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        Livewire::test(GestaltungSettingsPage::class)->assertForbidden();
    }

    public function test_filament_brand_name_reads_from_design_settings(): void
    {
        $admin = User::factory()->admin()->create();

        DesignSetting::query()->updateOrCreate(
            ['singleton_key' => 'app'],
            ['app_name' => 'Gestaltungs-Test-Marke'],
        );
        DesignSetting::forgetRememberedInstance();

        $this->actingAs($admin);
        $this->get('/admin');

        $this->assertSame(
            'Gestaltungs-Test-Marke',
            Filament::getPanel('admin')->getBrandName(),
        );
    }
}
