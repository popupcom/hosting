<?php

namespace App\Enums;

enum MocoSyncStatus: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
