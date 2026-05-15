<?php

namespace App\Enums;

enum BillingCadence: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case OneTime = 'one_time';
}
