<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrNew(['email' => 'office@popup.at']);
        $user->name = 'Popup Office';
        $user->is_admin = true;
        $user->role = 'admin';
        $user->is_active = true;
        $user->email_verified_at = now();

        if (! $user->exists) {
            $user->password = 'netlotion';
        }

        $user->save();

        $this->call([
            ServiceCatalogSeeder::class,
            NotificationSystemSeeder::class,
        ]);
    }
}
