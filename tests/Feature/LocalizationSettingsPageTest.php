<?php

namespace Tests\Feature;

use App\Enums\ReminderStatus;
use App\Filament\Pages\LocalizationSettingsPage;
use App\Filament\Support\GermanLabels;
use App\Models\DesignSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocalizationSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mount_localization_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(LocalizationSettingsPage::class)
            ->assertOk()
            ->assertFormSet([
                'ui_locale' => 'de',
            ]);
    }

    public function test_non_admin_cannot_mount_localization_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        Livewire::test(LocalizationSettingsPage::class)->assertForbidden();
    }

    public function test_admin_can_save_ui_locale(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(LocalizationSettingsPage::class)
            ->set('data.ui_locale', 'en')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('design_settings', [
            'singleton_key' => 'app',
            'ui_locale' => 'en',
        ]);

        DesignSetting::forgetRememberedInstance();
        $this->assertSame('en', DesignSetting::current()->effectiveUiLocale());
    }

    public function test_admin_can_save_ui_label_overrides(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(LocalizationSettingsPage::class)
            ->fillForm([
                'ui_label_overrides' => [
                    'todo_statuses' => [
                        'pending' => 'Offen (angepasst)',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        DesignSetting::forgetRememberedInstance();

        $this->assertSame(
            'Offen (angepasst)',
            GermanLabels::todoStatus(ReminderStatus::Pending),
        );
    }
}
