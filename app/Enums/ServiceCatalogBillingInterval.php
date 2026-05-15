<?php

namespace App\Enums;

enum ServiceCatalogBillingInterval: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case OneTime = 'one_time';
    case FlatRate = 'flat_rate';
}
