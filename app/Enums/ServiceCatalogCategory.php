<?php

namespace App\Enums;

enum ServiceCatalogCategory: string
{
    case Hosting = 'hosting';
    case Domain = 'domain';
    case Ssl = 'ssl';
    case License = 'license';
    case SupportPackage = 'support_package';
    case QrCode = 'qr_code';
    case MailExchange = 'mail_exchange';
    case Storage = 'storage';
    case ToolSaas = 'tool_saas';
    case Monitoring = 'monitoring';
    case AdditionalService = 'additional_service';
}
