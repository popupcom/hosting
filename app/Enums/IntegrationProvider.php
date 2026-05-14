<?php

namespace App\Enums;

enum IntegrationProvider: string
{
    case Moco = 'moco';
    case ManageWp = 'managewp';
    case AutoDns = 'autodns';
}
