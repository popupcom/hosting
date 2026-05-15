<?php

namespace App\Filament\Pages;

use App\Models\DashboardPreference;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class ManagementDashboard extends BaseDashboard
{
    protected static ?string $title = 'Management-Zusammenfassung';

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        $user = Filament::auth()->user();

        if ($user === null) {
            return parent::getWidgets();
        }

        $widgets = DashboardPreference::forUser($user)->visibleWidgets();

        return [
            AccountWidget::class,
            ...$widgets,
        ];
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboardSettings')
                ->label('Dashboard-Einstellungen')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->url(DashboardSettingsPage::getUrl()),
        ];
    }

    /**
     * @return array<string, ?int>|int
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
            '2xl' => 3,
        ];
    }
}
