<?php

namespace App\Enums;

enum CostLineItemType: string
{
    case Domain = 'domain';
    case Hosting = 'hosting';
    case License = 'license';
    case SupportPackage = 'support_package';
    case AdditionalService = 'additional_service';
}
