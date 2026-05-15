<?php

namespace App\Notifications;

use App\Models\ChangeLog;
use App\Models\NotificationEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public NotificationEventType $eventType,
        public Model $subject,
        public array $context,
        public bool $sendEmail,
        public bool $sendInApp,
        public ?ChangeLog $primaryChange = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        if ($this->sendInApp) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->context['subject'] ?? $this->eventType->name)
            ->markdown('emails.system-change', [
                'headline' => $this->context['headline'] ?? $this->eventType->name,
                'intro' => $this->context['intro'] ?? $this->eventType->description,
                'projectName' => $this->context['project_name'] ?? null,
                'clientName' => $this->context['client_name'] ?? null,
                'itemLabel' => $this->context['item_label'] ?? null,
                'changes' => $this->context['changes'] ?? [],
                'actionUrl' => $this->context['action_url'] ?? null,
                'changedAt' => $this->context['changed_at'] ?? now()->format('d.m.Y H:i'),
                'changedBy' => $this->context['changed_by'] ?? null,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event_key' => $this->eventType->key,
            'event_name' => $this->eventType->name,
            'headline' => $this->context['headline'] ?? $this->eventType->name,
            'intro' => $this->context['intro'] ?? null,
            'project_name' => $this->context['project_name'] ?? null,
            'client_name' => $this->context['client_name'] ?? null,
            'item_label' => $this->context['item_label'] ?? null,
            'changes' => $this->context['changes'] ?? [],
            'action_url' => $this->context['action_url'] ?? null,
            'change_log_id' => $this->primaryChange?->getKey(),
            'subject_type' => $this->subject::class,
            'subject_id' => $this->subject->getKey(),
        ];
    }
}
