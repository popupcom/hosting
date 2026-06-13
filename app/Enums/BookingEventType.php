<?php

namespace App\Enums;

/**
 * Funnel events captured server-side for every booking session. This is the
 * core of the "trackable booking flow" promise: the data is owned by the
 * platform (first-party) and can be replayed to Google Ads / GA4 / Meta
 * including via server-to-server conversion APIs.
 */
enum BookingEventType: string
{
    case PageView = 'page_view';
    case BeginCheckout = 'begin_checkout';
    case ViewUnits = 'view_units';
    case SelectUnit = 'select_unit';
    case SelectRate = 'select_rate';
    case AddExtra = 'add_extra';
    case AddInsurance = 'add_insurance';
    case EnterGuestData = 'enter_guest_data';
    case AddPaymentInfo = 'add_payment_info';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Seitenaufruf',
            self::BeginCheckout => 'Checkout gestartet',
            self::ViewUnits => 'Unterkünfte angesehen',
            self::SelectUnit => 'Unterkunft gewählt',
            self::SelectRate => 'Rate gewählt',
            self::AddExtra => 'Extra hinzugefügt',
            self::AddInsurance => 'Versicherung gewählt',
            self::EnterGuestData => 'Gastdaten erfasst',
            self::AddPaymentInfo => 'Zahlungsdaten erfasst',
            self::Purchase => 'Buchung abgeschlossen',
        };
    }

    /** Ordered funnel steps used by the conversion-funnel widget. */
    public static function funnel(): array
    {
        return [
            self::PageView,
            self::SelectUnit,
            self::AddPaymentInfo,
            self::Purchase,
        ];
    }
}
