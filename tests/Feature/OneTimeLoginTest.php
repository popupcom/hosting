<?php

namespace Tests\Feature;

use App\Filament\Auth\RequestOneTimeLogin;
use App\Filament\Pages\ManagementDashboard;
use App\Mail\OneTimeLoginMail;
use App\Models\User;
use App\Services\OneTimeLoginLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class OneTimeLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_magic_link_logs_in_and_invalidates_token(): void
    {
        $user = User::factory()->create();

        $service = app(OneTimeLoginLinkService::class);
        $url = $service->createSignedUrlForUser($user);

        $this->assertDatabaseHas('one_time_login_tokens', [
            'user_id' => $user->id,
        ]);

        $this->get($url)->assertRedirect(ManagementDashboard::getUrl());

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('one_time_login_tokens', [
            'user_id' => $user->id,
        ]);
    }

    public function test_request_page_sends_mail_for_known_user(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'known@example.com']);

        Livewire::test(RequestOneTimeLogin::class)
            ->set('data.email', 'known@example.com')
            ->call('request');

        Mail::assertSent(OneTimeLoginMail::class, function (OneTimeLoginMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
    }

    public function test_request_page_does_not_leak_unknown_email(): void
    {
        Mail::fake();

        Livewire::test(RequestOneTimeLogin::class)
            ->set('data.email', 'nobody@example.com')
            ->call('request');

        Mail::assertNothingSent();
    }
}
