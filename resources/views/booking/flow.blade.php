<!DOCTYPE html>
<html lang="{{ $hotel->locale }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('Buchung') }} · {{ $hotel->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={{ urlencode($brand['font_family']) }}:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
{!! $trackingHead !!}
<style>
  :root {
    --green: {{ $brand['green'] }};
    --green-dark: {{ $brand['green_dark'] }};
    --green-darker: {{ $brand['green_darker'] }};
    --green-soft: {{ $brand['green_bg'] }};
    --green-bg: {{ $brand['green_bg'] }};
    --bg: #ffffff; --bg-soft: #fafaf8;
    --ink: {{ $brand['ink'] }}; --ink-soft: #5a5a5a; --ink-muted: #888888;
    --line: #e5e5e2; --line-soft: #f0f0ed;
    --accent: {{ $brand['accent'] }}; --error: #b8474e;
    --brand-font: '{{ $brand['font_family'] }}', -apple-system, BlinkMacSystemFont, sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--bg); color: var(--ink); font-family: var(--brand-font); font-size: 15px; line-height: 1.6; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
  a { color: inherit; }
  img { max-width: 100%; display: block; }
  header { background: white; border-bottom: 1px solid var(--line); padding: 18px 0; }
  .container { max-width: 1240px; margin: 0 auto; padding: 0 24px; }
  .header-inner { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
  .logo { font-weight: 700; font-size: 18px; letter-spacing: 0.02em; line-height: 1; }
  .logo small { display: block; font-size: 9.5px; letter-spacing: 0.3em; color: var(--ink-muted); text-transform: uppercase; margin-top: 5px; font-weight: 500; }
  .header-meta { font-size: 13px; color: var(--ink-soft); text-align: right; }
  .header-meta a { color: var(--green-dark); text-decoration: none; font-weight: 600; }
  .stepper { background: var(--bg-soft); border-bottom: 1px solid var(--line); }
  .stepper-inner { display: flex; max-width: 1240px; margin: 0 auto; padding: 18px 24px; }
  .step { flex: 1; display: flex; align-items: center; gap: 10px; font-size: 11.5px; color: var(--ink-muted); padding-right: 12px; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; min-width: 0; }
  .step span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .step-num { width: 26px; height: 26px; border-radius: 50%; background: white; border: 1.5px solid var(--line); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; transition: all .3s; flex-shrink: 0; }
  .step.active .step-num { background: var(--green); color: white; border-color: var(--green); }
  .step.done .step-num { background: var(--green-dark); color: white; border-color: var(--green-dark); }
  .step.active { color: var(--ink); } .step.done { color: var(--green-dark); }
  .step:not(:last-child)::after { content: ''; flex: 1; height: 1px; background: var(--line); margin-left: 6px; min-width: 8px; }
  .stepper-mobile { display: none; padding: 14px 20px; background: var(--bg-soft); border-bottom: 1px solid var(--line); }
  .stepper-mobile-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
  .stepper-mobile-label { font-size: 14px; font-weight: 700; }
  .stepper-mobile-counter { font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); font-weight: 700; }
  .stepper-mobile-bar { height: 4px; background: var(--line); border-radius: 2px; overflow: hidden; }
  .stepper-mobile-bar-fill { height: 100%; background: var(--green); transition: width .3s; width: 20%; border-radius: 2px; }
  main { padding: 48px 0 80px; min-height: 70vh; }
  .layout { display: grid; grid-template-columns: 1fr 380px; gap: 56px; align-items: start; }
  h1.page-title { font-size: 38px; font-weight: 700; letter-spacing: -0.02em; line-height: 1.12; margin-bottom: 14px; }
  h1.page-title span { color: var(--green); display: block; font-weight: 400; font-style: italic; }
  .page-subtitle { color: var(--ink-soft); margin-bottom: 36px; max-width: 560px; font-size: 16px; }
  h2.section-title { font-size: 21px; font-weight: 700; margin: 36px 0 16px; letter-spacing: -0.01em; }
  h2.section-title:first-child { margin-top: 0; }
  h2.section-title small { font-size: 10.5px; color: var(--green-dark); letter-spacing: 0.18em; text-transform: uppercase; display: block; margin-bottom: 6px; font-weight: 700; }
  .field-group { display: grid; gap: 18px; margin-bottom: 28px; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .field { display: flex; flex-direction: column; gap: 8px; }
  .field label { font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); font-weight: 700; }
  .field input, .field select { padding: 13px 14px; background: white; border: 1px solid var(--line); border-radius: 4px; font-family: inherit; font-size: 15px; color: var(--ink); transition: all .15s; width: 100%; }
  .field input:focus, .field select:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(122,154,90,0.12); }
  .stepper-input { display: flex; align-items: center; border: 1px solid var(--line); background: white; border-radius: 4px; }
  .stepper-input button { background: transparent; border: 0; width: 42px; height: 46px; font-size: 18px; cursor: pointer; color: var(--green-dark); font-weight: 600; }
  .stepper-input button:hover { background: var(--green-bg); }
  .stepper-input .value { flex: 1; text-align: center; font-size: 15px; font-weight: 600; }
  .child-ages { display: grid; gap: 8px; padding: 16px; background: var(--green-bg); border-radius: 6px; margin-top: 6px; }
  .child-age-row { display: grid; grid-template-columns: 90px 1fr; gap: 12px; align-items: center; }
  .child-age-row label { font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--green-darker); font-weight: 700; }
  .child-age-row select { padding: 9px 11px; background: white; border: 1px solid var(--line); border-radius: 4px; font-size: 14px; width: 100%; }
  .age-hint { font-size: 11.5px; color: var(--ink-soft); margin-top: 8px; }
  .units-category { margin: 32px 0 14px; } .units-category:first-child { margin-top: 0; }
  .units-category h3 { font-size: 10.5px; color: var(--green-dark); letter-spacing: 0.2em; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
  .units-category p { font-size: 15px; color: var(--ink-soft); }
  .units { display: grid; gap: 14px; }
  .unit-card { border: 1px solid var(--line); background: white; display: grid; grid-template-columns: 220px 1fr 180px; transition: all .2s; cursor: pointer; overflow: hidden; border-radius: 6px; }
  .unit-card:hover { border-color: var(--green); box-shadow: 0 8px 28px -14px rgba(77,107,51,0.25); }
  .unit-card.selected { border-color: var(--green); border-width: 2px; }
  .unit-card.disabled { opacity: 0.45; cursor: not-allowed; pointer-events: none; }
  .unit-image-wrap { width: 220px; height: 100%; min-height: 200px; position: relative; overflow: hidden; background: var(--green-bg); }
  .unit-image-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
  .unit-card:hover .unit-image-wrap img { transform: scale(1.04); }
  .unit-image-wrap .badge { position: absolute; top: 12px; left: 12px; padding: 5px 10px; background: rgba(26,26,26,0.88); color: white; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; font-weight: 700; border-radius: 3px; z-index: 2; }
  .unit-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 7px; min-width: 0; }
  .unit-name { font-size: 20px; font-weight: 700; letter-spacing: -0.01em; line-height: 1.2; }
  .unit-meta { font-size: 13px; color: var(--ink-soft); display: flex; gap: 12px; flex-wrap: wrap; }
  .unit-rooms { font-size: 12px; color: var(--ink-muted); }
  .unit-desc { font-size: 13.5px; color: var(--ink-soft); margin-top: 4px; }
  .unit-rates { display: grid; gap: 6px; margin-top: 10px; }
  .rate-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: var(--bg-soft); border: 1px solid var(--line); font-size: 12.5px; color: var(--ink-soft); cursor: pointer; transition: all .15s; border-radius: 4px; flex-wrap: wrap; }
  .rate-pill:hover { border-color: var(--green); background: white; }
  .rate-pill.active { border-color: var(--green); background: var(--green-bg); color: var(--ink); border-width: 2px; padding: 7px 11px; }
  .rate-pill strong { color: var(--green-dark); font-weight: 700; }
  .unit-price-block { padding: 20px 22px; display: flex; flex-direction: column; justify-content: center; align-items: flex-end; text-align: right; border-left: 1px solid var(--line); }
  .unit-price { font-size: 24px; line-height: 1.1; font-weight: 700; letter-spacing: -0.01em; }
  .unit-price small { font-size: 11px; color: var(--ink-muted); display: block; margin-top: 5px; font-weight: 400; }
  .unit-select { margin-top: 14px; padding: 9px 16px; background: white; border: 1px solid var(--green-dark); color: var(--green-dark); font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; cursor: pointer; font-weight: 700; border-radius: 3px; }
  .unit-card.selected .unit-select { background: var(--green-dark); color: white; }
  .extras { display: grid; gap: 10px; }
  .extra-item { display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center; padding: 16px 18px; border: 1px solid var(--line); background: white; cursor: pointer; transition: all .15s; border-radius: 6px; }
  .extra-item:hover { border-color: var(--green); }
  .extra-item.selected { border-color: var(--green); border-width: 2px; padding: 15px 17px; background: var(--green-bg); }
  .checkbox { width: 22px; height: 22px; border: 1.5px solid var(--ink-muted); display: flex; align-items: center; justify-content: center; border-radius: 3px; flex-shrink: 0; }
  .extra-item.selected .checkbox { background: var(--green); border-color: var(--green); }
  .extra-item.selected .checkbox::after { content: '✓'; color: white; font-size: 13px; font-weight: 700; }
  .extra-info h4 { font-size: 15.5px; font-weight: 700; margin-bottom: 3px; }
  .extra-info p { font-size: 13px; color: var(--ink-soft); }
  .extra-price { font-size: 17px; color: var(--green-dark); text-align: right; font-weight: 700; white-space: nowrap; }
  .extra-price small { font-size: 10.5px; color: var(--ink-muted); display: block; margin-top: 2px; font-weight: 400; }
  .insurance-card { border: 2px solid var(--green); background: linear-gradient(135deg, white 0%, var(--green-bg) 100%); padding: 24px; margin-bottom: 16px; cursor: pointer; transition: all .2s; position: relative; overflow: hidden; border-radius: 8px; }
  .insurance-card.selected { background: white; border-color: var(--green-dark); }
  .insurance-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px; }
  .insurance-icon { width: 44px; height: 44px; border-radius: 50%; background: var(--green); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; }
  .insurance-card.selected .insurance-icon { background: var(--green-dark); }
  .insurance-title { font-size: 18px; font-weight: 700; margin-bottom: 3px; }
  .insurance-subtitle { font-size: 10.5px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--green-dark); font-weight: 700; }
  .insurance-card p { font-size: 14px; color: var(--ink-soft); margin-bottom: 12px; }
  .insurance-foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; padding-top: 14px; border-top: 1px solid rgba(77,107,51,0.15); margin-top: 12px; flex-wrap: wrap; }
  .insurance-price { font-size: 24px; color: var(--green-dark); font-weight: 700; }
  .insurance-price small { font-size: 11px; color: var(--ink-muted); display: block; font-weight: 400; }
  .rate-summary { background: var(--green-bg); border: 1px solid var(--green); padding: 20px 22px; margin-bottom: 22px; border-radius: 6px; }
  .rate-summary h4 { font-size: 16px; color: var(--green-darker); font-weight: 700; margin-bottom: 10px; }
  .rate-summary ul { margin-left: 18px; font-size: 13px; }
  .rate-summary li { margin-bottom: 5px; }
  .rate-summary-note { margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(77,107,51,0.2); font-size: 13px; }
  .summary { background: var(--ink); color: white; padding: 28px; position: sticky; top: 20px; border-radius: 8px; }
  .summary h3 { font-size: 20px; margin-bottom: 4px; font-weight: 700; line-height: 1.25; }
  .summary-section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.12); }
  .summary-section:last-of-type { border-bottom: 0; }
  .summary-row { display: flex; justify-content: space-between; font-size: 13.5px; margin-bottom: 7px; gap: 12px; }
  .summary-row.muted { color: rgba(255,255,255,0.65); }
  .summary-row.small { font-size: 12px; }
  .summary-label { font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--green); margin-bottom: 8px; font-weight: 700; }
  .summary-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 18px; gap: 12px; }
  .summary-total .label { font-size: 11.5px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--green); font-weight: 700; }
  .summary-total .amount { font-size: 30px; font-weight: 700; letter-spacing: -0.02em; }
  .summary-fine { font-size: 11px; color: rgba(255,255,255,0.55); margin-top: 14px; line-height: 1.55; }
  .summary-fine strong { color: white; }
  .summary-badge { display: inline-block; padding: 4px 9px; background: var(--green); color: white; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; font-weight: 700; border-radius: 3px; }
  .actions { display: flex; gap: 12px; margin-top: 36px; flex-wrap: wrap; }
  .btn { padding: 15px 28px; font-family: inherit; font-size: 11.5px; letter-spacing: 0.16em; text-transform: uppercase; font-weight: 700; cursor: pointer; transition: all .15s; border: 1.5px solid var(--green-dark); border-radius: 4px; }
  .btn-primary { background: var(--green-dark); color: white; }
  .btn-primary:hover { background: var(--green-darker); border-color: var(--green-darker); }
  .btn-secondary { background: white; color: var(--green-dark); }
  .btn:disabled { opacity: 0.35; cursor: not-allowed; }
  .payment-methods { display: grid; gap: 10px; margin: 22px 0; }
  .payment-option { display: grid; grid-template-columns: auto 1fr auto; gap: 14px; align-items: center; padding: 16px 18px; border: 1px solid var(--line); background: white; cursor: pointer; border-radius: 6px; transition: all .15s; }
  .payment-option:hover { border-color: var(--green); }
  .payment-option.selected { border-color: var(--green); border-width: 2px; padding: 15px 17px; background: var(--green-bg); }
  .radio { width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid var(--ink-muted); position: relative; flex-shrink: 0; }
  .payment-option.selected .radio { border-color: var(--green); }
  .payment-option.selected .radio::after { content: ''; position: absolute; top: 3px; left: 3px; right: 3px; bottom: 3px; border-radius: 50%; background: var(--green); }
  .pay-logo { font-size: 14.5px; font-weight: 700; }
  .pay-desc { font-size: 12px; color: var(--ink-soft); margin-top: 2px; }
  .pay-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; background: #0d1b2a; color: white; font-size: 9.5px; letter-spacing: 0.1em; font-weight: 700; border-radius: 3px; white-space: nowrap; }
  .pay-screen { background: #f4f4f6; min-height: 560px; display: flex; align-items: center; justify-content: center; padding: 24px; border-radius: 8px; }
  .pay-card { background: white; max-width: 480px; width: 100%; padding: 32px 28px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border-radius: 8px; }
  .pay-card .sum { font-size: 28px; font-weight: 700; color: #0d1b2a; margin: 14px 0 22px; letter-spacing: -0.02em; }
  .pay-card .pay-field { margin-bottom: 12px; }
  .pay-card .pay-field label { display: block; font-size: 11.5px; color: #555; margin-bottom: 4px; font-weight: 600; }
  .pay-card .pay-field input { width: 100%; padding: 11px 13px; border: 1px solid #d0d0d6; border-radius: 4px; font-size: 14px; }
  .pay-submit { width: 100%; padding: 14px; background: var(--green-dark); color: white; border: 0; font-size: 13.5px; font-weight: 700; letter-spacing: 0.05em; cursor: pointer; border-radius: 4px; margin-top: 14px; }
  .pay-secure { display: flex; align-items: center; gap: 8px; font-size: 11px; color: #777; margin-top: 16px; justify-content: center; }
  .alert { padding: 13px 16px; background: var(--green-bg); border-left: 3px solid var(--green); font-size: 12.5px; color: var(--ink-soft); margin-bottom: 22px; border-radius: 0 4px 4px 0; }
  .alert strong { color: var(--green-darker); }
  .pill { display: inline-block; padding: 4px 11px; background: var(--green-soft); color: var(--green-darker); font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 700; border-radius: 3px; }
  .pill-warn { background: #fef0d3; color: #855a14; }
  .hidden { display: none !important; }
  .dots::after { content: '…'; animation: dots 1.4s infinite; }
  @keyframes dots { 0%,100% { opacity:.3; } 50% { opacity:1; } }
  @media (max-width: 1024px) { .layout { grid-template-columns: 1fr; gap: 32px; } .summary { position: static; } }
  @media (max-width: 768px) { .stepper { display: none; } .stepper-mobile { display: block; } .unit-card { grid-template-columns: 200px 1fr; } .unit-image-wrap { width: 200px; } .unit-price-block { grid-column: 1 / -1; border-left: 0; border-top: 1px solid var(--line); flex-direction: row; align-items: center; justify-content: space-between; } .unit-select { margin-top: 0; } }
  @media (max-width: 600px) { h1.page-title { font-size: 28px; } h1.page-title span { display: inline; } .field-row { grid-template-columns: 1fr; } .unit-card { grid-template-columns: 1fr; } .unit-image-wrap { width: 100%; height: 200px; } .actions { flex-direction: column-reverse; } .actions .btn { width: 100%; } }
</style>
</head>
<body>
<header>
  <div class="container header-inner">
    <div class="logo">{{ $hotel->name }}@if($hotel->location)<small>{{ $hotel->location }}</small>@endif</div>
    @if($hotel->support_phone)<div class="header-meta">{{ __('Hilfe?') }} <a href="tel:{{ $hotel->support_phone }}">{{ $hotel->support_phone }}</a></div>@endif
  </div>
</header>

<div class="stepper"><div class="stepper-inner" id="stepper">
  <div class="step active" data-step="1"><div class="step-num">1</div><span>Reisezeitraum</span></div>
  <div class="step" data-step="2"><div class="step-num">2</div><span>Unterkunft</span></div>
  <div class="step" data-step="3"><div class="step-num">3</div><span>Extras</span></div>
  <div class="step" data-step="4"><div class="step-num">4</div><span>Gastdaten</span></div>
  <div class="step" data-step="5"><div class="step-num">5</div><span>Zahlung</span></div>
</div></div>
<div class="stepper-mobile" id="stepper-mobile">
  <div class="stepper-mobile-top"><div class="stepper-mobile-label" id="sm-label">Reisezeitraum</div><div class="stepper-mobile-counter" id="sm-counter">Schritt 1 / 5</div></div>
  <div class="stepper-mobile-bar"><div class="stepper-mobile-bar-fill" id="sm-fill"></div></div>
</div>

<main><div class="container">

  <section id="step-1" class="step-panel"><div class="layout"><div>
    <h1 class="page-title">Plane deinen <span>Aufenthalt.</span></h1>
    <p class="page-subtitle">Wähle deinen Zeitraum und die Anzahl deiner Gäste. Wir zeigen dir verfügbare Unterkünfte in Echtzeit.</p>
    <div class="field-group">
      <div class="field-row">
        <div class="field"><label>Anreise</label><input type="date" id="checkin"></div>
        <div class="field"><label>Abreise</label><input type="date" id="checkout"></div>
      </div>
      <div class="field-row">
        <div class="field"><label>Erwachsene</label><div class="stepper-input"><button type="button" onclick="updateGuests('adults',-1)">−</button><span class="value" id="adults-val">2</span><button type="button" onclick="updateGuests('adults',1)">+</button></div></div>
        <div class="field"><label>Kinder</label><div class="stepper-input"><button type="button" onclick="updateGuests('children',-1)">−</button><span class="value" id="children-val">0</span><button type="button" onclick="updateGuests('children',1)">+</button></div></div>
      </div>
      <div id="children-ages-wrap" class="hidden"><div class="field"><label>Alter der Kinder</label><div class="child-ages" id="child-ages-list"></div><div class="age-hint">0–2 Jahre kostenfrei · 3–6 Jahre 50% · 7–14 Jahre 30% Ermäßigung · ab 15 Erwachsenenpreis</div></div></div>
    </div>
    <div class="actions"><button class="btn btn-primary" onclick="goTo(2)">Verfügbarkeit prüfen →</button></div>
  </div><aside class="summary" id="summary-1">
    <div class="summary-label">Dein Aufenthalt</div><h3 id="sum-dates">–</h3><div style="height:14px"></div>
    <div class="summary-section"><div class="summary-row muted"><span id="sum-nights">–</span><span id="sum-guests">–</span></div></div>
    <div class="summary-fine">Preise inkl. Steuern. Ortstaxe wird separat vor Ort erhoben.</div>
  </aside></div></section>

  <section id="step-2" class="step-panel hidden"><div class="layout"><div>
    <h1 class="page-title">Verfügbare <span>Unterkünfte.</span></h1>
    <p class="page-subtitle">Wähle deine Wunschunterkunft und eine Rate.</p>
    <div id="units-list"></div>
  </div><aside class="summary" id="summary-2"></aside></div>
  <div class="actions"><button class="btn btn-secondary" onclick="goTo(1)">← Zurück</button><button class="btn btn-primary" id="btn-step-2" onclick="goTo(3)" disabled>Weiter zu Extras →</button></div></section>

  <section id="step-3" class="step-panel hidden"><div class="layout"><div>
    <h1 class="page-title">Veredle deinen <span>Aufenthalt.</span></h1>
    <p class="page-subtitle">Schütze deine Buchung und ergänze Zusatzleistungen. Alles optional.</p>
    <h2 class="section-title"><small>Sorglos buchen</small>Reise- &amp; Stornoversicherung</h2>
    <div class="insurance-card" id="insurance-card" onclick="toggleInsurance()">
      <div class="insurance-head"><div class="insurance-icon">⛨</div><div><div class="insurance-title">Komfort-Schutz mit Storno-Plus</div><div class="insurance-subtitle">Reiseversicherung</div></div></div>
      <p>Bei Krankheit, Unfall oder unerwarteten Ereignissen bist du finanziell abgesichert. Die Versicherung übernimmt die Stornogebühren.</p>
      <div class="insurance-foot"><div><div class="insurance-price" id="insurance-price">€ 0,00</div><small>einmalig, ca. 5,5% des Reisepreises</small></div></div>
    </div>
    <h2 class="section-title"><small>Zusatzleistungen</small>Mehr Genuss &amp; Komfort</h2>
    <div class="extras" id="extras-list"></div>
  </div><aside class="summary" id="summary-3"></aside></div>
  <div class="actions"><button class="btn btn-secondary" onclick="goTo(2)">← Zurück</button><button class="btn btn-primary" onclick="goTo(4)">Weiter zu Gastdaten →</button></div></section>

  <section id="step-4" class="step-panel hidden"><div class="layout"><div>
    <h1 class="page-title">Fast geschafft. <span>Deine Daten.</span></h1>
    <p class="page-subtitle">Wir brauchen nur das Nötigste. Alle Daten werden DSGVO-konform verarbeitet.</p>
    <div class="field-group">
      <div class="field-row"><div class="field"><label>Vorname</label><input type="text" id="g-first" placeholder="Maria"></div><div class="field"><label>Nachname</label><input type="text" id="g-last" placeholder="Mustermann"></div></div>
      <div class="field-row"><div class="field"><label>E-Mail</label><input type="email" id="g-email" placeholder="maria@example.at"></div><div class="field"><label>Telefon</label><input type="tel" id="g-phone" placeholder="+43 660 1234567"></div></div>
      <div class="field"><label>Adresse</label><input type="text" id="g-address" placeholder="Straße, Hausnummer"></div>
      <div class="field-row"><div class="field"><label>PLZ</label><input type="text" id="g-zip" placeholder="6020"></div><div class="field"><label>Ort</label><input type="text" id="g-city" placeholder="Innsbruck"></div></div>
      <div class="field"><label>Anmerkungen (optional)</label><input type="text" id="g-notes" placeholder="z.B. Späte Anreise, Allergien…"></div>
    </div>
    <div id="guest-error" class="alert hidden" style="border-left-color: var(--error);"></div>
  </div><aside class="summary" id="summary-4"></aside></div>
  <div class="actions"><button class="btn btn-secondary" onclick="goTo(3)">← Zurück</button><button class="btn btn-primary" onclick="goTo(5)">Weiter zur Zahlung →</button></div></section>

  <section id="step-5" class="step-panel hidden"><div class="layout"><div>
    <h1 class="page-title">Sicher <span>bezahlen.</span></h1>
    <p class="page-subtitle">Deine Buchung wird erst nach erfolgreicher Zahlung verbindlich.</p>
    <div class="rate-summary" id="rate-summary"></div>
    <div class="payment-methods" id="payment-methods">
      <div class="payment-option selected" data-method="card"><div class="radio"></div><div><div class="pay-logo">Kredit- / Debitkarte</div><div class="pay-desc">Visa, Mastercard, Amex</div></div><div class="pay-badge">gesichert</div></div>
      <div class="payment-option" data-method="eps"><div class="radio"></div><div><div class="pay-logo">EPS-Überweisung</div><div class="pay-desc">Sofort-Überweisung</div></div><div class="pay-badge">gesichert</div></div>
      <div class="payment-option" data-method="paypal"><div class="radio"></div><div><div class="pay-logo">PayPal</div><div class="pay-desc">Mit PayPal-Konto bezahlen</div></div><div class="pay-badge">gesichert</div></div>
    </div>
  </div><aside class="summary" id="summary-5"></aside></div>
  <div class="actions"><button class="btn btn-secondary" onclick="goTo(4)">← Zurück</button><button class="btn btn-primary" id="btn-book" onclick="submitBooking()">Jetzt verbindlich buchen →</button></div></section>

  <section id="step-pay" class="step-panel hidden"><div class="pay-screen"><div class="pay-card">
    <div style="font-size:12.5px;color:#555">Bestellreferenz: <strong id="pay-ref">–</strong></div>
    <div class="sum" id="pay-amount">€ 0,00</div>
    <div class="pay-field"><label>Kartennummer</label><input type="text" value="4242 4242 4242 4242"></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><div class="pay-field"><label>Gültig bis</label><input type="text" value="12/28"></div><div class="pay-field"><label>CVV</label><input type="text" value="123"></div></div>
    <div class="pay-field"><label>Karteninhaber</label><input type="text" value="MARIA MUSTERMANN"></div>
    <button class="pay-submit" id="pay-submit" onclick="completePayment()">Jetzt bezahlen</button>
    <div class="pay-secure">🔒 SSL · PCI DSS · 3-D Secure 2</div>
  </div></div></section>

</div></main>

<script>
window.BOOKING_CONFIG = @json($config);
window.TRACKING = @json($trackingConfig);
const SESSION_ID = @json($sessionId);
const CSRF = document.querySelector('meta[name=csrf-token]').content;
</script>
<script>
(function(){
  const CFG = window.BOOKING_CONFIG;
  const CUR = { EUR: '€', CHF: 'CHF', USD: '$' }[CFG.hotel.currency] || CFG.hotel.currency;
  const STEP_NAMES = ['Reisezeitraum','Unterkunft','Extras','Gastdaten','Zahlung'];
  const defaultRate = (CFG.rates.find(r => r.isDefault) || CFG.rates[0] || null);

  function plusDays(n){ const d=new Date(); d.setDate(d.getDate()+n); return d.toISOString().slice(0,10); }
  const state = {
    step:1, checkin: plusDays(32), checkout: plusDays(37), adults:2, children:0, childAges:[],
    selectedUnitId:null, selectedRateId: defaultRate ? defaultRate.id : null,
    selectedExtras:new Set(), insurance:false, paymentMethod:'card', bookingRef:null, completeUrl:null
  };

  const $ = id => document.getElementById(id);
  function nights(){ const a=new Date(state.checkin), b=new Date(state.checkout); return Math.max(1, Math.round((b-a)/86400000)); }
  function fmtDate(d){ return new Date(d).toLocaleDateString(CFG.hotel.locale+'-AT', {day:'2-digit',month:'2-digit',year:'numeric'}); }
  function fmtPrice(n){ return CUR+' '+Number(n).toFixed(2).replace('.',','); }
  function childMult(a){ if(a<=2) return 0; if(a<=6) return 0.5; if(a<=14) return 0.7; return 1; }
  function totalGuests(){ return state.adults + state.children; }
  function effGuests(){ let n=state.adults; state.childAges.forEach(a=>n+=childMult(a)); return n; }
  function unit(){ return CFG.units.find(u=>u.id===state.selectedUnitId) || null; }
  function rate(){ return CFG.rates.find(r=>r.id===state.selectedRateId) || null; }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function calc(){
    const N = nights(); const u = unit(); const r = rate();
    const disc = r ? r.discount : 0;
    const nightly = u ? u.pricePerNight*(1-disc) : 0;
    const unitsTotal = nightly*N;
    let extrasTotal = 0;
    if(u){ state.selectedExtras.forEach(id=>{
      const e = CFG.extras.find(x=>x.id===id); if(!e) return;
      if(e.unit==='per_person_per_day') extrasTotal += e.price*effGuests()*N;
      else if(e.unit==='per_person_per_evening') extrasTotal += e.price*effGuests()*Math.min(N,4);
      else if(e.unit==='per_person') extrasTotal += e.price*totalGuests();
      else if(e.unit==='per_night') extrasTotal += e.price*N;
      else extrasTotal += e.price;
    }); }
    const subtotal = unitsTotal+extrasTotal;
    const insurance = state.insurance ? Math.round(subtotal*CFG.hotel.insuranceRate*100)/100 : 0;
    const grand = subtotal+insurance;
    const depositPct = r ? r.depositPercent : 1;
    return { N, nightly, unitsTotal, extrasTotal, insurance, subtotal, grand, deposit: Math.round(grand*depositPct*100)/100 };
  }

  // ---- Tracking ----
  function track(type, extra){
    extra = extra || {};
    fetch(CFG.endpoints.events, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body: JSON.stringify({ session_id: SESSION_ID, type, step: extra.step||null, value: extra.value||null, payload: extra.payload||null }) }).catch(()=>{});
  }
  function fireConversion(ref, value){
    try {
      if(window.gtag){
        if(TRACKING.googleAds && TRACKING.googleAdsLabel){ gtag('event','conversion',{ send_to: TRACKING.googleAds+'/'+TRACKING.googleAdsLabel, value, currency: CFG.hotel.currency, transaction_id: ref }); }
        if(TRACKING.ga4){ gtag('event','purchase',{ transaction_id: ref, value, currency: CFG.hotel.currency }); }
      }
      if(window.fbq){ fbq('track','Purchase',{ value, currency: CFG.hotel.currency }); }
    } catch(e){}
  }

  // ---- Navigation ----
  function goTo(n){
    state.step=n;
    document.querySelectorAll('.step-panel').forEach(p=>p.classList.add('hidden'));
    $('step-'+n).classList.remove('hidden');
    document.querySelectorAll('#stepper .step').forEach(s=>{ const sn=+s.dataset.step; s.classList.remove('active','done'); if(sn<n)s.classList.add('done'); if(sn===n)s.classList.add('active'); });
    $('sm-label').textContent = STEP_NAMES[n-1]; $('sm-counter').textContent='Schritt '+n+' / 5'; $('sm-fill').style.width=(n*20)+'%';
    if(n===2){ renderUnits(); renderSummary('summary-2'); track('view_units',{step:2}); }
    if(n===3){ renderExtras(); renderInsurance(); renderSummary('summary-3'); }
    if(n===4){ renderSummary('summary-4'); track('enter_guest_data',{step:4}); }
    if(n===5){ renderRateSummary(); renderSummary('summary-5'); }
    window.scrollTo({top:0,behavior:'smooth'});
  }
  window.goTo = goTo;

  function updateGuests(type, d){
    if(type==='adults') state.adults=Math.max(1,Math.min(20,state.adults+d));
    if(type==='children'){ state.children=Math.max(0,Math.min(10,state.children+d)); while(state.childAges.length<state.children) state.childAges.push(6); state.childAges=state.childAges.slice(0,state.children); renderChildAges(); }
    $(type+'-val').textContent=state[type]; updateSidebar1();
  }
  window.updateGuests = updateGuests;

  function renderChildAges(){
    const wrap=$('children-ages-wrap'), list=$('child-ages-list');
    if(state.children===0){ wrap.classList.add('hidden'); list.innerHTML=''; return; }
    wrap.classList.remove('hidden'); let h='';
    for(let i=0;i<state.children;i++){ const age=state.childAges[i]??6; let opts='';
      for(let a=0;a<=17;a++) opts+='<option value="'+a+'"'+(a===age?' selected':'')+'>'+a+(a===1?' Jahr':' Jahre')+'</option>';
      h+='<div class="child-age-row"><label>Kind '+(i+1)+'</label><select onchange="setChildAge('+i+',this.value)">'+opts+'</select></div>'; }
    list.innerHTML=h;
  }
  window.setChildAge=(i,v)=>{ state.childAges[i]=parseInt(v); };

  function updateSidebar1(){
    $('sum-dates').textContent=fmtDate(state.checkin)+' – '+fmtDate(state.checkout);
    $('sum-nights').textContent=nights()+' Nächte';
    let g=state.adults+' Erwachsene'; if(state.children>0) g+=', '+state.children+' Kind'+(state.children>1?'er':'');
    $('sum-guests').textContent=g;
  }

  function renderUnits(){
    const N=nights(); const cap=state.adults+state.childAges.filter(a=>a>2).length;
    const grouped={}; CFG.units.forEach(u=>{ (grouped[u.category||'Unterkünfte']=grouped[u.category||'Unterkünfte']||[]).push(u); });
    const saver = CFG.rates.find(r=>r.discount>0) || defaultRate;
    let h='';
    Object.keys(grouped).forEach(cat=>{
      h+='<div class="units-category"><h3>'+esc(cat)+'</h3></div><div class="units">';
      grouped[cat].forEach(u=>{
        const small=u.maxPax<cap; const sel=state.selectedUnitId===u.id;
        const saverPrice=u.pricePerNight*(1-(saver?saver.discount:0))*N; const flexPrice=u.pricePerNight*N;
        h+='<div class="unit-card '+(sel?'selected':'')+' '+(small?'disabled':'')+'" onclick="'+(small?'':'selectUnit('+u.id+')')+'">';
        h+='<div class="unit-image-wrap">'+(u.badge?'<div class="badge">'+esc(u.badge)+'</div>':'');
        if(u.image) h+='<img src="'+esc(u.image)+'" alt="'+esc(u.name)+'" loading="lazy" referrerpolicy="no-referrer">';
        h+='</div><div class="unit-body"><div class="unit-name">'+esc(u.name)+'</div>';
        h+='<div class="unit-meta"><span>bis '+u.maxPax+' Pers.</span>'+(u.size?'<span>·</span><span>'+esc(u.size)+'</span>':'')+'</div>';
        if(u.rooms) h+='<div class="unit-rooms">'+esc(u.rooms)+'</div>';
        if(u.desc) h+='<div class="unit-desc">'+esc(u.desc)+'</div>';
        if(small){ h+='<div style="margin-top:10px"><span class="pill pill-warn">Zu klein für '+cap+' Pers.</span></div>'; }
        else { h+='<div class="unit-rates">'; CFG.rates.forEach(r=>{ const price=u.pricePerNight*(1-r.discount)*N; const active=sel&&state.selectedRateId===r.id;
          h+='<span class="rate-pill '+(active?'active':'')+'" onclick="event.stopPropagation();selectRate('+u.id+','+r.id+')"><strong>'+esc(r.label)+'</strong> · '+fmtPrice(price)+(r.sublabel?' · '+esc(r.sublabel):'')+'</span>'; }); h+='</div>'; }
        h+='</div><div class="unit-price-block">';
        if(sel){ const t=calc(); h+='<div class="unit-price">'+fmtPrice(t.unitsTotal)+'<small>'+N+' N · '+fmtPrice(t.nightly)+'/Nacht</small></div>'; }
        else { h+='<div class="unit-price">ab '+fmtPrice(saverPrice)+'<small>'+N+' Nächte</small></div>'; }
        h+='<button class="unit-select">'+(sel?'✓ Ausgewählt':'Auswählen')+'</button></div></div>';
      });
      h+='</div>';
    });
    $('units-list').innerHTML=h;
  }

  function selectUnit(id){ pickUnit(id, state.selectedRateId || (defaultRate&&defaultRate.id)); }
  function selectRate(id, rid){ pickUnit(id, rid); }
  function pickUnit(id, rid){
    state.selectedUnitId=id; state.selectedRateId=rid;
    $('btn-step-2').disabled=false; renderUnits(); renderSummary('summary-2');
    const u=unit(), t=calc();
    track('select_unit',{step:2, value:t.unitsTotal, payload:{ unit_id:id, unit_name:u?u.name:null }});
    track('select_rate',{step:2, payload:{ rate_id:rid }});
    setTimeout(()=>goTo(3),400);
  }
  window.selectUnit=selectUnit; window.selectRate=selectRate;

  function renderExtras(){
    $('extras-list').innerHTML = CFG.extras.map(e=>'<div class="extra-item '+(state.selectedExtras.has(e.id)?'selected':'')+'" onclick="toggleExtra('+e.id+')"><div class="checkbox"></div><div class="extra-info"><h4>'+esc(e.name)+'</h4><p>'+esc(e.desc||'')+'</p></div><div class="extra-price">'+fmtPrice(e.price)+'<small>'+esc(e.unitLabel)+'</small></div></div>').join('');
  }
  function toggleExtra(id){ state.selectedExtras.has(id)?state.selectedExtras.delete(id):state.selectedExtras.add(id); renderExtras(); renderSummary('summary-3'); const e=CFG.extras.find(x=>x.id===id); track('add_extra',{step:3, payload:{ extra_id:id, name:e?e.name:null }}); }
  window.toggleExtra=toggleExtra;

  function renderInsurance(){ const t=calc(); const cost=Math.round((t.unitsTotal+t.extrasTotal)*CFG.hotel.insuranceRate*100)/100; $('insurance-price').textContent=fmtPrice(cost); $('insurance-card').classList.toggle('selected',state.insurance); }
  function toggleInsurance(){ state.insurance=!state.insurance; renderInsurance(); renderSummary('summary-3'); if(state.insurance) track('add_insurance',{step:3}); }
  window.toggleInsurance=toggleInsurance;

  function renderSummary(target){
    const el=$(target); if(!el) return; const t=calc(); const u=unit(), r=rate();
    let h='<div class="summary-label">Buchungsübersicht</div><h3>'+(u?esc(u.name):'Noch nicht gewählt')+'</h3>';
    if(r) h+='<div class="summary-badge">'+esc(r.label)+(r.sublabel?' · '+esc(r.sublabel):'')+'</div>';
    h+='<div style="height:10px"></div><div class="summary-section">';
    h+='<div class="summary-row muted"><span>Anreise</span><span>'+fmtDate(state.checkin)+'</span></div>';
    h+='<div class="summary-row muted"><span>Abreise</span><span>'+fmtDate(state.checkout)+'</span></div>';
    let g=state.adults+' Erw.'; if(state.children>0) g+=' + '+state.children+' Kind'+(state.children>1?'er':'');
    h+='<div class="summary-row muted"><span>'+t.N+' Nächte</span><span>'+g+'</span></div></div>';
    if(u){ h+='<div class="summary-section"><div class="summary-row"><span>Unterkunft</span><span><strong>'+fmtPrice(t.unitsTotal)+'</strong></span></div>';
      if(state.selectedExtras.size) h+='<div class="summary-row" style="margin-top:8px"><span>Extras</span><span><strong>'+fmtPrice(t.extrasTotal)+'</strong></span></div>';
      if(state.insurance) h+='<div class="summary-row" style="margin-top:8px"><span>Storno-Versicherung</span><span><strong>'+fmtPrice(t.insurance)+'</strong></span></div>';
      h+='</div><div class="summary-total"><span class="label">Gesamt</span><span class="amount">'+fmtPrice(t.grand)+'</span></div>';
      if(r && r.depositPercent<1) h+='<div class="summary-fine">Anzahlung jetzt fällig: <strong>'+fmtPrice(t.deposit)+'</strong>. Restbetrag bei Anreise.</div>';
      else h+='<div class="summary-fine">Voller Betrag jetzt fällig.</div>';
      h+='<div class="summary-fine" style="margin-top:8px">Inkl. '+CFG.hotel.taxPercent+'% USt., zzgl. Ortstaxe '+fmtPrice(CFG.hotel.cityTax)+' p.P./Nacht vor Ort.</div>';
    } else { h+='<div class="summary-fine">Wähle eine Unterkunft, um den Preis zu sehen.</div>'; }
    el.innerHTML=h;
  }

  function renderRateSummary(){ const r=rate(); if(!r) return; let h='<h4>Deine Rate: '+esc(r.label)+(r.sublabel?' · '+esc(r.sublabel):'')+'</h4><ul>'; (r.rules||[]).forEach(x=>h+='<li>'+esc(x)+'</li>'); h+='</ul>'; $('rate-summary').innerHTML=h; }

  document.querySelectorAll('#payment-methods .payment-option').forEach(opt=>opt.addEventListener('click',()=>{ document.querySelectorAll('#payment-methods .payment-option').forEach(o=>o.classList.remove('selected')); opt.classList.add('selected'); state.paymentMethod=opt.dataset.method; }));

  function collectGuest(){
    return { first_name: $('g-first').value.trim(), last_name: $('g-last').value.trim(), email: $('g-email').value.trim(),
      phone: $('g-phone').value.trim(), address: $('g-address').value.trim(), zip: $('g-zip').value.trim(), city: $('g-city').value.trim(), notes: $('g-notes').value.trim() };
  }

  async function submitBooking(){
    const guest = collectGuest();
    if(!guest.first_name || !guest.last_name || !/.+@.+\..+/.test(guest.email)){ goTo(4); const err=$('guest-error'); err.textContent='Bitte Vorname, Nachname und eine gültige E-Mail angeben.'; err.classList.remove('hidden'); return; }
    const btn=$('btn-book'); btn.disabled=true; btn.innerHTML='Wird angelegt<span class="dots"></span>';
    try {
      const res = await fetch(CFG.endpoints.store, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify({ session_id:SESSION_ID, check_in:state.checkin, check_out:state.checkout, adults:state.adults, child_ages:state.childAges,
          unit_id:state.selectedUnitId, rate_id:state.selectedRateId, extras:Array.from(state.selectedExtras), insurance:state.insurance,
          board:CFG.hotel.board||null, payment_method:state.paymentMethod, guest }) });
      if(!res.ok) throw new Error('store failed');
      const data = await res.json();
      state.bookingRef=data.reference; state.completeUrl=data.complete_url;
      $('pay-ref').textContent=data.reference; $('pay-amount').textContent=fmtPrice(data.amount_due);
      document.querySelectorAll('.step-panel').forEach(p=>p.classList.add('hidden')); $('step-pay').classList.remove('hidden');
      track('add_payment_info',{step:5, value:data.grand_total, payload:{ payment_method: state.paymentMethod }});
      window.scrollTo({top:0});
    } catch(e){ btn.disabled=false; btn.innerHTML='Jetzt verbindlich buchen →'; alert('Es ist ein Fehler aufgetreten. Bitte erneut versuchen.'); }
  }
  window.submitBooking=submitBooking;

  async function completePayment(){
    const btn=$('pay-submit'); btn.disabled=true; btn.innerHTML='Zahlung wird verarbeitet<span class="dots"></span>';
    try {
      const res = await fetch(state.completeUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:'{}' });
      const data = await res.json();
      const t = calc();
      fireConversion(state.bookingRef, t.grand);
      window.location.href = data.redirect;
    } catch(e){ btn.disabled=false; btn.innerHTML='Jetzt bezahlen'; alert('Zahlung fehlgeschlagen.'); }
  }
  window.completePayment=completePayment;

  // ---- Init ----
  $('checkin').value=state.checkin; $('checkout').value=state.checkout;
  $('checkin').addEventListener('change',e=>{ state.checkin=e.target.value; updateSidebar1(); });
  $('checkout').addEventListener('change',e=>{ state.checkout=e.target.value; updateSidebar1(); });
  updateSidebar1();
  track('begin_checkout',{step:1});
})();
</script>
</body>
</html>
