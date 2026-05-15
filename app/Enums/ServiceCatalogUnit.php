<?php

namespace App\Enums;

enum ServiceCatalogUnit: string
{
    case Month = 'month';
    case Year = 'year';
    case Piece = 'piece';
    case FlatRate = 'flat_rate';
}
