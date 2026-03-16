<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MiSocio — Sistema de Gestión para tu Negocio</title>
    <meta name="description" content="Controla tus ventas, compras, inventario y más. La solución todo-en-uno para pequeños negocios bolivianos." />

    <!-- Favicon mascota -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/mascota-sonrisa.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/images/mascota-sonrisa.png') }}" />

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,400;6..12,600;6..12,700;6..12,800;6..12,900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet" />

    <style>
        :root {
            --brand:       #308e87;
            --brand-dark:  #1f6b65;
            --brand-light: #e6f5f4;
            --brand-glow:  rgba(48,142,135,.25);
            --accent:      #f3914f;
            --dark:        #0f1923;
            --text:        #334155;
            --muted:       #64748b;
            --surface:     #f8fffe;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Nunito Sans', sans-serif; color: var(--text); background: #fff; overflow-x: hidden; }

        /* ─── NAVBAR ─────────────────────────────────── */
        .lp-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            padding: 0 5%; height: 68px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255,255,255,.85); backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(48,142,135,.12); transition: box-shadow .3s;
        }
        .lp-nav.scrolled { box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .lp-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .lp-logo-icon {
            width: 40px; height: 40px; border-radius: 10px;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px var(--brand-glow);
        }
        .lp-logo-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .lp-logo-icon i { color: #fff; font-size: 18px; }
        .lp-logo-text { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.35rem; color: var(--dark); }
        .lp-logo-text span { color: var(--brand); }
        .lp-nav-links { display: flex; align-items: center; gap: 8px; }
        .btn-nav-login {
            padding: 8px 20px; border-radius: 8px; border: 1.5px solid var(--brand);
            color: var(--brand); background: transparent;
            font-weight: 700; font-size: .9rem; text-decoration: none; transition: all .2s;
        }
        .btn-nav-login:hover { background: var(--brand); color: #fff; }
        .btn-nav-cta {
            padding: 8px 22px; border-radius: 8px;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff; font-weight: 700; font-size: .9rem; text-decoration: none;
            transition: all .2s; box-shadow: 0 4px 14px var(--brand-glow);
        }
        .btn-nav-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 20px var(--brand-glow); color: #fff; }

        /* ─── HERO ───────────────────────────────────── */
        .hero {
            min-height: 100vh; padding: 120px 5% 80px;
            display: flex; align-items: center;
            background: linear-gradient(145deg, #f0faf9 0%, #e6f5f4 50%, #fff 100%);
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -100px; right: -150px;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(48,142,135,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-inner {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr;
            align-items: center; gap: 60px; width: 100%;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand-light); border: 1px solid rgba(48,142,135,.3);
            border-radius: 50px; padding: 6px 16px;
            font-size: .8rem; font-weight: 700; color: var(--brand);
            margin-bottom: 24px; animation: fadeInUp .6s ease both;
        }
        .hero-badge .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--brand); animation: pulse-dot 2s infinite; }
        .hero-title {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2.2rem, 4.5vw, 3.8rem); font-weight: 900; line-height: 1.1;
            color: var(--dark); margin-bottom: 20px; animation: fadeInUp .7s ease .1s both;
        }
        .hero-title .highlight { color: var(--brand); position: relative; display: inline-block; }
        .hero-title .highlight::after {
            content: ''; position: absolute; bottom: -4px; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--accent)); border-radius: 2px;
        }
        .hero-sub { font-size: 1.1rem; color: var(--muted); line-height: 1.7; margin-bottom: 36px; animation: fadeInUp .7s ease .2s both; }
        .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; animation: fadeInUp .7s ease .3s both; }
        .btn-hero-primary {
            padding: 14px 32px; border-radius: 10px;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff; font-weight: 800; font-size: 1rem;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 8px 24px var(--brand-glow); transition: all .25s;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px var(--brand-glow); color: #fff; }
        .btn-hero-secondary {
            padding: 14px 32px; border-radius: 10px; border: 2px solid rgba(48,142,135,.3);
            color: var(--brand); font-weight: 700; font-size: 1rem;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            background: transparent; transition: all .25s;
        }
        .btn-hero-secondary:hover { border-color: var(--brand); background: var(--brand-light); color: var(--brand); }
        .hero-stats { display: flex; gap: 28px; margin-top: 40px; animation: fadeInUp .7s ease .4s both; }
        .hero-stat { text-align: center; }
        .hero-stat strong { display: block; font-family: 'Poppins', sans-serif; font-size: 1.7rem; font-weight: 900; color: var(--brand); }
        .hero-stat span { font-size: .8rem; color: var(--muted); font-weight: 600; }

        /* Mockup */
        .hero-visual { position: relative; animation: fadeInRight .8s ease .2s both; }
        .hero-mockup {
            background: #fff; border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,.15), 0 0 0 1px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .hero-mascot-corner {
            position: absolute; bottom: -30px; right: -30px;
            height: 190px; width: auto; object-fit: contain;
            filter: drop-shadow(0 10px 24px rgba(0,0,0,.18));
            animation: float 5s ease-in-out infinite;
            pointer-events: none;
        }
        .mockup-topbar {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;
        }
        .mockup-dots { display: flex; gap: 6px; }
        .mockup-dots span { width: 10px; height: 10px; border-radius: 50%; }
        .mockup-dots span:nth-child(1) { background: #ff5f57; }
        .mockup-dots span:nth-child(2) { background: #ffbd2e; }
        .mockup-dots span:nth-child(3) { background: #27c93f; }
        .mockup-title { color: rgba(255,255,255,.9); font-size: .8rem; font-weight: 700; }
        .mockup-body { padding: 20px; }
        .mockup-metric-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 16px; }
        .mockup-metric { background: var(--surface); border-radius: 10px; padding: 12px; border: 1px solid rgba(48,142,135,.1); }
        .mockup-metric .label { font-size: .65rem; color: var(--muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .mockup-metric .value { font-family: 'Poppins', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--dark); }
        .mockup-metric .value.green { color: var(--brand); }
        .mockup-metric .value.orange { color: var(--accent); }
        .mockup-chart-bar { height: 8px; border-radius: 4px; background: var(--brand-light); margin-bottom: 8px; overflow: hidden; }
        .mockup-chart-bar .fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--brand), var(--accent)); }
        .mockup-sale-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .mockup-sale-row:last-child { border-bottom: none; }
        .mockup-sale-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .7rem; }
        .mockup-sale-info { flex: 1; }
        .mockup-sale-info .name { font-size: .72rem; font-weight: 700; color: var(--dark); }
        .mockup-sale-info .time { font-size: .62rem; color: var(--muted); }
        .mockup-sale-amount { font-size: .8rem; font-weight: 800; color: var(--brand); }

        /* Floating badges */
        .float-badge {
            position: absolute; background: #fff; border-radius: 12px; padding: 10px 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12); display: flex; align-items: center; gap: 8px;
            font-size: .78rem; font-weight: 700; animation: float 4s ease-in-out infinite; white-space: nowrap;
        }
        .float-badge.top-left { top: -20px; left: -30px; animation-delay: 1s; }
        .float-badge.bottom-right { bottom: -10px; right: -25px; animation-delay: 2s; }
        .float-badge .fbi { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }

        /* ─── SECTIONS ────────────────────────────────── */
        .section { padding: 90px 5%; }
        .section-inner { max-width: 1200px; margin: 0 auto; }
        .section-tag {
            display: inline-block; background: var(--brand-light); color: var(--brand);
            padding: 5px 14px; border-radius: 50px;
            font-size: .78rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 14px;
        }
        .section-title { font-family: 'Poppins', sans-serif; font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 800; color: var(--dark); line-height: 1.2; margin-bottom: 16px; }
        .section-desc { font-size: 1.05rem; color: var(--muted); max-width: 560px; line-height: 1.7; }

        /* Features */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 56px; }
        .feature-card {
            background: #fff; border: 1.5px solid rgba(48,142,135,.1); border-radius: 16px;
            padding: 28px; transition: all .3s; position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--brand), var(--accent));
            transform: scaleX(0); transition: transform .3s; transform-origin: left;
        }
        .feature-card:hover { transform: translateY(-6px); border-color: rgba(48,142,135,.3); box-shadow: 0 20px 50px rgba(48,142,135,.12); }
        .feature-card:hover::before { transform: scaleX(1); }
        .feature-icon { width: 54px; height: 54px; border-radius: 14px; background: var(--brand-light); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--brand); margin-bottom: 18px; }
        .feature-card h3 { font-size: 1.05rem; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .feature-card p { font-size: .9rem; color: var(--muted); line-height: 1.6; }

        /* Steps */
        .steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px; margin-top: 56px; }
        .step-card { text-align: center; padding: 20px; }
        .step-number {
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff; font-family: 'Poppins', sans-serif; font-size: 1.4rem; font-weight: 900;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 18px;
            box-shadow: 0 8px 20px var(--brand-glow);
        }
        .step-card h3 { font-size: 1rem; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .step-card p { font-size: .88rem; color: var(--muted); line-height: 1.6; }

        /* Pricing */
        .pricing-section { background: linear-gradient(180deg, #f0faf9 0%, #fff 100%); }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-top: 56px; align-items: start; }
        .price-card { background: #fff; border-radius: 20px; border: 2px solid rgba(48,142,135,.1); padding: 32px 28px; transition: all .3s; position: relative; overflow: hidden; }
        .price-card.popular { border-color: var(--brand); box-shadow: 0 20px 60px var(--brand-glow); transform: scale(1.04); }
        .price-card:hover:not(.popular) { transform: translateY(-6px); border-color: rgba(48,142,135,.3); box-shadow: 0 20px 50px rgba(48,142,135,.1); }
        .popular-badge { position: absolute; top: 0; right: 0; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; font-size: .7rem; font-weight: 800; padding: 6px 18px; border-radius: 0 18px 0 12px; text-transform: uppercase; letter-spacing: .08em; }
        .free-badge { position: absolute; top: 0; right: 0; background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; font-size: .7rem; font-weight: 800; padding: 6px 18px; border-radius: 0 18px 0 12px; text-transform: uppercase; letter-spacing: .08em; }
        .price-plan-name { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--brand); margin-bottom: 12px; }
        .price-amount { display: flex; align-items: flex-end; gap: 4px; margin-bottom: 4px; }
        .price-amount .currency { font-size: 1.1rem; font-weight: 700; color: var(--muted); padding-bottom: 8px; }
        .price-amount .amount { font-family: 'Poppins', sans-serif; font-size: 3rem; font-weight: 900; color: var(--dark); line-height: 1; }
        .price-duration { font-size: .82rem; color: var(--muted); margin-bottom: 24px; }
        .price-divider { height: 1px; background: rgba(48,142,135,.1); margin: 20px 0; }
        .price-features { list-style: none; margin-bottom: 28px; }
        .price-features li { display: flex; align-items: center; gap: 10px; font-size: .88rem; color: var(--text); padding: 5px 0; }
        .price-features li i { color: var(--brand); font-size: .8rem; width: 16px; }
        .btn-price { display: block; width: 100%; padding: 13px; border-radius: 10px; font-weight: 800; font-size: .95rem; text-align: center; text-decoration: none; transition: all .25s; border: 2px solid var(--brand); color: var(--brand); background: transparent; }
        .btn-price:hover { background: var(--brand); color: #fff; }
        .btn-price.primary { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border-color: transparent; box-shadow: 0 8px 20px var(--brand-glow); }
        .btn-price.primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px var(--brand-glow); }
        .btn-price.free { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border-color: transparent; }
        .btn-price.free:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(34,197,94,.3); }

        /* CTA Banner */
        .cta-section { background: linear-gradient(135deg, var(--dark) 0%, #1a2e38 50%, #0f2027 100%); padding: 90px 5%; text-align: center; position: relative; overflow: hidden; }
        .cta-section::before { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(48,142,135,.2) 0%, transparent 70%); }
        .cta-section .section-inner { position: relative; z-index: 1; }
        .cta-section h2 { font-family: 'Poppins', sans-serif; font-size: clamp(1.8rem, 3.5vw, 3rem); font-weight: 900; color: #fff; margin-bottom: 16px; }
        .cta-section p { font-size: 1.1rem; color: rgba(255,255,255,.7); margin-bottom: 36px; max-width: 520px; margin-left: auto; margin-right: auto; line-height: 1.7; }
        .btn-cta { display: inline-flex; align-items: center; gap: 10px; padding: 16px 40px; border-radius: 12px; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; font-weight: 800; font-size: 1.05rem; text-decoration: none; box-shadow: 0 10px 30px var(--brand-glow); transition: all .25s; }
        .btn-cta:hover { transform: translateY(-3px); box-shadow: 0 16px 40px var(--brand-glow); color: #fff; }

        /* Footer */
        .lp-footer { background: var(--dark); padding: 48px 5% 28px; color: rgba(255,255,255,.6); }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 32px; margin-bottom: 36px; }
        .footer-brand .lp-logo-text { color: #fff; }
        .footer-brand p { font-size: .85rem; color: rgba(255,255,255,.5); margin-top: 8px; max-width: 240px; line-height: 1.6; }
        .footer-links h4 { color: rgba(255,255,255,.9); font-size: .85rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; }
        .footer-links ul { list-style: none; }
        .footer-links ul li { margin-bottom: 8px; }
        .footer-links ul li a { color: rgba(255,255,255,.5); text-decoration: none; font-size: .85rem; transition: color .2s; }
        .footer-links ul li a:hover { color: var(--brand); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,.08); padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .footer-bottom p { font-size: .82rem; }
        .footer-bottom .made { color: rgba(255,255,255,.4); font-size: .8rem; }
        .footer-bottom .made span { color: var(--brand); }

        /* ─── MASCOTAS ────────────────────────────────── */
        .hero-mascot-img {
            height: 520px; max-height: 66vh; width: auto;
            display: block; margin: 0 auto; object-fit: contain;
            filter: drop-shadow(0 24px 48px rgba(48,142,135,.22));
            animation: float 5s ease-in-out infinite;
        }
        .float-mascot { object-fit: contain; flex-shrink: 0; filter: drop-shadow(0 10px 20px rgba(48,142,135,.15)); }
        .section-header-flex { display: flex; align-items: center; justify-content: space-between; gap: 48px; }
        .section-header-flex > div { flex: 1; }
        .section-mascot { height: 230px; animation: float 6s ease-in-out infinite 1s; }
        .steps-mascot-wrap { display: flex; align-items: center; gap: 48px; margin-top: 48px; }
        .steps-mascot-wrap .steps-grid { flex: 1; margin-top: 0; }
        .steps-side-mascot { height: 290px; animation: float 7s ease-in-out infinite .5s; }
        .cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 60px; text-align: left; }
        .cta-text { flex: 1; }
        .cta-text h2 { text-align: left; }
        .cta-text p { text-align: left; margin-left: 0; margin-right: 0; }
        .cta-mascot { height: 360px; animation: float 5s ease-in-out infinite 1.5s; }
        .footer-mascot { height: 120px; margin-top: 10px; animation: float 8s ease-in-out infinite 2s; display: block; }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .6; transform: scale(1.3); } }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Responsive */
        @media (max-width: 900px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero-actions { justify-content: center; }
            .hero-stats { justify-content: center; }
            .hero-visual { display: none; }
            .section-desc { max-width: 100%; }
            .footer-top { flex-direction: column; }
            .section-header-flex { flex-direction: column-reverse; }
            .section-mascot { height: 160px; }
            .steps-mascot-wrap { flex-direction: column; }
            .steps-side-mascot { height: 190px; }
            .cta-inner { flex-direction: column; text-align: center; }
            .cta-text h2 { text-align: center; }
            .cta-text p { text-align: center; margin-left: auto; margin-right: auto; }
            .cta-mascot { height: 220px; }
            .footer-mascot { display: none; }
        }
        @media (max-width: 600px) {
            .lp-nav { padding: 0 4%; }
            .hero { padding: 100px 4% 60px; }
            .section { padding: 60px 4%; }
            .pricing-grid { grid-template-columns: 1fr; }
            .price-card.popular { transform: none; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="lp-nav" id="lpNav">
    <a href="/" class="lp-logo">
        <div class="lp-logo-icon">
            <img src="{{ asset('assets/images/mascota-sonrisa.png') }}" alt="MiSocio" onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#308e87,#1f6b65)';this.parentElement.innerHTML='<i class=&quot;fa-solid fa-store&quot; style=&quot;color:#fff;font-size:18px&quot;></i>'" />
        </div>
        <span class="lp-logo-text">Mi<span>Socio</span></span>
    </a>
    <div class="lp-nav-links">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-nav-login">Mi Panel</a>
        @else
            <a href="{{ route('login') }}" class="btn-nav-login">Iniciar Sesión</a>
            @if(Route::has('register'))
                <a href="{{ route('register') }}" class="btn-nav-cta">Crear Cuenta Gratis</a>
            @endif
        @endauth
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <div class="hero-badge"><span class="dot"></span> Sistema de Gestión 100% Boliviano</div>
            <h1 class="hero-title">Gestiona tu negocio<br>como un <span class="highlight">profesional</span></h1>
            <p class="hero-sub">Ventas, compras, inventario, clientes y reportes en un solo lugar. Fácil de usar, sin complicaciones y diseñado para emprendedores bolivianos.</p>
            <div class="hero-actions">
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-hero-primary">
                        <i class="fa-solid fa-rocket"></i> Empezar Gratis — 30 días
                    </a>
                @endif
                <a href="#planes" class="btn-hero-secondary"><i class="fa-solid fa-tag"></i> Ver Planes</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><strong>100%</strong><span>En la nube</span></div>
                <div class="hero-stat"><strong>24/7</strong><span>Disponible</span></div>
                <div class="hero-stat"><strong>0 Bs.</strong><span>Para empezar</span></div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="float-badge top-left">
                <div class="fbi" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div><div style="font-size:.65rem;color:#64748b;font-weight:600">Ventas hoy</div><div style="color:#0f1923">+23% este mes</div></div>
            </div>
            <div class="hero-mockup">
                <div class="mockup-topbar">
                    <div class="mockup-dots"><span></span><span></span><span></span></div>
                    <div class="mockup-title">MiSocio — Panel Principal</div>
                    <div style="width:48px"></div>
                </div>
                <div class="mockup-body">
                    <div class="mockup-metric-row">
                        <div class="mockup-metric"><div class="label">Ventas hoy</div><div class="value green">Bs. 842</div></div>
                        <div class="mockup-metric"><div class="label">Productos</div><div class="value">127</div></div>
                        <div class="mockup-metric"><div class="label">Clientes</div><div class="value orange">48</div></div>
                    </div>
                    <div style="font-size:.65rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Actividad semanal</div>
                    <div class="mockup-chart-bar"><div class="fill" style="width:72%"></div></div>
                    <div class="mockup-chart-bar"><div class="fill" style="width:45%"></div></div>
                    <div class="mockup-chart-bar"><div class="fill" style="width:88%"></div></div>
                    <div style="font-size:.65rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin:14px 0 6px">Últimas ventas</div>
                    <div class="mockup-sale-row">
                        <div class="mockup-sale-icon" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-bottle-water" style="font-size:.7rem"></i></div>
                        <div class="mockup-sale-info"><div class="name">Coca-Cola 2L</div><div class="time">hace 3 min</div></div>
                        <div class="mockup-sale-amount">Bs. 14</div>
                    </div>
                    <div class="mockup-sale-row">
                        <div class="mockup-sale-icon" style="background:#dbeafe;color:#2563eb"><i class="fa-solid fa-bottle-water" style="font-size:.7rem"></i></div>
                        <div class="mockup-sale-info"><div class="name">Agua Vital 600ml x6</div><div class="time">hace 8 min</div></div>
                        <div class="mockup-sale-amount">Bs. 22</div>
                    </div>
                    <div class="mockup-sale-row">
                        <div class="mockup-sale-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-bottle-water" style="font-size:.7rem"></i></div>
                        <div class="mockup-sale-info"><div class="name">Manzana Sprite 1.5L</div><div class="time">hace 15 min</div></div>
                        <div class="mockup-sale-amount">Bs. 10</div>
                    </div>
                </div>
            </div>
            <!-- Mascota pequeña en esquina inferior derecha del mockup -->
            <img src="{{ asset('assets/images/mascota-pulgar.png') }}" alt="" class="hero-mascot-corner" onerror="this.style.display='none'" />
        </div>
    </div>
</section>

<!-- CARACTERÍSTICAS -->
<section class="section" id="caracteristicas">
    <div class="section-inner">
        <div class="reveal section-header-flex">
            <div>
                <span class="section-tag">¿Qué incluye?</span>
                <h2 class="section-title">Todo lo que necesitas<br>para crecer</h2>
                <p class="section-desc">Desde el primer día tendrás todo bajo control, sin necesidad de ser experto en tecnología.</p>
            </div>
            <img src="{{ asset('assets/images/mascota-saludo.png') }}" alt="" class="section-mascot float-mascot" onerror="this.style.display='none'" />
        </div>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-cash-register"></i></div>
                <h3>Punto de Venta</h3>
                <p>Registra ventas rápidamente, agrega descuentos, gestiona el carrito y genera tickets en segundos.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <h3>Control de Inventario</h3>
                <p>Conoce al detalle las existencias con alertas de stock bajo y movimientos en tiempo real.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-cart-flatbed"></i></div>
                <h3>Gestión de Compras</h3>
                <p>Registra compras a proveedores, controla costos y mantén actualizado el inventario automáticamente.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-users"></i></div>
                <h3>Gestión de Clientes</h3>
                <p>Guarda el historial de compras de cada cliente y lleva el seguimiento de créditos o préstamos pendientes.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Reportes y Kardex</h3>
                <p>Visualiza tus ganancias, movimientos y el historial completo de cada producto con reportes claros.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                <h3>Acceso desde cualquier lugar</h3>
                <p>Funciona en celular, tablet o computadora. Instálalo como app y úsalo desde donde estés.</p>
            </div>
        </div>
    </div>
</section>

<!-- CÓMO FUNCIONA -->
<section class="section" style="background:linear-gradient(180deg,#fff 0%,#f0faf9 100%)">
    <div class="section-inner">
        <div class="reveal" style="text-align:center">
            <span class="section-tag">Proceso</span>
            <h2 class="section-title">Empieza en 3 pasos</h2>
            <p class="section-desc" style="margin:0 auto">Sin contratos, sin complicaciones. Tu tienda lista en minutos.</p>
        </div>
        <div class="steps-mascot-wrap">
            <img src="{{ asset('assets/images/mascota-senalando.png') }}" alt="" class="steps-side-mascot float-mascot" onerror="this.style.display='none'" />
            <div class="steps-grid">
                <div class="step-card reveal">
                    <div class="step-number">1</div>
                    <h3>Crea tu cuenta</h3>
                    <p>Regístrate con tu número de celular y un PIN de 4 dígitos. Sin tarjeta, sin trámites.</p>
                </div>
                <div class="step-card reveal">
                    <div class="step-number">2</div>
                    <h3>Configura tu tienda</h3>
                    <p>Agrega tus productos, categorías y personaliza el color de tu tienda en minutos.</p>
                </div>
                <div class="step-card reveal">
                    <div class="step-number">3</div>
                    <h3>¡Empieza a vender!</h3>
                    <p>Registra tus primeras ventas desde el mismo día. Todo queda guardado automáticamente.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PLANES -->
<section class="section pricing-section" id="planes">
    <div class="section-inner">
        <div class="reveal" style="text-align:center">
            <span class="section-tag">Precios</span>
            <h2 class="section-title">Planes para cada negocio</h2>
            <p class="section-desc" style="margin:0 auto">Empieza gratis y escala cuando lo necesites. Sin sorpresas.</p>
        </div>

        @if($planes->count() > 0)
        <div class="pricing-grid">
            @foreach($planes as $plan)
            @php
                $isDemo    = $plan->precio == 0;
                $isPopular = !$isDemo && $plan->duracion_meses == 12;
            @endphp
            <div class="price-card {{ $isPopular ? 'popular' : '' }} reveal">
                @if($isPopular)
                    <div class="popular-badge"><i class="fa-solid fa-star"></i> Más popular</div>
                @elseif($isDemo)
                    <div class="free-badge"><i class="fa-solid fa-gift"></i> Gratis</div>
                @endif
                <div class="price-plan-name">{{ $plan->nombre }}</div>
                <div class="price-amount">
                    <span class="currency">Bs.</span>
                    <span class="amount">{{ number_format($plan->precio, 0) }}</span>
                </div>
                <div class="price-duration">
                    @if($isDemo)
                        30 días de prueba gratuita
                    @else
                        por {{ $plan->duracion_texto ?? ($plan->duracion_meses . ' mes(es)') }}
                    @endif
                </div>
                <div class="price-divider"></div>
                @if($plan->caracteristicas)
                <ul class="price-features">
                    @foreach($plan->caracteristicas as $feat)
                    <li><i class="fa-solid fa-check-circle"></i> {{ $feat }}</li>
                    @endforeach
                </ul>
                @endif
                @if(Route::has('register'))
                <a href="{{ route('register') }}" class="btn-price {{ $isPopular ? 'primary' : ($isDemo ? 'free' : '') }}">
                    @if($isDemo)<i class="fa-solid fa-rocket"></i> Comenzar Gratis
                    @elseif($isPopular)<i class="fa-solid fa-bolt"></i> Elegir este Plan
                    @else<i class="fa-solid fa-arrow-right"></i> Seleccionar
                    @endif
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="reveal" style="text-align:center;padding:60px 20px;color:var(--muted)">
            <i class="fa-solid fa-tag" style="font-size:2.5rem;color:var(--brand);margin-bottom:16px;display:block"></i>
            <p>Los planes están disponibles al registrarte.</p>
            @if(Route::has('register'))
                <a href="{{ route('register') }}" class="btn-hero-primary" style="display:inline-flex;margin-top:20px">
                    <i class="fa-solid fa-rocket"></i> Crear cuenta gratis
                </a>
            @endif
        </div>
        @endif

        <div class="reveal" style="text-align:center;margin-top:32px;color:var(--muted);font-size:.88rem">
            <i class="fa-solid fa-shield-halved" style="color:var(--brand);margin-right:6px"></i>
            Sin contratos. Cancela cuando quieras. Soporte incluido en todos los planes.
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<section class="cta-section">
    <div class="section-inner cta-inner">
        <div class="cta-text">
            <h2 class="reveal">¿Listo para llevar tu negocio<br>al siguiente nivel?</h2>
            <p class="reveal">Únete a los emprendedores bolivianos que ya controlan sus negocios con MiSocio. Empieza hoy, gratis.</p>
            @if(Route::has('register'))
                <a href="{{ route('register') }}" class="btn-cta reveal">
                    <i class="fa-solid fa-rocket"></i> Crear mi cuenta gratis
                    <i class="fa-solid fa-arrow-right" style="font-size:.85rem"></i>
                </a>
            @endif
        </div>
        <img src="{{ asset('assets/images/mascota-bienvenida.png') }}" alt="" class="cta-mascot float-mascot" onerror="this.style.display='none'" />
    </div>
</section>

<!-- FOOTER -->
<footer class="lp-footer">
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="/" class="lp-logo" style="margin-bottom:10px;display:inline-flex">
                    <div class="lp-logo-icon">
                        <img src="{{ asset('assets/images/mascota-sonrisa.png') }}" alt="MiSocio" onerror="this.style.display='none';this.parentElement.innerHTML='<i class=&quot;fa-solid fa-store&quot; style=&quot;color:#308e87;font-size:22px&quot;></i>'" />
                    </div>
                    <span class="lp-logo-text">Mi<span>Socio</span></span>
                </a>
                <p>La herramienta de gestión para pequeños negocios bolivianos. Simple, rápida y accesible.</p>
                <img src="{{ asset('assets/images/mascota-sonrisa.png') }}" alt="" class="footer-mascot float-mascot" onerror="this.style.display='none'" />
            </div>
            <div class="footer-links">
                <h4>Acceso</h4>
                <ul>
                    @if(Route::has('login'))<li><a href="{{ route('login') }}">Iniciar Sesión</a></li>@endif
                    @if(Route::has('register'))<li><a href="{{ route('register') }}">Crear Cuenta</a></li>@endif
                </ul>
            </div>
            <div class="footer-links">
                <h4>Sistema</h4>
                <ul>
                    <li><a href="#caracteristicas">Características</a></li>
                    <li><a href="#planes">Planes y Precios</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} MiSocio — Todos los derechos reservados.</p>
            <p class="made">Hecho con <span>♥</span> en Bolivia</p>
        </div>
    </div>
</footer>

<script>
    const nav = document.getElementById('lpNav');
    window.addEventListener('scroll', () => { nav.classList.toggle('scrolled', window.scrollY > 20); });
    const revealEls = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) { setTimeout(() => entry.target.classList.add('visible'), i * 80); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.12 });
    revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>
