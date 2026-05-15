<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case WordPressCore = 'wordpress_core';
    case PluginUpdate = 'plugin_update';
    case ThemeUpdate = 'theme_update';
    case Backup = 'backup';
    case PerformanceCheck = 'performance_check';
    case SecurityCheck = 'security_check';
    case SupportPackageExcelSnapshot = 'support_package_excel_snapshot';
}
