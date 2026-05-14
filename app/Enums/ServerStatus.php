<?php

namespace App\Enums;

enum ServerStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
}
