<!DOCTYPE html>
<html lang="{{ $hotel->locale }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buchung bestätigt · {{ $hotel->name }}</title>
<link href="https://fonts.googleapis.com/css2?family={{ urlencode($brand['font_family']) }}:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root { --green: {{ $brand['green'] }}; --green-dark: {{ $brand['green_dark'] }}; --green-bg: {{ $brand['green_bg'] }}; --ink: {{ $brand['ink'] }}; --ink-soft: #5a5a5a; --line: #e5e5e2; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: '{{ $brand['font_family'] }}', -apple-system, sans-serif; color: var(--ink); background: #fff; line-height: 1.6; }
  .confirm { text-align: center; padding: 72px 16px; max-width: 600px; margin: 0 auto; }
  .mark { width: 80px; height: 80px; margin: 0 auto 28px; border-radius: 50%; background: var(--green); display: flex; align-items: center; justify-content: center; font-size: 36px; color: #fff; font-weight: 700; }
  h1 { font-size: 34px; margin-bottom: 14px; font-weight: 700; letter-spacing: -0.02em; }
  p { color: var(--ink-soft); max-width: 480px; margin: 0 auto 24px; font-size: 15.5px; }
  .ref { display: inline-block; padding: 10px 18px; background: var(--green-bg); border: 1px solid var(--green); font-size: 14px; color: var(--green-dark); font-weight: 700; border-radius: 4px; }
  .totals { margin: 28px auto 0; max-width: 360px; text-align: left; border: 1px solid var(--line); border-radius: 8px; padding: 18px 22px; }
  .totals .row { display: flex; justify-content: space-between; font-size: 14px; padding: 5px 0; }
  .totals .row.grand { border-top: 1px solid var(--line); margin-top: 8px; padding-top: 12px; font-weight: 700; font-size: 16px; }
  .foot { margin-top: 30px; font-size: 13px; color: var(--ink-soft); }
  .foot a { color: var(--green-dark); font-weight: 600; }
</style>
</head>
<body>
  <div class="confirm">
    <div class="mark">✓</div>
    <h1>Vielen Dank!</h1>
    <p>Deine Buchung im {{ $hotel->name }} ist bestätigt. Eine Bestätigung mit allen Details haben wir an <strong>{{ $booking->guest['email'] ?? '' }}</strong> geschickt.</p>
    <div class="ref">Buchungsnummer: {{ $booking->reference }}</div>
    <div class="totals">
      <div class="row"><span>{{ optional($booking->unit)->name ?? 'Unterkunft' }}</span><span>{{ $booking->nights }} Nächte</span></div>
      <div class="row"><span>Unterkunft</span><span>€ {{ number_format((float) $booking->units_total, 2, ',', '.') }}</span></div>
      @if((float) $booking->extras_total > 0)<div class="row"><span>Extras</span><span>€ {{ number_format((float) $booking->extras_total, 2, ',', '.') }}</span></div>@endif
      @if($booking->insurance)<div class="row"><span>Versicherung</span><span>€ {{ number_format((float) $booking->insurance_total, 2, ',', '.') }}</span></div>@endif
      <div class="row grand"><span>Gesamt</span><span>€ {{ number_format((float) $booking->grand_total, 2, ',', '.') }}</span></div>
    </div>
    @if($hotel->support_email)<div class="foot">Fragen? <a href="mailto:{{ $hotel->support_email }}">{{ $hotel->support_email }}</a>@if($hotel->support_phone) · <a href="tel:{{ $hotel->support_phone }}">{{ $hotel->support_phone }}</a>@endif</div>@endif
  </div>
</body>
</html>
