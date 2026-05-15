@php
    $sanitizeHex = static function (?string $v, string $fallback): string {
        $s = (string) $v;

        return preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $s) ? $s : $fallback;
    };
    $sanitizeRadius = static function (?string $v, string $fallback): string {
        $s = (string) $v;

        return preg_match('/^[a-z0-9.\-\s%(),]+$/i', $s) && strlen($s) <= 64 ? $s : $fallback;
    };
    $primary = $sanitizeHex($primary ?? null, '#d6002a');
    $secondary = $sanitizeHex($secondary ?? null, '#52525b');
    $accent = $sanitizeHex($accent ?? null, '#1d5a96');
    $background = $sanitizeHex($background ?? null, '#fafafa');
    $text = $sanitizeHex($text ?? null, '#18181b');
    $radius = $sanitizeRadius($radius ?? null, '1rem');
@endphp
<div
    style="box-sizing:border-box;padding:1.25rem;border-radius:{{ $radius }};background:{{ $background }};color:{{ $text }};border:1px solid {{ $secondary }};max-width:42rem;">
    <div style="font-weight:600;margin-bottom:0.75rem;font-size:1rem;">Beispiel-Card</div>
    <p style="margin:0 0 1rem;font-size:0.875rem;line-height:1.5;opacity:0.9;">Kurzer Fließtext zur Darstellung von Kontrast und Zeilenabstand.</p>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;margin-bottom:1rem;">
        <button type="button"
            style="cursor:default;border:0;border-radius:{{ $radius }};background:{{ $primary }};color:#fff;padding:0.5rem 1rem;font-size:0.875rem;font-weight:600;">
            Primär-Button
        </button>
        <span
            style="display:inline-block;border-radius:{{ $radius }};background:{{ $accent }};color:#fff;padding:0.25rem 0.75rem;font-size:0.6875rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;">
            Badge
        </span>
    </div>
    <div style="border:1px solid {{ $secondary }};border-radius:{{ $radius }};overflow:hidden;font-size:0.8125rem;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:rgba(0,0,0,0.04);text-align:left;">
                    <th style="padding:0.5rem 0.75rem;font-weight:600;">Spalte A</th>
                    <th style="padding:0.5rem 0.75rem;font-weight:600;">Spalte B</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-top:1px solid {{ $secondary }};">
                    <td style="padding:0.5rem 0.75rem;">Alpha</td>
                    <td style="padding:0.5rem 0.75rem;">100</td>
                </tr>
                <tr style="border-top:1px solid {{ $secondary }};">
                    <td style="padding:0.5rem 0.75rem;">Beta</td>
                    <td style="padding:0.5rem 0.75rem;">200</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
