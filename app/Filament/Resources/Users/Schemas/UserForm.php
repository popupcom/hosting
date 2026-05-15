<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Benutzer:in')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('role')
                            ->label('Rolle')
                            ->required()
                            ->maxLength(64)
                            ->datalist(array_keys(UserRole::labels()))
                            ->helperText('Vordefiniert: admin, mitarbeiter, buchhaltung, technik, support — oder eigener Schlüssel.'),
                        Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true),
                        Toggle::make('is_admin')
                            ->label('Administrator (voller Zugriff)')
                            ->helperText('Alternativ Rolle „admin“ vergeben.'),
                        TextInput::make('password')
                            ->label('Passwort')
                            ->password()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Select::make('notificationGroups')
                            ->label('Benachrichtigungsgruppen')
                            ->relationship('notificationGroups', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
