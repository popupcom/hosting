<?php

/**
 * Zentrale Designwerte für das Filament-Admin-Panel (popup Hosting Overview).
 *
 * Anpassung:
 * - Primär hier oder per .env (siehe Schlüssel unten)
 * - Logo: optional, Datei unter `public/`; Pfad in `logo.path` (z. B. per .env `FILAMENT_BRAND_LOGO`).
 * - CSS-Variablen werden aus `tokens` generiert (--popup-{key-in-kebab})
 */

return [

    'logo' => [
        'path' => env('FILAMENT_BRAND_LOGO') ?: 'images/brand/popup-header-logo.png',
        'height' => env('FILAMENT_BRAND_LOGO_HEIGHT', '2.75rem'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament-Farbpaletten (HEX) → Panel::colors()
    |--------------------------------------------------------------------------
    */

    'colors' => [
        'primary' => env('FILAMENT_COLOR_PRIMARY', '#d6002a'),
        'success' => env('FILAMENT_COLOR_SUCCESS', '#0d6e4d'),
        'warning' => env('FILAMENT_COLOR_WARNING', '#b45309'),
        'danger' => env('FILAMENT_COLOR_DANGER', '#b42318'),
        'info' => env('FILAMENT_COLOR_INFO', '#1d5a96'),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI-Tokens → CSS-Variablen :root (--popup-*)
    |--------------------------------------------------------------------------
    |
    | Werte als gültige CSS-Werte (Farben: HEX/RGB/RGBA, Abstände: px/rem).
    |
    */

    'tokens' => [

        /* Flächen / Text */
        'body_bg' => env('FILAMENT_UI_BODY_BG', '#fafafa'),
        'body_text' => env('FILAMENT_UI_BODY_TEXT', '#18181b'),
        'body_bg_dark' => env('FILAMENT_UI_BODY_BG_DARK', '#09090b'),
        'body_text_dark' => env('FILAMENT_UI_BODY_TEXT_DARK', '#fafafa'),

        'sidebar_bg' => env('FILAMENT_UI_SIDEBAR_BG', '#ffffff'),
        'sidebar_border' => env('FILAMENT_UI_SIDEBAR_BORDER', 'rgba(228, 228, 231, 0.85)'),
        'sidebar_bg_dark' => env('FILAMENT_UI_SIDEBAR_BG_DARK', '#18181b'),
        'sidebar_border_dark' => env('FILAMENT_UI_SIDEBAR_BORDER_DARK', 'rgba(255, 255, 255, 0.1)'),

        'topbar_bg' => env('FILAMENT_UI_TOPBAR_BG', 'rgba(255, 255, 255, 0.92)'),
        'topbar_border' => env('FILAMENT_UI_TOPBAR_BORDER', 'rgba(228, 228, 231, 0.85)'),
        'topbar_bg_dark' => env('FILAMENT_UI_TOPBAR_BG_DARK', 'rgba(24, 24, 27, 0.92)'),
        'topbar_border_dark' => env('FILAMENT_UI_TOPBAR_BORDER_DARK', 'rgba(255, 255, 255, 0.1)'),

        /* Cards / Sections */
        'section_radius' => env('FILAMENT_UI_SECTION_RADIUS', '1rem'),
        'section_padding' => env('FILAMENT_UI_SECTION_PADDING', '2rem'),
        'section_ring' => env('FILAMENT_UI_SECTION_RING', 'rgba(9, 9, 11, 0.05)'),
        'section_ring_dark' => env('FILAMENT_UI_SECTION_RING_DARK', 'rgba(255, 255, 255, 0.1)'),

        /* Seite / Raster */
        'page_content_gap_y' => env('FILAMENT_UI_PAGE_GAP_Y', '2.5rem'),

        /* Buttons */
        'button_radius' => env('FILAMENT_UI_BUTTON_RADIUS', '9999px'),
        'button_padding_x' => env('FILAMENT_UI_BUTTON_PAD_X', '1.25rem'),
        'button_padding_y' => env('FILAMENT_UI_BUTTON_PAD_Y', '0.625rem'),
        'button_sm_padding_x' => env('FILAMENT_UI_BUTTON_SM_PAD_X', '1rem'),
        'button_sm_padding_y' => env('FILAMENT_UI_BUTTON_SM_PAD_Y', '0.5rem'),
        'icon_button_radius' => env('FILAMENT_UI_ICON_BUTTON_RADIUS', '0.75rem'),

        /* Tabellen */
        'table_header_pad_x' => env('FILAMENT_UI_TABLE_HEADER_PAD_X', '1.25rem'),
        'table_header_pad_x_lg' => env('FILAMENT_UI_TABLE_HEADER_PAD_X_LG', '2rem'),
        'table_header_pad_y' => env('FILAMENT_UI_TABLE_HEADER_PAD_Y', '1rem'),
        'table_header_text' => env('FILAMENT_UI_TABLE_HEADER_TEXT', '#71717a'),
        'table_header_text_dark' => env('FILAMENT_UI_TABLE_HEADER_TEXT_DARK', '#a1a1aa'),
        'table_cell_size' => env('FILAMENT_UI_TABLE_CELL_SIZE', '0.875rem'),
        'table_cell_leading' => env('FILAMENT_UI_TABLE_CELL_LEADING', '1.625'),

        /* Dashboard-Stats */
        'stats_radius' => env('FILAMENT_UI_STATS_RADIUS', '1rem'),
        'stats_padding' => env('FILAMENT_UI_STATS_PADDING', '2rem'),
        'stats_value_size' => env('FILAMENT_UI_STATS_VALUE_SIZE', '1.5rem'),
        'stats_value_size_lg' => env('FILAMENT_UI_STATS_VALUE_SIZE_LG', '1.875rem'),

        /* Badges */
        'badge_radius' => env('FILAMENT_UI_BADGE_RADIUS', '9999px'),
        'badge_pad_x' => env('FILAMENT_UI_BADGE_PAD_X', '0.75rem'),
        'badge_pad_y' => env('FILAMENT_UI_BADGE_PAD_Y', '0.25rem'),
        'badge_font_size' => env('FILAMENT_UI_BADGE_FONT_SIZE', '0.6875rem'),
        'badge_font_weight' => env('FILAMENT_UI_BADGE_FONT_WEIGHT', '600'),
        'badge_tracking' => env('FILAMENT_UI_BADGE_TRACKING', '0.06em'),

        /* Login / Simple Layout */
        'login_gradient_from' => env('FILAMENT_UI_LOGIN_GRAD_FROM', '#f4f4f5'),
        'login_gradient_to' => env('FILAMENT_UI_LOGIN_GRAD_TO', '#fafafa'),
        'login_gradient_from_dark' => env('FILAMENT_UI_LOGIN_GRAD_FROM_DARK', '#09090b'),
        'login_gradient_to_dark' => env('FILAMENT_UI_LOGIN_GRAD_TO_DARK', '#18181b'),
        'simple_main_pad_y' => env('FILAMENT_UI_SIMPLE_MAIN_PAD_Y', '2.5rem'),
        'simple_main_pad_y_sm' => env('FILAMENT_UI_SIMPLE_MAIN_PAD_Y_SM', '3.5rem'),
        'simple_page_gap_y' => env('FILAMENT_UI_SIMPLE_PAGE_GAP_Y', '2rem'),

        /* Auth-Intro */
        'auth_intro_max_width' => env('FILAMENT_UI_AUTH_INTRO_MAX_W', '28rem'),
        'auth_intro_text' => env('FILAMENT_UI_AUTH_INTRO_TEXT', '#52525b'),
        'auth_intro_text_dark' => env('FILAMENT_UI_AUTH_INTRO_TEXT_DARK', '#a1a1aa'),
    ],

];
