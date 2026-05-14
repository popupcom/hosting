<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
    case OnHold = 'on_hold';
}
