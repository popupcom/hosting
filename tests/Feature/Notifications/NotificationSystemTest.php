<?php

namespace Tests\Feature\Notifications;

use App\Enums\ClientStatus;
use App\Enums\NotificationEventKey;
use App\Enums\ProjectStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Models\Client;
use App\Models\NotificationEventType;
use App\Models\NotificationGroup;
use App\Models\NotificationGroupEventSetting;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ServiceCatalogItem;
use App\Models\User;
use App\Notifications\SystemChangeNotification;
use App\Services\Notifications\NotificationRecipientResolver;
use Database\Seeders\NotificationSystemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_logs_price_change_on_update(): void
    {
        $service = $this->createProjectService();

        $service->update(['custom_sales_price' => 150.00]);

        $this->assertDatabaseHas('change_logs', [
            'changeable_type' => ProjectService::class,
            'changeable_id' => $service->getKey(),
            'field_name' => 'custom_sales_price',
        ]);
    }

    public function test_group_member_receives_notification_when_service_created(): void
    {
        Notification::fake();
        $this->seed(NotificationSystemSeeder::class);

        $user = User::factory()->create([
            'is_active' => true,
            'email' => 'billing@example.test',
        ]);
        $group = NotificationGroup::query()->where('name', 'Buchhaltung')->firstOrFail();
        $group->users()->attach($user);

        $event = NotificationEventType::query()
            ->where('key', NotificationEventKey::ProjectServiceCreated->value)
            ->firstOrFail();

        NotificationGroupEventSetting::query()
            ->where('notification_group_id', $group->id)
            ->where('notification_event_type_id', $event->id)
            ->update(['is_enabled' => true, 'send_email' => true, 'send_in_app' => true]);

        $this->createProjectService();

        Notification::assertSentTo($user, SystemChangeNotification::class);
    }

    public function test_recipient_resolver_respects_user_opt_out(): void
    {
        $this->seed(NotificationSystemSeeder::class);

        $user = User::factory()->create(['is_active' => true]);
        $group = NotificationGroup::query()->where('name', 'Buchhaltung')->firstOrFail();
        $group->users()->attach($user);

        $eventKey = NotificationEventKey::ProjectServiceCreated->value;
        $event = NotificationEventType::query()->where('key', $eventKey)->firstOrFail();

        $user->notificationPreferences()->create([
            'notification_event_type_id' => $event->id,
            'is_enabled' => false,
            'email_enabled' => false,
            'in_app_enabled' => false,
        ]);

        $recipients = NotificationRecipientResolver::resolve($eventKey);

        $this->assertTrue($recipients->isEmpty());
    }

    private function createProjectService(): ProjectService
    {
        $client = Client::query()->create([
            'name' => 'Test Kundin',
            'slug' => 'test-kundin-'.uniqid(),
            'status' => ClientStatus::Active,
        ]);

        $project = Project::query()->create([
            'client_id' => $client->id,
            'name' => 'Test Projekt',
            'url' => 'https://example.test',
            'status' => ProjectStatus::Active,
        ]);

        $catalog = ServiceCatalogItem::query()->create([
            'name' => 'Hosting Basic',
            'slug' => 'hosting-basic-'.uniqid(),
            'category' => ServiceCatalogCategory::Hosting,
            'unit' => ServiceCatalogUnit::Month,
            'billing_interval' => ServiceCatalogBillingInterval::Monthly,
            'is_active' => true,
        ]);

        return ProjectService::query()->create([
            'project_id' => $project->id,
            'service_catalog_item_id' => $catalog->id,
            'name_snapshot' => $catalog->name,
            'quantity' => 1,
            'status' => 'active',
            'moco_sync_status' => 'not_synced',
        ]);
    }
}
