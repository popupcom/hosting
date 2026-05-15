<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deduplication window (minutes)
    |--------------------------------------------------------------------------
    |
    | Identische Benachrichtigungen an dieselbe Person werden in diesem
    | Zeitfenster nicht erneut versendet.
    |
    */
    'dedupe_minutes' => (int) env('NOTIFICATION_DEDUPE_MINUTES', 60),

    /*
    |--------------------------------------------------------------------------
    | n8n Webhook (optional, zukünftige Erweiterung)
    |--------------------------------------------------------------------------
    */
    'n8n_webhook_url' => env('NOTIFICATION_N8N_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Felder, die auf ProjectService protokolliert werden
    |--------------------------------------------------------------------------
    */
    'project_service_log_fields' => [
        'status',
        'custom_sales_price',
        'custom_cost_price',
        'custom_billing_interval',
        'billing_interval_snapshot',
        'billing_group_id',
        'end_date',
        'cancellation_date',
        'do_not_renew',
        'moco_sync_status',
        'quantity',
        'custom_quantity',
    ],

];
