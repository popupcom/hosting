<?php

namespace App\Enums;

enum PopupApplicationStatus: string
{
    case Draft = 'draft';
    case Live = 'live';
    case Archived = 'archived';
}
