<?php
$formatRubroLabel = static function (?string $descripcion): string {
    $valor = strtolower(trim((string) $descripcion));
    return match ($valor) {
        'cancha', 'canchas' => '🏟 Canchas',
        'peluqueria', 'peluquería' => '💇 Peluquería',
        'consultorio', 'consultorios' => '🏥 Consultorio',
        'gimnasio', 'gimnasios' => '🏋 Gimnasio',
        'comida', 'restaurante', 'restaurantes', 'pedidos' => '🍽 Pedidos',
        default => trim((string) $descripcion) !== '' ? (string) $descripcion : 'Otro',
    };
};
$selectedRubroName = trim((string) old('rubro_nombre'));
if ($selectedRubroName === '' && old('id_rubro')) {
    foreach (($rubros ?? []) as $rubroItem) {
        if ((string) old('id_rubro') === (string) ($rubroItem['id'] ?? '')) {
            $selectedRubroName = trim((string) ($rubroItem['descripcion'] ?? ''));
            break;
        }
    }
}
$hasRegisterData = old('name') || old('razon_social') || old('email') || old('dni') || old('city') || old('phone');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php $security = config('Security'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token-name" content="<?= esc($security->tokenName) ?>">
    <meta name="csrf-header-name" content="<?= esc($security->headerName) ?>">
    <meta name="csrf-cookie-name" content="<?= esc($security->cookieName) ?>">
    <meta name="csrf-hash" content="<?= esc(csrf_hash()) ?>">
    <title>Registro | TURNOK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="icon" href="<?= base_url('favicon-32x32.png?v=20260317a') ?>" sizes="32x32" type="image/png">
    <style>
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Regular.ttf") ?>') format('truetype');
            font-weight:400;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Medium.ttf") ?>') format('truetype');
            font-weight:500;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-SemiBold.ttf") ?>') format('truetype');
            font-weight:600;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Bold.ttf") ?>') format('truetype');
            font-weight:700;
            font-style:normal;
        }
        :root {
            --turnok-blue: #165ecc;
            --turnok-blue-deep: #11499d;
            --turnok-lime: #e3f50d;
            --turnok-orange: #ffa042;
            --turnok-sky: #b1d4f0;
            --turnok-ink: #1e1e1e;
            --turnok-cream: #f7f3e7;
            --bg-main: #e8edf4;
            --text-main: #1e1e1e;
            --card-bg: rgba(255, 255, 255, .92);
            --card-border: rgba(177, 212, 240, .8);
            --hero-bg-start: rgba(12, 61, 132, .88);
            --hero-bg-end: rgba(20, 79, 160, .92);
            --form-border: rgba(177, 212, 240, .9);
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(1100px 460px at -10% -10%, rgba(22,94,204,.10), transparent 55%),
                radial-gradient(900px 300px at 110% 0%, rgba(255,160,66,.05), transparent 52%),
                linear-gradient(180deg, #f6f2e9 0%, var(--bg-main) 100%);
            color: var(--text-main);
            font-family: 'Quicksand', sans-serif;
        }
        body.theme-dark {
            --bg-main: #0f1f2f;
            --text-main: #dbe9f8;
            --card-bg: rgba(15, 31, 48, .76);
            --card-border: rgba(53, 111, 191, .7);
            --hero-bg-start: rgba(16, 62, 132, .94);
            --hero-bg-end: rgba(11, 28, 45, .90);
            --form-border: rgba(177, 212, 240, .16);
        }
        body.theme-dark {
            background:
                radial-gradient(circle at 12% 18%, rgba(22,94,204,.18), transparent 28%),
                radial-gradient(circle at 88% 8%, rgba(255,160,66,.06), transparent 22%),
                var(--bg-main);
        }
        .register-shell {
            min-height: 100dvh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 4px 0 14px;
        }
        .register-box {
            max-width: 980px;
            width: 100%;
            background: linear-gradient(180deg, rgba(255,252,247,.96) 0%, rgba(247,249,252,.96) 100%);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(16,65,116,.12);
            padding: 1.45rem 2rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .register-corner-brand {
            position: absolute;
            top: -48px;
            right: -48px;
            width: 164px;
            height: 164px;
            border-radius: 50%;
            background: linear-gradient(180deg, #edf3fb 0%, #dfeaf7 100%);
            border: 1px solid rgba(177,212,240,.58);
            box-shadow: 0 24px 56px rgba(16,65,116,.14);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding-left: 28px;
            padding-top: 30px;
            z-index: 2;
            text-decoration: none;
        }
        .register-corner-brand img {
            width: 70px;
            height: auto;
            display: block;
        }
        body.theme-dark .register-box {
            background:
                radial-gradient(circle at 12% 18%, rgba(22,94,204,.12), transparent 24%),
                linear-gradient(180deg, rgba(16,31,47,.94) 0%, rgba(12,24,38,.96) 100%);
            border-color: rgba(53, 111, 191, .7);
            box-shadow: 0 24px 64px rgba(2,9,18,.36);
        }
        body.theme-dark .register-corner-brand {
            background: linear-gradient(180deg, #dfe8f4 0%, #cdd9ea 100%);
            border-color: rgba(177,212,240,.24);
            box-shadow: 0 24px 56px rgba(0,0,0,.28);
        }
        body.theme-dark .register-box::before {
            opacity: .16;
        }
        .register-box::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(177,212,240,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(177,212,240,.06) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: .22;
            pointer-events: none;
        }
        .top-info {
            display: none;
        }
        .register-logo {
            display: flex;
            justify-content: center;
            margin-bottom: .8rem;
            position: relative;
            z-index: 1;
        }
        .register-logo img {
            width: 168px;
            height: auto;
        }
        .register-body {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
            position: relative;
            z-index: 1;
        }
        .register-body.is-plan-step {
            grid-template-columns: 1fr;
        }
        .register-body.is-plan-step .form-panel {
            display: none;
        }
        .register-body.is-form-step {
            grid-template-columns: 1fr;
        }
        .register-body.is-form-step .hero-panel {
            display: none;
        }
        .hero-panel {
            background: linear-gradient(155deg, var(--hero-bg-start) 0%, var(--hero-bg-end) 100%);
            border: 1px solid rgba(177, 212, 240, .16);
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            text-align: left;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
        }
        body:not(.theme-dark) .hero-panel {
            background:
                radial-gradient(circle at 12% 14%, rgba(227,245,13,.05), transparent 18%),
                radial-gradient(circle at 88% 82%, rgba(255,160,66,.07), transparent 24%),
                linear-gradient(135deg, #5d7a9f 0%, #4a6889 52%, #3b5971 100%);
        }
        .hero-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(9,132,227,.12);
            color: #0a74c2;
            font-size: 1.4rem;
            margin: .2rem auto .5rem;
        }
        body.theme-dark .hero-icon {
            background: rgba(177,212,240,.10);
            color: #b1d4f0;
        }
        .hero-panel h2 {
            color: #f7f3e7;
            font-size: 1.55rem;
            font-weight: 700;
            margin-bottom: .45rem;
            letter-spacing: -.02em;
        }
        .hero-panel p {
            color: #dcecff;
            margin-bottom: .9rem;
            font-size: 1rem;
        }
        .panel-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(247,243,231,.10);
            border: 1px solid rgba(177,212,240,.16);
            color: #f7f3e7;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-weight: 700;
            width: max-content;
        }
        body.theme-dark .panel-kicker,
        body.theme-dark .form-kicker {
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.14);
        }
        .panel-kicker::before {
            content:'';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--turnok-lime);
            box-shadow: 0 0 0 4px rgba(227,245,13,.14);
        }
        .pricing-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 12px;
        }
        .billing-toggle {
            display: inline-grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
            padding: 6px;
            margin-bottom: 12px;
            border-radius: 16px;
            background: rgba(12, 29, 51, .18);
            border: 1px solid rgba(177,212,240,.18);
            width: 100%;
            max-width: 320px;
        }
        .billing-option {
            border: 0;
            border-radius: 12px;
            min-height: 42px;
            padding: 0 14px;
            background: transparent;
            color: #dcecff;
            font-weight: 700;
            font-size: .9rem;
            transition: background-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .billing-option small {
            display: block;
            font-size: .68rem;
            opacity: .8;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .billing-option.active {
            background: rgba(247,243,231,.14);
            color: #f7f3e7;
            box-shadow: 0 10px 20px rgba(5, 18, 33, .12), inset 0 1px 0 rgba(255,255,255,.1);
        }
        .plan-card {
            border: 1px solid rgba(177,212,240,.26);
            border-radius: 16px;
            background: rgba(247,243,231,.08);
            padding: 10px 12px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .plan-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.06) 0%, transparent 48%);
            opacity: 0;
            transition: opacity .2s ease;
            pointer-events: none;
        }
        .plan-card:hover {
            transform: translateY(-1px);
            border-color: rgba(177,212,240,.34);
        }
        .plan-card:hover::after {
            opacity: 1;
        }
        .plan-card.selected {
            border-color: rgba(177,212,240,.5);
            background: rgba(247,243,231,.14);
            box-shadow: 0 0 0 2px rgba(177,212,240,.10), 0 10px 24px rgba(7, 18, 34, .10);
            transform: translateY(-1px);
        }
        .plan-card.selected::after {
            opacity: 1;
        }
        body:not(.theme-dark) .plan-card {
            background: rgba(15,35,58,.16);
            border-color: rgba(177,212,240,.22);
        }
        body:not(.theme-dark) .plan-card.selected {
            background: rgba(247,243,231,.14);
            border-color: rgba(177,212,240,.40);
        }
        body.theme-dark .plan-card {
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.18);
        }
        body.theme-dark .plan-card.selected {
            background: rgba(177,212,240,.12);
            border-color: rgba(177,212,240,.34);
            box-shadow: 0 0 0 2px rgba(177,212,240,.12), 0 12px 28px rgba(0, 0, 0, .18);
        }
        body.theme-dark .plan-detail-card {
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.14);
        }
        body.theme-dark .billing-toggle {
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.14);
        }
        body.theme-dark .billing-option.active {
            background: rgba(177,212,240,.14);
        }
        body.theme-dark .btn-plan-next {
            background: linear-gradient(135deg, #f3eee3 0%, #dbe6f3 100%);
            color: var(--turnok-blue-deep);
        }
        .plan-title {
            font-weight: 700;
            color: #f7f3e7;
        }
        .plan-price {
            color: #dcecff;
            font-size: .95rem;
        }
        .plan-check {
            position: absolute;
            right: 10px;
            top: 10px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 1px solid rgba(177,212,240,.5);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            background: rgba(247,243,231,.12);
        }
        .plan-card.selected .plan-check {
            background: rgba(177,212,240,.18);
            border-color: rgba(177,212,240,.55);
            color: #f7f3e7;
        }
        .calc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .calc-label {
            font-size: .82rem;
            color: #dcecff;
            margin-bottom: 4px;
        }
        .price-line {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed rgba(177,212,240,.35);
            color: #f7f3e7;
            font-weight: 700;
        }
        .plan-detail-card {
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(247,243,231,.10);
            border: 1px solid rgba(177,212,240,.16);
        }
        .plan-detail-kicker {
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #dcecff;
            margin-bottom: 6px;
        }
        .plan-detail-title {
            color: #f7f3e7;
            font-size: 1.18rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .plan-detail-text {
            color: #dcecff;
            font-size: .94rem;
            margin-bottom: 10px;
        }
        .plan-detail-list {
            margin: 0;
            padding-left: 18px;
            color: #eaf4ff;
            display: grid;
            gap: 6px;
            font-size: .88rem;
        }
        .plan-cta {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-plan-next {
            background: linear-gradient(135deg, #f3eee3 0%, #dbe6f3 100%);
            color: var(--turnok-blue-deep);
            border: 0;
            border-radius: 16px;
            min-height: 48px;
            padding: 0 18px;
            font-weight: 700;
        }
        .plan-cta-note {
            color: #dcecff;
            font-size: .84rem;
        }
        .form-panel {
            background: linear-gradient(180deg, rgba(255, 252, 247, .98) 0%, rgba(248, 250, 253, .98) 100%);
            border: 1px solid var(--form-border);
            border-radius: 24px;
            padding: 1.15rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 40px rgba(17, 73, 157, .08), inset 0 1px 0 rgba(255,255,255,.9);
        }
        .form-panel .mb-3 {
            margin-bottom: .7rem !important;
        }
        .form-panel h1,
        .form-panel .form-group,
        .form-panel .row.d-flex.align-items-center.justify-content-center.flex-nowrap.flex-row {
            display: none !important;
        }
        .form-panel .form-control,
        .form-panel .form-select,
        .form-panel .input-group-text {
            background: #ffffff;
            border-color: rgba(177,212,240,.95);
            color: var(--turnok-ink);
            border-radius: 16px;
            font-size: 1rem;
        }
        .form-panel .form-floating > .form-control,
        .form-panel .form-select,
        .form-panel .input-group-text {
            min-height: calc(3.45rem + 2px);
        }
        .form-panel .form-floating > .form-control {
            height: calc(3.45rem + 2px);
            padding-top: 1.5rem;
            padding-bottom: .55rem;
        }
        .form-panel .form-select {
            padding-top: .85rem;
            padding-bottom: .85rem;
        }
        .form-panel .input-group-text {
            padding-left: .9rem;
            padding-right: .9rem;
        }
        .form-panel .form-floating > label,
        .form-label,
        .small.text-muted {
            color: #5b6f84 !important;
        }
        .form-panel .form-floating > label {
            font-size: .98rem;
        }
        .form-panel .form-floating > .form-control:focus {
            border-color: #72a7ea;
            box-shadow: 0 0 0 .2rem rgba(114, 167, 234, .12);
        }
        .form-panel .form-floating > .form-control::placeholder,
        #link_path::placeholder {
            color: transparent;
        }
        .form-header {
            margin-bottom: 10px;
        }
        .form-step-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .register-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0 12px;
        }
        .register-form-grid .full-width {
            grid-column: 1 / -1;
        }
        .form-step {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #5b6f84;
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .form-step-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22,94,204,.12);
            color: var(--turnok-blue-deep);
        }
        .step-back-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid rgba(177,212,240,.7);
            background: rgba(255,255,255,.86);
            color: var(--turnok-blue-deep);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            transition: all .18s ease;
        }
        .step-back-btn:hover {
            background: #ffffff;
            border-color: rgba(22,94,204,.42);
            color: var(--turnok-blue-deep);
        }
        .form-lock-note {
            margin-bottom: 10px;
            padding: 8px 10px;
            border-radius: 14px;
            background: rgba(22,94,204,.06);
            border: 1px solid rgba(177,212,240,.35);
            color: #5b6f84;
            font-size: .82rem;
        }
        .form-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(66,100,137,.08);
            border: 1px solid rgba(177,212,240,.55);
            color: #47647f;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-weight: 700;
        }
        .form-kicker::before {
            content:'';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--turnok-lime);
            box-shadow: 0 0 0 4px rgba(227,245,13,.14);
        }
        .form-panel h3 {
            color: var(--turnok-ink) !important;
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: -.02em;
            margin-bottom: .2rem !important;
        }
        .form-subtitle {
            color: #5b6f84;
            font-size: .86rem;
            margin-bottom: .8rem;
        }
        .btn-main {
            background: linear-gradient(135deg, #f3eee3 0%, #dbe6f3 100%);
            color: var(--turnok-blue-deep);
            border: 0;
            font-weight: 700;
            border-radius: 16px;
        }
        .btn-main:hover {
            color: var(--turnok-blue-deep);
        }
        .btn-outline-main {
            border-color: rgba(109,166,224,.22);
            color: var(--turnok-blue-deep);
            border-radius: 16px;
            background: rgba(255,255,255,.84);
            min-height: 42px;
            font-weight: 700;
        }
        .btn-outline-main:hover {
            background: #eef2f6;
            color: var(--turnok-blue-deep);
            border-color: rgba(22,94,204,.42);
        }
        .form-panel .input-group-text {
            color: var(--turnok-blue-deep);
            background: #ffffff;
        }
        #btn-register-save {
            min-height: 44px;
        }
        body.theme-dark .btn-main {
            background: linear-gradient(135deg, #f2efe6 0%, #d9e7f7 100%);
            color: var(--turnok-blue-deep);
        }
        body.theme-dark .btn-outline-main {
            border-color: rgba(177,212,240,.42);
            color: #f7f3e7;
            background: rgba(177,212,240,.06);
        }
        body.theme-dark .btn-outline-main:hover {
            background: rgba(177,212,240,.14);
            color: #fff;
            border-color: rgba(177,212,240,.6);
        }
        body.theme-dark .form-panel {
            background: linear-gradient(180deg, rgba(15,35,58,.72) 0%, rgba(12,28,46,.78) 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
        }
        body.theme-dark .form-panel .form-control,
        body.theme-dark .form-panel .form-select,
        body.theme-dark .form-panel .input-group-text {
            background: rgba(247,243,231,.08);
            border-color: rgba(177,212,240,.34);
            color: #f7f3e7;
        }
        body.theme-dark .form-panel .form-floating > label,
        body.theme-dark .form-label,
        body.theme-dark .small.text-muted {
            color: #b9d4ed !important;
        }
        body.theme-dark .form-kicker {
            color: #dcecff;
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.14);
        }
        body.theme-dark .form-panel h3 {
            color: #f7f3e7 !important;
        }
        body.theme-dark .form-subtitle {
            color: #a8c5e0;
        }
        body.theme-dark .form-step {
            color: #b9d4ed;
        }
        body.theme-dark .form-step-badge {
            background: rgba(177,212,240,.12);
            color: #f7f3e7;
        }
        body.theme-dark .step-back-btn {
            background: rgba(177,212,240,.08);
            border-color: rgba(177,212,240,.22);
            color: #f7f3e7;
        }
        body.theme-dark .step-back-btn:hover {
            background: rgba(177,212,240,.14);
            border-color: rgba(177,212,240,.36);
            color: #ffffff;
        }
        body.theme-dark .form-lock-note {
            background: rgba(247,243,231,.06);
            border-color: rgba(177,212,240,.14);
            color: #b9d4ed;
        }
        body.theme-dark .btn-outline-main {
            border-color: rgba(177,212,240,.42);
            color: #f7f3e7;
        }
        body.theme-dark .btn-outline-main:hover {
            background: rgba(247,243,231,.08);
            color: #fff;
            border-color: rgba(177,212,240,.6);
        }
        .register-body.is-form-step .form-panel {
            max-width: 880px;
            width: 100%;
            margin: 0 auto;
        }
        @media (min-width: 992px) {
            .register-shell {
                min-height: 100dvh;
                align-items: flex-start;
                padding: 2px 0 10px;
            }
            .register-box {
                width: min(980px, calc(100vw - 20px));
                padding: 1.15rem 1.6rem 1.5rem;
            }
            .register-logo {
                margin-bottom: .65rem;
            }
            .register-logo img {
                width: 154px;
            }
            .register-corner-brand {
                top: -38px;
                right: -38px;
                width: 146px;
                height: 146px;
                padding-left: 24px;
                padding-top: 26px;
            }
            .register-corner-brand img {
                width: 62px;
            }
            .hero-panel,
            .form-panel {
                border-radius: 22px;
            }
            .register-form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0 16px;
            }
        }
        @media (min-width: 992px) and (max-height: 920px) {
            .register-shell {
                padding: 0 0 8px;
            }
            .register-box {
                padding: 1rem 1.35rem 1.25rem;
            }
            .register-logo {
                margin-bottom: .45rem;
            }
            .register-logo img {
                width: 142px;
            }
            .hero-panel {
                padding: 1.15rem;
            }
            .hero-icon {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
                margin: .1rem auto .4rem;
            }
            .hero-panel h2 {
                font-size: 1.38rem;
                margin-bottom: .35rem;
            }
            .hero-panel p {
                margin-bottom: .7rem;
                font-size: .92rem;
            }
            .panel-kicker {
                margin-bottom: 10px;
                padding: 7px 11px;
                font-size: .7rem;
            }
            .pricing-grid {
                gap: 8px;
                margin-bottom: 10px;
            }
            .plan-card {
                padding: 8px 11px;
            }
            .plan-price {
                font-size: .88rem;
            }
            .calc-grid {
                margin-bottom: 8px;
            }
            .calc-label {
                margin-bottom: 3px;
                font-size: .78rem;
            }
            .price-line {
                margin-top: 6px;
                padding-top: 6px;
                font-size: .95rem;
            }
            .plan-detail-card {
                margin-top: 10px;
                padding: 12px 14px;
            }
            .plan-detail-title {
                font-size: 1.05rem;
                margin-bottom: 4px;
            }
            .plan-detail-text {
                font-size: .88rem;
                margin-bottom: 8px;
            }
            .plan-detail-list {
                gap: 4px;
                font-size: .82rem;
            }
            .plan-cta {
                margin-top: 12px;
            }
            .btn-plan-next {
                min-height: 42px;
                padding: 0 16px;
            }
            .plan-cta-note {
                font-size: .79rem;
            }
        }
        @media (max-width: 767px) {
            .register-body {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .register-corner-brand {
                width: 132px;
                height: 132px;
                top: -34px;
                right: -34px;
                padding-left: 24px;
                padding-top: 24px;
            }
            .register-corner-brand img {
                width: 58px;
            }
        }
    </style>
</head>

<body>
    <script>
        (function () {
            try {
                if (localStorage.getItem('alfa_theme') === 'dark') {
                    document.body.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>
    <div class="container register-shell">
        <div class="register-box">
            <a class="register-corner-brand" href="<?= base_url() ?>" aria-label="Ir al inicio">
                <img src="<?= base_url(PUBLIC_FOLDER . "assets/images/turnok-corner-icon.png") ?>" alt="">
            </a>
            <div class="register-logo">
                <a href="<?= base_url() ?>"><img src="<?= base_url(PUBLIC_FOLDER . "assets/images/logo-shadow.png") ?>" alt="TURNOK"></a>
            </div>

            <div class="register-body <?= $hasRegisterData ? 'is-form-step' : 'is-plan-step' ?>" id="registerBody">
                <div class="hero-panel">
                    <div class="panel-kicker">Planes y costos</div>
                    <div class="hero-icon">
                        <i class="fa-regular fa-id-card"></i>
                    </div>
                    <h2>Planes disponibles</h2>
                    <p>Selecciona un plan y ajusta precios por cantidad de servicios y usuarios.</p>

                    <div class="billing-toggle" id="billingToggle" role="tablist" aria-label="Tipo de cobro">
                        <button type="button" class="billing-option active" data-billing="mensual">
                            Mensual
                            <small>Cobro mes a mes</small>
                        </button>
                        <button type="button" class="billing-option" data-billing="anual">
                            Anual
                            <small>Ahorras 2 meses</small>
                        </button>
                    </div>

                    <div class="pricing-grid" id="pricingGrid">
                        <div class="plan-card selected" data-plan="Basico" data-base="12000" data-service="1800" data-user="900">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Basico</div>
                            <div class="plan-price">$12.000 base mensual</div>
                        </div>
                        <div class="plan-card" data-plan="Pro" data-base="24000" data-service="3200" data-user="1500">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Pro</div>
                            <div class="plan-price">$24.000 base mensual</div>
                        </div>
                        <div class="plan-card" data-plan="Premium" data-base="42000" data-service="5200" data-user="2500">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Premium</div>
                            <div class="plan-price">$42.000 base mensual</div>
                        </div>
                    </div>

                    <div class="calc-grid">
                        <div>
                            <div class="calc-label">Cantidad de servicios</div>
                            <input type="number" min="1" step="1" value="1" id="qtyServicios" class="form-control form-control-sm">
                        </div>
                        <div>
                            <div class="calc-label">Cantidad de usuarios</div>
                            <input type="number" min="1" step="1" value="1" id="qtyUsuarios" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="small text-muted">
                        Formula: Base + (Servicios x valor servicio) + (Usuarios x valor usuario)
                    </div>
                    <div class="price-line" id="priceLine">Total estimado: $14.700 / mes</div>
                    <div class="plan-detail-card" id="planDetailCard">
                        <div class="plan-detail-kicker">Paso 1</div>
                        <div class="plan-detail-title" id="planDetailTitle">Basico</div>
                        <div class="plan-detail-text" id="planDetailText">Ideal para comenzar con una operacion simple y ordenada.</div>
                        <ul class="plan-detail-list" id="planDetailList">
                            <li>Agenda centralizada y acceso admin.</li>
                            <li>Configuracion base para tu rubro.</li>
                            <li>Escalable por servicios y usuarios.</li>
                        </ul>
                    </div>
                    <div class="plan-cta">
                        <button type="button" class="btn btn-plan-next" id="goToRegisterStep">Registrarse ahora</button>
                        <div class="plan-cta-note">Paso 2: completa tus datos con el plan elegido.</div>
                    </div>
                </div>

                <form action="" method="POST" class="form-panel">

                <!-- legacy-form removed -->

                    <?php if (session('msg')) : ?>
                        <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                            <small> <?= session('msg.body') ?> </small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="form-header">
                        <div class="form-step-row">
                            <button type="button" class="step-back-btn js-back-plan" aria-label="Volver a planes">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <div class="form-step"><span class="form-step-badge">2</span> Carga de datos</div>
                        </div>
                        <div class="form-kicker">Registro cliente</div>
                        <h3 class="h5 mb-1 text-center">Tus datos</h3>
                        <div class="form-subtitle">Completa la informacion de tu cuenta para crear tu espacio en TURNOK.</div>
                    </div>
                    <?php if (!$hasRegisterData) : ?>
                        <div class="form-lock-note" id="formLockNote">Primero elige tu plan y luego pulsa "Registrarse ahora" para completar tus datos.</div>
                    <?php endif; ?>
                    <input type="hidden" name="plan" id="plan" value="<?= esc(old('plan', 'Basico')) ?>">
                    <input type="hidden" name="billing_cycle" id="billing_cycle" value="<?= esc(old('billing_cycle', 'mensual')) ?>">
                    <input type="hidden" name="cantidad_servicios" id="cantidad_servicios" value="<?= esc(old('cantidad_servicios', '1')) ?>">
                    <input type="hidden" name="cantidad_usuarios" id="cantidad_usuarios" value="<?= esc(old('cantidad_usuarios', '1')) ?>">

                    <div class="register-form-grid">
                        <div class="form-floating mb-3">
                            <input type="text" name="name" class="form-control" id="name" placeholder="Nombre y apellido" value="<?= esc(old('name')) ?>" required>
                            <label for="name">Nombre y apellido</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="razon_social" class="form-control" id="razon_social" placeholder="Razon social" value="<?= esc(old('razon_social')) ?>" required>
                            <label for="razon_social">Razon social</label>
                        </div>

                        <div class="mb-3 full-width">
                            <label class="form-label mb-1">Link completo</label>
                            <div class="small text-muted mb-2" id="full_link_label">-</div>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" name="link_path" class="form-control" id="link_path" placeholder="..." value="<?= esc(old('link_path')) ?>" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="dni" class="form-control" id="dni" placeholder="DNI" value="<?= esc(old('dni')) ?>" required>
                            <label for="dni">DNI</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="city" class="form-control" id="city" placeholder="Localidad" value="<?= esc(old('city')) ?>" required>
                            <label for="city">Localidad</label>
                        </div>

                        <div class="mb-3">
                            <label for="id_rubro" class="form-label mb-1">Rubro</label>
                            <input type="hidden" name="id_rubro" id="id_rubro" value="<?= esc(old('id_rubro')) ?>">
                            <input
                                type="text"
                                name="rubro_nombre"
                                id="rubro_nombre"
                                class="form-control"
                                list="rubros_list"
                                placeholder="Seleccionar o escribir rubro"
                                value="<?= esc($selectedRubroName) ?>"
                                required
                            >
                            <datalist id="rubros_list">
                                <?php foreach (($rubros ?? []) as $rubro) : ?>
                                    <option value="<?= esc(trim((string) ($rubro['descripcion'] ?? ''))) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="form-floating mb-3 full-width">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?= esc(old('email')) ?>" required>
                            <label for="email">Email</label>
                        </div>

                        <div class="form-floating mb-3 full-width">
                            <input type="password" name="password" class="form-control" id="password" placeholder="Contrasena" required>
                            <label for="password">Contrasena</label>
                        </div>

                        <div class="form-floating mb-3 full-width">
                            <input type="text" name="phone" class="form-control" id="phone" placeholder="Telefono" value="<?= esc(old('phone')) ?>" required>
                            <label for="phone">Telefono</label>
                        </div>

                        <div class="d-grid gap-2 mt-2 mb-2 full-width">
                            <button type="submit" class="btn btn-main" id="btn-register-save">Registrar</button>
                            <button type="button" class="btn btn-outline-main js-back-plan" id="backToPlanStep">Volver</button>
                        </div>
                    </div>

                    <h1 style="color:#595959" class="text-center">Registrate y accedé a descuentos</h1>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="name" class="form-control" placeholder="Nombre">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="last_name" class="form-control" placeholder="Apellido">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="dni" class="form-control" placeholder="DNI">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="city" class="form-control" placeholder="Localidad">
                    </div>

                    <div class="d-flex justify-content-center align-items-center flex-row" style="width: 100%;">
                        <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center" style="width: 30%;">
                            <input type="text" name="areaCode" class="form-control" placeholder="Código de área">
                        </div>
                        <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center" style="width: 70%;">
                            <input type="text" name="phone" class="form-control" placeholder="Teléfono">
                        </div>
                    </div>


                    <div class="row d-flex align-items-center justify-content-center flex-nowrap flex-row">
                        <div class="col d-flex align-items-end justify-content-end">
                            <a href="<?= base_url('abmAdmin') ?>" style="background-color: #595959; color: #ffffff" class="btn btn-block btn-flat me-2">Volver</a>
                            <button type="submit" class="btn btn-block btn-flat" style="background-color: #f39323;" id="btn-login">Registrar</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const razonSocialInput = document.getElementById('razon_social');
            const linkPathInput = document.getElementById('link_path');
            const fullLinkLabel = document.getElementById('full_link_label');
            const baseWeb = <?= json_encode(rtrim((string) env('app.baseURL', base_url('/')), '/')) ?>;
            let linkEdited = false;

            const normalizeKey = (value) => {
                return (value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '')
                    .slice(0, 90);
            };

            const normalizePath = (value) => {
                return normalizeKey((value || '').replace(/^\/+/, ''));
            };

            const updateBaseAndLinkPreview = (forceAuto = false) => {
                if (!razonSocialInput || !linkPathInput || !fullLinkLabel) {
                    return;
                }

                const key = normalizeKey(razonSocialInput.value);

                if (!linkEdited || forceAuto) {
                    linkPathInput.value = key;
                }

                const path = normalizePath(linkPathInput.value);
                linkPathInput.value = path;
                fullLinkLabel.textContent = path ? (baseWeb + '/' + path) : '-';
            };

            const cards = Array.from(document.querySelectorAll('.plan-card'));
            const qtyServicios = document.getElementById('qtyServicios');
            const qtyUsuarios = document.getElementById('qtyUsuarios');
            const planHidden = document.getElementById('plan');
            const billingCycleHidden = document.getElementById('billing_cycle');
            const serviciosHidden = document.getElementById('cantidad_servicios');
            const usuariosHidden = document.getElementById('cantidad_usuarios');
            const rubroInput = document.getElementById('rubro_nombre');
            const rubroHidden = document.getElementById('id_rubro');
            const priceLine = document.getElementById('priceLine');
            const billingOptions = Array.from(document.querySelectorAll('.billing-option'));
            const registerBody = document.getElementById('registerBody');
            const goToRegisterStep = document.getElementById('goToRegisterStep');
            const planDetailTitle = document.getElementById('planDetailTitle');
            const planDetailText = document.getElementById('planDetailText');
            const planDetailList = document.getElementById('planDetailList');
            const formLockNote = document.getElementById('formLockNote');
            const backToPlanButtons = Array.from(document.querySelectorAll('.js-back-plan'));

            const planMeta = {
                Basico: {
                    text: 'Ideal para comenzar con una operacion simple y ordenada.',
                    items: [
                        'Agenda centralizada y acceso admin.',
                        'Configuracion base para tu rubro.',
                        'Escalable por servicios y usuarios.',
                    ],
                },
                Pro: {
                    text: 'Pensado para negocios que necesitan mas control y volumen.',
                    items: [
                        'Mayor capacidad operativa y multi-sede.',
                        'Mas servicios y usuarios con mejor margen.',
                        'Gestion mas solida para equipos en crecimiento.',
                    ],
                },
                Premium: {
                    text: 'La opcion mas completa para operaciones intensivas y multi-cliente.',
                    items: [
                        'Cobertura amplia para rubros, sedes y equipos.',
                        'Escala alta con configuracion avanzada.',
                        'Preparado para crecimiento continuo.',
                    ],
                },
            };
            const rubroMap = new Map([
                <?php foreach (($rubros ?? []) as $rubro) : ?>
                [<?= json_encode(strtolower(trim((string) ($rubro['descripcion'] ?? '')))) ?>, <?= json_encode((string) ($rubro['id'] ?? '')) ?>],
                <?php endforeach; ?>
            ]);

            function unlockRegisterStep() {
                if (registerBody) {
                    registerBody.classList.remove('is-plan-step');
                    registerBody.classList.add('is-form-step');
                }
                if (formLockNote) {
                    formLockNote.style.display = 'none';
                }
                document.querySelector('.form-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function goBackToPlanStep() {
                if (registerBody) {
                    registerBody.classList.remove('is-form-step');
                    registerBody.classList.add('is-plan-step');
                }
                if (formLockNote) {
                    formLockNote.style.display = '';
                }
                document.querySelector('.hero-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function money(n) {
                return new Intl.NumberFormat('es-AR').format(n);
            }

            function getSelectedCard() {
                return cards.find((c) => c.classList.contains('selected')) || cards[0];
            }

            function syncRubroSelection() {
                if (!rubroInput || !rubroHidden) {
                    return;
                }
                const key = (rubroInput.value || '').trim().toLocaleLowerCase('es-AR');
                rubroHidden.value = rubroMap.get(key) || '';
            }

            function getBillingCycle() {
                return billingCycleHidden?.value === 'anual' ? 'anual' : 'mensual';
            }

            function syncBillingButtons() {
                const current = getBillingCycle();
                billingOptions.forEach((option) => {
                    option.classList.toggle('active', option.dataset.billing === current);
                    option.setAttribute('aria-selected', option.dataset.billing === current ? 'true' : 'false');
                });
            }

            function updatePrice() {
                const selected = getSelectedCard();
                const base = parseInt(selected.dataset.base || '0', 10);
                const perService = parseInt(selected.dataset.service || '0', 10);
                const perUser = parseInt(selected.dataset.user || '0', 10);
                const services = Math.max(1, parseInt(qtyServicios.value || '1', 10));
                const users = Math.max(1, parseInt(qtyUsuarios.value || '1', 10));
                const monthlyTotal = base + (services * perService) + (users * perUser);
                const annualTotal = monthlyTotal * 10;
                const billingCycle = getBillingCycle();
                const displayTotal = billingCycle === 'anual' ? annualTotal : monthlyTotal;

                qtyServicios.value = services;
                qtyUsuarios.value = users;
                planHidden.value = selected.dataset.plan || 'Basico';
                serviciosHidden.value = String(services);
                usuariosHidden.value = String(users);
                priceLine.textContent = billingCycle === 'anual'
                    ? 'Total estimado: $' + money(displayTotal) + ' / anio'
                    : 'Total estimado: $' + money(displayTotal) + ' / mes';

                const meta = planMeta[planHidden.value] || planMeta.Basico;
                if (planDetailTitle) {
                    planDetailTitle.textContent = planHidden.value;
                }
                if (planDetailText) {
                    planDetailText.textContent = billingCycle === 'anual'
                        ? meta.text + ' Con facturacion anual obtienes una bonificacion equivalente a 2 meses.'
                        : meta.text;
                }
                if (planDetailList) {
                    planDetailList.innerHTML = meta.items.map((item) => '<li>' + item + '</li>').join('');
                }
                syncBillingButtons();
            }

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    cards.forEach((c) => c.classList.remove('selected'));
                    card.classList.add('selected');
                    updatePrice();
                });
            });

            qtyServicios.addEventListener('input', updatePrice);
            qtyUsuarios.addEventListener('input', updatePrice);
            if (rubroInput) {
                rubroInput.addEventListener('input', syncRubroSelection);
                rubroInput.addEventListener('change', syncRubroSelection);
            }
            billingOptions.forEach((option) => {
                option.addEventListener('click', () => {
                    if (billingCycleHidden) {
                        billingCycleHidden.value = option.dataset.billing || 'mensual';
                    }
                    updatePrice();
                });
            });
            if (goToRegisterStep) {
                goToRegisterStep.addEventListener('click', unlockRegisterStep);
            }
            backToPlanButtons.forEach((button) => {
                button.addEventListener('click', goBackToPlanStep);
            });
            syncBillingButtons();
            syncRubroSelection();
            updatePrice();
            if (razonSocialInput) {
                razonSocialInput.addEventListener('input', updateBaseAndLinkPreview);
            }
            if (linkPathInput) {
                linkPathInput.addEventListener('input', function () {
                    linkEdited = true;
                    updateBaseAndLinkPreview();
                });
            }
            updateBaseAndLinkPreview();

            document.querySelectorAll('.form-panel .form-group input, #btn-login').forEach(function (el) {
                el.disabled = true;
            });
        });
    </script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>

</html>
