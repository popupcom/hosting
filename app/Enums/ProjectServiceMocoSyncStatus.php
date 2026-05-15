<?php

namespace App\Enums;

enum ProjectServiceMocoSyncStatus: string
{
    case NotSynced = 'not_synced';
    case Ready = 'ready';
    case Synced = 'synced';
    case Error = 'error';
}
