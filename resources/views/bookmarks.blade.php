@extends('layouts.frontend')

@section('title', 'My Saved Learning Space')

@push('styles')
    <style>
        :root {
            --font-display: 'Sora', sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background:
                radial-gradient(circle at 12% 12%, rgba(56, 189, 248, 0.20), transparent 30%),
                radial-gradient(circle at 84% 8%, rgba(37, 99, 235, 0.16), transparent 28%),
                radial-gradient(circle at 66% 52%, rgba(16, 185, 129, 0.08), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, #eef5ff 50%, #ffffff 100%) !important;
            min-height: 100vh;
            color: #0f172a;
        }

        .bookmarks-page {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding-top: 72px;
        }

        .bookmarks-page::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.035) 1px, transparent 1px);
            background-size: 58px 58px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 78%);
        }

        .bookmarks-page::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -2;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.055) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.6), transparent 72%);
        }

        .bm-glow {
            position: fixed;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            pointer-events: none;
            z-index: -1;
            filter: blur(8px);
            opacity: 0.72;
            animation: bmFloat 12s ease-in-out infinite alternate;
        }

        .bm-glow.one { left: -150px; top: 110px; background: rgba(56, 189, 248, 0.22); }
        .bm-glow.two { right: -160px; top: 260px; background: rgba(37, 99, 235, 0.18); animation-delay: -4s; }
        .bm-glow.three { left: 46%; bottom: 6%; background: rgba(16, 185, 129, 0.10); animation-delay: -8s; }

        @keyframes bmFloat {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to { transform: translate3d(36px, -28px, 0) scale(1.08); }
        }

        .bm-container {
            width: min(1220px, calc(100% - 40px));
            margin: 0 auto;
        }

        .bm-hero {
            position: relative;
            padding: 90px 0 54px;
        }

        .bm-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(360px, 0.78fr);
            gap: 32px;
            align-items: center;
        }

        .bm-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 34px;
            padding: 7px 13px;
            border: 1px solid rgba(37, 99, 235, 0.14);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.66);
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            box-shadow: 0 12px 34px rgba(37, 99, 235, 0.09);
        }

        .bm-title {
            margin-top: 18px;
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 3.9rem);
            line-height: 1.08;
            letter-spacing: 0;
            color: #0f172a;
        }

        .bm-title span {
            display: block;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 56%, #10b981 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bm-subtitle {
            max-width: 700px;
            margin-top: 18px;
            color: #526176;
            font-size: clamp(1rem, 1.5vw, 1.1rem);
            line-height: 1.75;
        }

        .bm-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .bm-primary-btn,
        .bm-secondary-btn,
        .bm-card-btn,
        .bm-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 44px;
            border-radius: 999px;
            font-weight: 800;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .bm-primary-btn {
            padding: 0 20px;
            color: #ffffff !important;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            box-shadow: 0 18px 36px rgba(37, 99, 235, 0.24);
        }

        .bm-secondary-btn {
            padding: 0 18px;
            color: #1e3a8a;
            border: 1px solid rgba(37, 99, 235, 0.16);
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.08);
        }

        .bm-hero-visual {
            position: relative;
            min-height: 470px;
        }

        .bm-dashboard-orbit {
            position: absolute;
            inset: 0;
            border-radius: 28px;
            border: 1px solid rgba(37, 99, 235, 0.10);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.80), rgba(255, 255, 255, 0.48)),
                radial-gradient(circle at 20% 20%, rgba(56, 189, 248, 0.18), transparent 38%);
            backdrop-filter: blur(24px);
            box-shadow: 0 30px 80px rgba(37, 99, 235, 0.13);
            overflow: hidden;
        }

        .bm-visual-top {
            position: absolute;
            left: 28px;
            right: 28px;
            top: 28px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 58px;
            padding: 12px 14px;
            border: 1px solid rgba(37, 99, 235, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.10);
        }

        .bm-visual-dots { display: flex; gap: 7px; }
        .bm-visual-dots span { width: 10px; height: 10px; border-radius: 999px; background: #38bdf8; box-shadow: 0 0 0 5px rgba(56,189,248,0.11); }
        .bm-visual-dots span:nth-child(2) { background: #10b981; box-shadow: 0 0 0 5px rgba(16,185,129,0.10); }
        .bm-visual-dots span:nth-child(3) { background: #f59e0b; box-shadow: 0 0 0 5px rgba(245,158,11,0.10); }

        .bm-visual-search { flex: 1; max-width: 230px; height: 30px; border-radius: 999px; background: linear-gradient(90deg, rgba(37, 99, 235, 0.10), rgba(56, 189, 248, 0.10)); }

        .bm-floating-card {
            position: absolute;
            z-index: 3;
            width: min(260px, 58%);
            padding: 18px;
            border: 1px solid rgba(37, 99, 235, 0.13);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.74);
            backdrop-filter: blur(20px);
            box-shadow: 0 22px 54px rgba(37, 99, 235, 0.13);
            animation: bmCardFloat 7s ease-in-out infinite alternate;
        }

        .bm-floating-card.card-a { left: 34px; top: 124px; }
        .bm-floating-card.card-b { right: 28px; top: 210px; animation-delay: -2s; }
        .bm-floating-card.card-c { left: 88px; bottom: 50px; animation-delay: -4s; }

        @keyframes bmCardFloat { from { transform: translateY(0); } to { transform: translateY(-16px); } }

        .bm-floating-card strong { display: block; margin-top: 10px; color: #0f172a; font-family: var(--font-display); font-size: 1rem; }
        .bm-floating-card small { display: block; margin-top: 4px; color: #64748b; font-weight: 600; }
        .bm-save-mark { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; color: #2563eb; border-radius: 14px; background: linear-gradient(135deg, rgba(37,99,235,0.13), rgba(56,189,248,0.13)); box-shadow: 0 12px 26px rgba(37,99,235,0.15); }
        .bm-card-line { height: 7px; margin-top: 14px; border-radius: 999px; background: linear-gradient(90deg, rgba(37,99,235,0.20), rgba(56,189,248,0.10)); }
        .bm-card-line.short { width: 64%; }

        .bm-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-top: 36px; }
        .bm-stat-card { position: relative; overflow: hidden; min-height: 140px; padding: 22px; border: 1px solid rgba(37, 99, 235, 0.13); border-radius: 22px; background: linear-gradient(145deg, rgba(255,255,255,0.84), rgba(255,255,255,0.60)); box-shadow: 0 18px 42px rgba(37,99,235,0.09); backdrop-filter: blur(18px); transition: var(--transition); }
        .bm-stat-card::after { content: ""; position: absolute; inset: auto -35px -50px auto; width: 120px; height: 120px; border-radius: 999px; background: rgba(56, 189, 248, 0.15); transition: var(--transition); }
        .bm-stat-card:hover { transform: translateY(-5px); border-color: rgba(37,99,235,0.28); box-shadow: 0 24px 54px rgba(37,99,235,0.15); }
        .bm-stat-card:hover::after { transform: scale(1.28); }
        .bm-stat-card strong { display: block; font-family: var(--font-display); font-size: 2.3rem; line-height: 1; color: #0f172a; }
        .bm-stat-card span { display: block; margin-top: 10px; color: #64748b; font-weight: 700; }

        .bm-section { padding: 92px 0; position: relative; }
        .bm-section-header { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 34px; }
        .bm-section-header h2 { font-family: var(--font-display); font-size: clamp(1.8rem, 3.5vw, 2.8rem); line-height: 1.15; color: #0f172a; letter-spacing: 0; }
        .bm-section-header p { margin-top: 12px; max-width: 620px; color: #64748b; line-height: 1.75; }

        .bm-categories { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 13px; }
        .bm-category { position: relative; min-height: 124px; padding: 18px; overflow: hidden; border: 1px solid rgba(37, 99, 235, 0.13); border-radius: 18px; background: linear-gradient(145deg, rgba(255,255,255,0.84), rgba(255,255,255,0.58)); color: #334155; text-align: left; box-shadow: 0 14px 36px rgba(37,99,235,0.07); backdrop-filter: blur(16px); transition: var(--transition); border: 1px solid rgba(37,99,235,0.13); cursor: pointer; }
        .bm-category::before { content: ""; position: absolute; inset: 0; opacity: 0; background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(56, 189, 248, 0.10)); transition: var(--transition); }
        .bm-category:hover, .bm-category.active { transform: translateY(-4px); border-color: rgba(37, 99, 235, 0.42); box-shadow: 0 14px 40px rgba(37,99,235,0.12); color: #1d4ed8; }
        .bm-category:hover::before, .bm-category.active::before { opacity: 1; }
        .bm-category span { position: relative; z-index: 1; display: grid; place-items: center; width: 38px; height: 38px; margin-bottom: 14px; border-radius: 14px; color: #2563eb; background: rgba(37, 99, 235, 0.10); }
        .bm-category strong { position: relative; z-index: 1; display: block; font-size: 0.96rem; line-height: 1.2; }
        .bm-category small { position: relative; z-index: 1; display: block; margin-top: 5px; color: #64748b; font-weight: 700; }

        .bm-toolbar { display: grid; grid-template-columns: minmax(240px, 1fr) 180px 160px; gap: 12px; padding: 14px; border: 1px solid rgba(37, 99, 235, 0.12); border-radius: 22px; background: linear-gradient(145deg, rgba(255,255,255,0.82), rgba(255,255,255,0.62)); backdrop-filter: blur(18px); box-shadow: 0 18px 46px rgba(37,99,235,0.08); margin-bottom: 20px; }
        .bm-control { display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 0 14px; border: 1px solid rgba(37, 99, 235, 0.11); border-radius: 14px; background: rgba(255, 255, 255, 0.76); color: #64748b; transition: var(--transition); }
        .bm-control:focus-within { border-color: rgba(37, 99, 235, 0.35); box-shadow: 0 12px 28px rgba(37,99,235,0.10); }
        .bm-control input, .bm-control select { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; color: #0f172a; font-size: 0.95rem; font-weight: 600; }

        .bm-dashboard { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .bm-saved-card { position: relative; overflow: hidden; min-height: 285px; display: flex; flex-direction: column; padding: 20px; border: 1px solid rgba(37, 99, 235, 0.13); border-radius: 24px; background: linear-gradient(145deg, rgba(255,255,255,0.88), rgba(255,255,255,0.60)), radial-gradient(circle at 90% 8%, rgba(56, 189, 248, 0.16), transparent 34%); box-shadow: 0 20px 52px rgba(37,99,235,0.09); backdrop-filter: blur(18px); transition: var(--transition); opacity: 1; transform: scale(1); }
        .bm-saved-card::before { content: ""; position: absolute; inset: 0; border-radius: inherit; padding: 1px; background: linear-gradient(135deg, rgba(56, 189, 248, 0.55), transparent 44%, rgba(37, 99, 235, 0.38)); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; opacity: 0; transition: var(--transition); }
        .bm-saved-card:hover { transform: translateY(-7px); box-shadow: 0 28px 70px rgba(37,99,235,0.16); }
        .bm-saved-card:hover::before { opacity: 1; }

        .bm-card-head, .bm-card-footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; position: relative; z-index: 1; }
        .bm-pill { display: inline-flex; align-items: center; gap: 7px; min-height: 30px; padding: 0 11px; border-radius: 999px; background: rgba(37, 99, 235, 0.10); color: #1d4ed8; font-size: 0.78rem; font-weight: 800; }
        .bm-date { color: #94a3b8; font-size: 0.82rem; font-weight: 800; }
        .bm-saved-card h3 { position: relative; z-index: 1; margin-top: 18px; font-family: var(--font-display); font-size: 1.34rem; line-height: 1.2; color: #0f172a; letter-spacing: 0; }
        .bm-preview { position: relative; z-index: 1; margin-top: 12px; color: #64748b; line-height: 1.72; flex: 1; }
        .bm-card-btn { min-height: 40px; padding: 0 14px; color: #ffffff !important; background: linear-gradient(135deg, #2563eb, #38bdf8); box-shadow: 0 12px 26px rgba(37,99,235,0.18); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; font-weight: 800; }
        .bm-icon-btn { width: 40px; min-height: 40px; color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.13); background: rgba(255, 255, 255, 0.70); border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); }
        .bm-icon-btn.saved svg { fill: currentColor; }
        .bm-icon-btn.removing { color: #ef4444; animation: bmPulse 0.55s ease; }
        @keyframes bmPulse { 0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37,99,235,0.28); } 55% { transform: scale(1.12); box-shadow: 0 0 0 12px rgba(37,99,235,0); } 100% { transform: scale(1); } }

        .bm-empty { display: none; margin-top: 22px; padding: 42px 24px; text-align: center; border: 1px solid rgba(37, 99, 235, 0.13); border-radius: 28px; background: rgba(255,255,255,0.72); box-shadow: 0 22px 60px rgba(37,99,235,0.10); backdrop-filter: blur(18px); }
        .bm-empty.visible { display: block; }
        .bm-empty-illustration { position: relative; display: grid; place-items: center; width: 136px; height: 136px; margin: 0 auto 18px; border-radius: 34px; color: #2563eb; background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(56,189,248,0.16)); box-shadow: 0 22px 52px rgba(37,99,235,0.14); animation: bmCardFloat 4s ease-in-out infinite alternate; }
        .bm-empty h3 { font-family: var(--font-display); font-size: 1.55rem; color: #0f172a; }
        .bm-empty p { max-width: 560px; margin: 10px auto 22px; color: #64748b; }

        .bm-reveal { opacity: 0; transform: translateY(40px); transition: opacity 0.7s cubic-bezier(0.4, 0, 0.2, 1), transform 0.7s cubic-bezier(0.4, 0, 0.2, 1); }
        .bm-reveal.visible { opacity: 1; transform: translateY(0); }

        .bm-recent-track { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(250px, 330px); gap: 16px; overflow-x: auto; overscroll-behavior-inline: contain; scroll-snap-type: inline mandatory; padding: 4px 4px 18px; }
        .bm-mini-card { scroll-snap-align: start; min-height: 150px; padding: 18px; border: 1px solid rgba(37, 99, 235, 0.12); border-radius: 20px; background: linear-gradient(145deg, rgba(255,255,255,0.84), rgba(255,255,255,0.58)); box-shadow: 0 18px 40px rgba(37,99,235,0.08); transition: var(--transition); text-decoration: none; color: inherit; }
        .bm-mini-card:hover { transform: translateY(-5px); box-shadow: 0 22px 48px rgba(37,99,235,0.14); }
        .bm-mini-card small { color: #2563eb; font-weight: 800; display: block; }
        .bm-mini-card h3 { margin-top: 12px; font-family: var(--font-display); font-size: 1.05rem; color: #0f172a; }
        .bm-timeline { display: flex; align-items: center; gap: 10px; margin-top: 16px; color: #64748b; font-size: 0.88rem; font-weight: 700; }
        .bm-timeline::before { content: ""; width: 8px; height: 8px; border-radius: 999px; background: #38bdf8; box-shadow: 0 0 0 4px rgba(56,189,248,0.14); }

        .bm-smart-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .bm-smart-card { min-height: 170px; padding: 22px; border: 1px solid rgba(37, 99, 235, 0.12); border-radius: 22px; background: linear-gradient(145deg, rgba(255,255,255,0.86), rgba(255,255,255,0.60)), radial-gradient(circle at 92% 0%, rgba(16, 185, 129, 0.12), transparent 36%); box-shadow: 0 18px 44px rgba(37,99,235,0.08); transition: var(--transition); }
        .bm-smart-card:hover { transform: translateY(-6px); border-color: rgba(37,99,235,0.28); box-shadow: 0 24px 58px rgba(37,99,235,0.15); }
        .bm-smart-icon { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 16px; color: #2563eb; background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(56,189,248,0.13)); box-shadow: 0 12px 28px rgba(37,99,235,0.12); margin-bottom: 16px; }
        .bm-smart-card h3 { font-family: var(--font-display); font-size: 1.1rem; color: #0f172a; font-weight: 700; }
        .bm-smart-card p { margin-top: 8px; color: #64748b; line-height: 1.65; font-size: 0.9rem; }

        @media (max-width: 1120px) {
            .bm-hero-grid { grid-template-columns: 1fr; }
            .bm-hero-visual { min-height: 420px; }
            .bm-stats, .bm-categories, .bm-dashboard, .bm-smart-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 760px) {
            .bm-stats, .bm-categories, .bm-dashboard, .bm-smart-grid, .bm-toolbar { grid-template-columns: 1fr; }
        }


        /* ─── BOOKMARKS PAGE: RESPONSIVE OVERRIDES ───────────────────── */
        @media (max-width: 1120px) {
            .bookmarks-page .bm-hero-grid {
                grid-template-columns: 1fr;
            }

            .bookmarks-page .bm-hero-visual {
                min-height: 420px;
            }

            .bookmarks-page .bm-stats,
            .bookmarks-page .bm-categories,
            .bookmarks-page .bm-dashboard,
            .bookmarks-page .bm-smart-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .bookmarks-page {
                padding-top: 64px;
                overflow-x: hidden;
            }

            .bookmarks-page .bm-container {
                width: calc(100% - 32px);
            }

            .bookmarks-page .bm-hero {
                padding: 48px 0 36px;
            }

            .bookmarks-page .bm-hero-grid {
                gap: 28px;
            }

            .bookmarks-page .bm-kicker {
                max-width: 100%;
                white-space: normal;
                line-height: 1.4;
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-title {
                font-size: clamp(2.15rem, 11vw, 3rem);
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-subtitle {
                font-size: 1rem;
                line-height: 1.7;
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-hero-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                margin-top: 24px;
            }

            .bookmarks-page .bm-primary-btn,
            .bookmarks-page .bm-secondary-btn {
                width: 100%;
                text-align: center;
            }

            .bookmarks-page .bm-hero-visual {
                min-height: auto;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .bookmarks-page .bm-dashboard-orbit {
                position: absolute;
                inset: 0;
            }

            .bookmarks-page .bm-visual-top {
                position: relative;
                left: auto;
                right: auto;
                top: auto;
                width: 100%;
                min-height: 52px;
                padding: 10px 12px;
            }

            .bookmarks-page .bm-visual-search {
                min-width: 0;
            }

            .bookmarks-page .bm-floating-card {
                position: relative;
                inset: auto !important;
                width: 100%;
                max-width: 100%;
                animation: none;
            }

            .bookmarks-page .bm-stats,
            .bookmarks-page .bm-categories,
            .bookmarks-page .bm-dashboard,
            .bookmarks-page .bm-smart-grid,
            .bookmarks-page .bm-toolbar {
                grid-template-columns: 1fr;
            }

            .bookmarks-page .bm-stats {
                gap: 12px;
                margin-top: 28px;
            }

            .bookmarks-page .bm-stat-card {
                min-height: 120px;
                padding: 18px;
                border-radius: 18px;
            }

            .bookmarks-page .bm-stat-card strong {
                font-size: 2rem;
            }

            .bookmarks-page .bm-section {
                padding: 60px 0;
            }

            .bookmarks-page .bm-section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                margin-bottom: 26px;
            }

            .bookmarks-page .bm-section-header h2 {
                font-size: clamp(1.7rem, 8vw, 2.3rem);
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-section-header p {
                margin-top: 8px;
                line-height: 1.65;
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-categories {
                gap: 10px;
            }

            .bookmarks-page .bm-category {
                min-height: 106px;
                padding: 16px;
            }

            .bookmarks-page .bm-toolbar {
                gap: 10px;
                padding: 10px;
                border-radius: 18px;
            }

            .bookmarks-page .bm-control {
                min-width: 0;
                width: 100%;
            }

            .bookmarks-page .bm-control input,
            .bookmarks-page .bm-control select {
                min-width: 0;
                font-size: 16px;
            }

            .bookmarks-page .bm-dashboard {
                gap: 14px;
            }

            .bookmarks-page .bm-saved-card {
                min-height: 0;
                padding: 18px;
                border-radius: 20px;
            }

            .bookmarks-page .bm-card-head,
            .bookmarks-page .bm-card-footer {
                gap: 10px;
            }

            .bookmarks-page .bm-card-head {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .bookmarks-page .bm-pill,
            .bookmarks-page .bm-date,
            .bookmarks-page .bm-saved-card h3,
            .bookmarks-page .bm-preview {
                overflow-wrap: anywhere;
            }

            .bookmarks-page .bm-saved-card h3 {
                font-size: 1.2rem;
            }

            .bookmarks-page .bm-card-footer {
                align-items: stretch;
            }

            .bookmarks-page .bm-card-btn {
                flex: 1;
                min-width: 0;
                text-align: center;
            }

            .bookmarks-page .bm-empty {
                padding: 34px 18px;
                border-radius: 22px;
            }

            .bookmarks-page .bm-empty-illustration {
                width: 112px;
                height: 112px;
                border-radius: 28px;
            }

            .bookmarks-page .bm-recent-track {
                grid-auto-columns: minmax(240px, 82vw);
            }

            .bookmarks-page .bm-mini-card,
            .bookmarks-page .bm-smart-card {
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .bookmarks-page #guestView .bm-empty > div:last-child,
            .bookmarks-page #guestView .bm-empty [style*="display: flex"] {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .bookmarks-page #guestView .bm-empty [style*="display: flex"] > a {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .bookmarks-page .bm-container {
                width: calc(100% - 24px);
            }

            .bookmarks-page .bm-hero {
                padding: 38px 0 28px;
            }

            .bookmarks-page .bm-kicker {
                font-size: 0.7rem;
                letter-spacing: 0.08em;
                padding: 7px 11px;
            }

            .bookmarks-page .bm-title {
                font-size: clamp(2rem, 12vw, 2.55rem);
            }

            .bookmarks-page .bm-visual-top {
                gap: 10px;
            }

            .bookmarks-page .bm-visual-search {
                max-width: none;
            }

            .bookmarks-page .bm-floating-card {
                padding: 16px;
                border-radius: 18px;
            }

            .bookmarks-page .bm-stat-card {
                min-height: 108px;
                padding: 16px;
            }

            .bookmarks-page .bm-section {
                padding: 50px 0;
            }

            .bookmarks-page .bm-category {
                min-height: 96px;
                padding: 14px;
            }

            .bookmarks-page .bm-toolbar {
                padding: 8px;
            }

            .bookmarks-page .bm-control {
                padding: 0 12px;
            }

            .bookmarks-page .bm-saved-card {
                padding: 16px;
            }

            .bookmarks-page .bm-card-footer {
                flex-direction: column;
            }

            .bookmarks-page .bm-card-btn,
            .bookmarks-page .bm-icon-btn {
                width: 100%;
            }

            .bookmarks-page .bm-icon-btn {
                min-height: 42px;
            }

            .bookmarks-page .bm-empty {
                padding: 30px 14px;
            }

            .bookmarks-page .bm-empty-illustration {
                width: 96px;
                height: 96px;
            }

            .bookmarks-page .bm-empty-illustration svg {
                width: 56px;
                height: 56px;
            }

            .bookmarks-page .bm-recent-track {
                grid-auto-columns: 88vw;
            }

            .bookmarks-page .bm-smart-card {
                padding: 18px;
                border-radius: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bookmarks-page *,
            .bookmarks-page *::before,
            .bookmarks-page *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
@endpush

@section('content')
    <main class="bookmarks-page">
        <span class="bm-glow one"></span>
        <span class="bm-glow two"></span>
        <span class="bm-glow three"></span>

        <section class="bm-hero">
            <div class="bm-container">
                <div class="bm-hero-grid">
                    <div class="bm-reveal">
                        <span class="bm-kicker"><span class="ui-icon ui-icon-book" aria-hidden="true"></span> Intelligent bookmark workspace</span>
                        <h1 class="bm-title">Your Saved <span>Learning Space</span></h1>
                        <p class="bm-subtitle">Access your bookmarked topics, notes, questions, and resources anytime in one organized academic dashboard.</p>
                        <div class="bm-hero-actions">
                            <a href="#savedDashboard" class="bm-primary-btn"><span class="ui-icon ui-icon-zap" aria-hidden="true"></span> Open Dashboard</a>
                            <a href="{{ route('topics.index') }}" class="bm-secondary-btn"><span class="ui-icon ui-icon-book" aria-hidden="true"></span> Explore Topics</a>
                        </div>
                    </div>

                    <div class="bm-hero-visual bm-reveal" aria-label="Bookmark dashboard preview">
                        <div class="bm-dashboard-orbit"></div>
                        <div class="bm-visual-top">
                            <div class="bm-visual-dots"><span></span><span></span><span></span></div>
                            <div class="bm-visual-search"></div>
                        </div>
                        <div class="bm-floating-card card-a">
                            <span class="bm-save-mark"><span class="ui-icon ui-icon-book" aria-hidden="true"></span></span>
                            <strong>Brachial Plexus</strong>
                            <small>Anatomy topic saved</small>
                            <div class="bm-card-line"></div>
                            <div class="bm-card-line short"></div>
                        </div>
                        <div class="bm-floating-card card-b">
                            <span class="bm-save-mark"><span class="ui-icon ui-icon-help" aria-hidden="true"></span></span>
                            <strong>UMN vs LMN Lesions</strong>
                            <small>Important viva question</small>
                            <div class="bm-card-line"></div>
                            <div class="bm-card-line short"></div>
                        </div>
                        <div class="bm-floating-card card-c">
                            <span class="bm-save-mark"><span class="ui-icon ui-icon-file" aria-hidden="true"></span></span>
                            <strong>Gait Cycle Notes PDF</strong>
                            <small>Clinical resource</small>
                            <div class="bm-card-line"></div>
                            <div class="bm-card-line short"></div>
                        </div>
                    </div>
                </div>

                <div class="bm-stats bm-reveal">
                    <article class="bm-stat-card"><strong data-count="{{ auth()->check() ? auth()->user()->bookmarks()->count() : 0 }}">0</strong><span>Total Saved</span></article>
                    <article class="bm-stat-card"><strong data-count="{{ auth()->check() ? auth()->user()->bookmarks()->where('bookmarkable_type', 'App\Models\Topic')->count() : 0 }}">0</strong><span>Topics Saved</span></article>
                    <article class="bm-stat-card"><strong data-count="{{ auth()->check() ? auth()->user()->bookmarks()->where('bookmarkable_type', 'App\Models\LearningMaterial')->count() : 0 }}">0</strong><span>Materials Saved</span></article>
                    <article class="bm-stat-card"><strong data-count="{{ auth()->check() ? auth()->user()->bookmarks()->whereDate('created_at', today())->count() : 0 }}">0</strong><span>Saved Today</span></article>
                </div>
            </div>
        </section>

        @auth
            <section class="bm-section" id="categories">
                <div class="bm-container">
                    <div class="bm-section-header bm-reveal">
                        <div>
                            <h2>Organize Your Saved Content</h2>
                            <p>Switch between academic collections and keep every revision item exactly where your study flow expects it.</p>
                        </div>
                    </div>

                    <div class="bm-categories bm-reveal" id="categoryFilters">
                        <button class="bm-category active" data-category="All"><span class="ui-icon ui-icon-folder" aria-hidden="true"></span><strong>All</strong><small>Everything</small></button>
                        <button class="bm-category" data-category="Topic"><span class="ui-icon ui-icon-book" aria-hidden="true"></span><strong>Topics</strong><small>Core syllabus</small></button>
                        <button class="bm-category" data-category="LearningMaterial"><span class="ui-icon ui-icon-help" aria-hidden="true"></span><strong>Materials</strong><small>Study resources</small></button>
                    </div>
                </div>
            </section>

            <section class="bm-section" id="savedDashboard">
                <div class="bm-container">
                    <div class="bm-section-header bm-reveal">
                        <div>
                            <h2>Saved Content Dashboard</h2>
                            <p>Search, filter, sort, open, and remove saved academic content from a single focused workspace.</p>
                        </div>
                    </div>

                    <div class="bm-toolbar bm-reveal">
                        <label class="bm-control">
                            <span class="ui-icon ui-icon-search" aria-hidden="true"></span>
                            <input type="search" id="bookmarkSearch" placeholder="Search bookmarked items">
                        </label>
                        <label class="bm-control">
                            <span class="ui-icon ui-icon-brain" aria-hidden="true"></span>
                            <select id="subjectFilter" aria-label="Filter by subject">
                                <option value="All">All subjects</option>
                                @foreach($bookmarks->map(function($b) {
                                    $item = $b->bookmarkable;
                                    return $item instanceof \App\Models\Topic ? ($item->subject->name ?? null) : ($item->topic->subject->name ?? null);
                                })->unique()->filter() as $sub)
                                    <option value="{{ $sub }}">{{ $sub }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="bm-control">
                            <span class="ui-icon ui-icon-clock" aria-hidden="true"></span>
                            <select id="sortFilter" aria-label="Sort bookmarks">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </select>
                        </label>
                    </div>

                    <div class="bm-dashboard bm-reveal" id="bookmarkGrid">
                        @foreach($bookmarks as $bookmark)
                            @php
                                $item = $bookmark->bookmarkable;
                                $type = class_basename($bookmark->bookmarkable_type);
                                $subject = null;
                                if ($item instanceof \App\Models\Topic) {
                                    $subject = $item->subject->name ?? 'General';
                                } elseif ($item instanceof \App\Models\LearningMaterial) {
                                    $subject = $item->topic->subject->name ?? 'General';
                                }
                                $preview = '';
                                if (isset($item->description)) $preview = strip_tags($item->description);
                                elseif (isset($item->content)) $preview = strip_tags($item->content);
                            @endphp
                            <article class="bm-saved-card" data-id="{{ $bookmark->id }}" data-category="{{ $type }}" data-subject="{{ $subject }}" data-title="{{ strtolower($item->title) }}" data-time="{{ $bookmark->created_at->timestamp }}">
                                <div class="bm-card-head">
                                    <span class="bm-pill">{{ $type }}</span>
                                    <span class="bm-date">Saved {{ $bookmark->created_at->diffForHumans() }}</span>
                                </div>
                                <h3>{{ $item->title }}</h3>
                                <p class="bm-preview">{{ Str::limit($preview, 100) }}</p>
                                <div style="margin: 18px 0; color: #64748b; font-size: 0.82rem; font-weight: 800;">
                                    <span>{{ $subject }}</span>
                                </div>
                                <div class="bm-card-footer">
                                    <a class="bm-card-btn" href="{{ $type === 'Topic' ? route('topics.show', $item->slug) : '#' }}">Open / View</a>
                                    <button class="bm-icon-btn saved" onclick="removeBookmark({{ $bookmark->id }}, this)" aria-label="Remove bookmark">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="bm-empty {{ $bookmarks->isEmpty() ? 'visible' : '' }}" id="emptyState">
                        <div class="bm-empty-illustration">
                            <svg width="68" height="68" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/><path d="M9 8h6M9 12h4"/></svg>
                        </div>
                        <h3>No bookmarks saved yet.</h3>
                        <p>Save topics, notes, and resources to create your personalized learning dashboard.</p>
                        <a href="{{ route('topics.index') }}" class="bm-primary-btn">Explore Topics</a>
                    </div>
                </div>
            </section>

            @if($bookmarks->isNotEmpty())
                <section class="bm-section">
                    <div class="bm-container">
                        <div class="bm-section-header bm-reveal">
                            <div>
                                <h2>Recently Saved</h2>
                                <p>Quick-access mini cards keep your newest study material within reach.</p>
                            </div>
                        </div>
                        <div class="bm-recent-track bm-reveal" id="recentTrack">
                            @foreach($bookmarks->take(6) as $bookmark)
                                @php
                                    $item = $bookmark->bookmarkable;
                                    $type = class_basename($bookmark->bookmarkable_type);
                                    $subject = null;
                                    if ($item instanceof \App\Models\Topic) {
                                        $subject = $item->subject->name ?? 'General';
                                    } elseif ($item instanceof \App\Models\LearningMaterial) {
                                        $subject = $item->topic->subject->name ?? 'General';
                                    }
                                @endphp
                                <a class="bm-mini-card" href="{{ $type === 'Topic' ? route('topics.show', $item->slug) : '#' }}">
                                    <small>{{ $type }} / {{ $subject }}</small>
                                    <h3>{{ $item->title }}</h3>
                                    <div class="bm-timeline">Saved {{ $bookmark->created_at->diffForHumans() }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @else
            <section class="bm-section" id="guestView">
                <div class="bm-container">
                    <div class="bm-empty visible" style="border: none; background: transparent; box-shadow: none;">
                        <div class="bm-empty-illustration">
                            <svg width="68" height="68" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3>Sign in to sync your space</h3>
                        <p>Join thousands of students who save their study progress across devices.</p>
                        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 24px;">
                            <a href="{{ route('login') }}" class="bm-primary-btn">Sign In to Continue</a>
                            <a href="{{ route('register') }}" class="bm-secondary-btn">Create Account</a>
                        </div>
                    </div>
                </div>
            </section>
        @endauth

        <section class="bm-section" id="smartOrganization">
            <div class="bm-container">
                <div class="bm-section-header bm-reveal">
                    <div>
                        <h2>Study Smarter with Organized Learning</h2>
                        <p>A calmer academic workflow for revision, clinical recall, exam preparation, and resource management.</p>
                    </div>
                </div>

                <div class="bm-smart-grid">
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-zap" aria-hidden="true"></span></span><h3>Quick access to saved resources</h3><p>Return to high-value references without repeating topic searches.</p></article>
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-folder" aria-hidden="true"></span></span><h3>Personalized study collection</h3><p>Build a dashboard around your own academic priorities.</p></article>
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-settings" aria-hidden="true"></span></span><h3>Organized academic workflow</h3><p>Separate topics, PDFs, notes, viva questions, and revision material.</p></article>
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-rocket" aria-hidden="true"></span></span><h3>Faster exam preparation</h3><p>Filter by subject and category to move from planning to revision quickly.</p></article>
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-refresh" aria-hidden="true"></span></span><h3>Bookmark important revisions</h3><p>Keep recall-heavy concepts ready for rapid review sessions.</p></article>
                    <article class="bm-smart-card bm-reveal"><span class="bm-smart-icon"><span class="ui-icon ui-icon-hospital" aria-hidden="true"></span></span><h3>Save clinical learning resources</h3><p>Collect clinical notes, gait resources, and practical references in one place.</p></article>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        (function initBookmarks() {
            const searchInput = document.getElementById('bookmarkSearch');
            const subjectFilter = document.getElementById('subjectFilter');
            const sortFilter = document.getElementById('sortFilter');
            const categoryButtons = document.querySelectorAll('.bm-category');
            const grid = document.getElementById('bookmarkGrid');
            const emptyState = document.getElementById('emptyState');
            const cards = document.querySelectorAll('.bm-saved-card');

            let activeCategory = 'All';

            function filter() {
                const query = searchInput.value.toLowerCase();
                const subject = subjectFilter.value;
                let visibleCount = 0;

                const cardsArray = Array.from(cards);

                cardsArray.forEach(card => {
                    const title = card.dataset.title;
                    const cat = card.dataset.category;
                    const sub = card.dataset.subject;

                    const matchesSearch = title.includes(query);
                    const matchesCategory = activeCategory === 'All' || cat === activeCategory;
                    const matchesSubject = subject === 'All' || sub === subject;

                    if (matchesSearch && matchesCategory && matchesSubject) {
                        card.style.display = 'flex';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Sorting
                const sortMode = sortFilter.value;
                const sortedCards = cardsArray.sort((a, b) => {
                    const timeA = parseInt(a.dataset.time);
                    const timeB = parseInt(b.dataset.time);
                    return sortMode === 'latest' ? timeB - timeA : timeA - timeB;
                });

                sortedCards.forEach(card => grid.appendChild(card));

                if (emptyState) {
                    emptyState.classList.toggle('visible', visibleCount === 0);
                }
            }

            if (searchInput) searchInput.addEventListener('input', filter);
            if (subjectFilter) subjectFilter.addEventListener('change', filter);
            if (sortFilter) sortFilter.addEventListener('change', filter);

            categoryButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    categoryButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    activeCategory = btn.dataset.category;
                    filter();
                });
            });

            // Stats Counters
            document.querySelectorAll('.bm-stat-card strong').forEach(el => {
                const target = parseInt(el.dataset.count);
                let current = 0;
                const inc = Math.max(1, Math.floor(target / 20));
                const timer = setInterval(() => {
                    current += inc;
                    if (current >= target) {
                        el.textContent = target;
                        clearInterval(timer);
                    } else {
                        el.textContent = current;
                    }
                }, 30);
            });

            // Reveal animations
            const revealObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            document.querySelectorAll('.bm-reveal').forEach((el, index) => {
                el.style.transitionDelay = `${index * 50}ms`;
                revealObserver.observe(el);
            });
        })();

        function removeBookmark(id, btn) {
            if (!confirm('Are you sure you want to remove this bookmark?')) return;

            btn.classList.add('removing');
            const card = btn.closest('.bm-saved-card');

            fetch(`/bookmarks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(16px) scale(0.98)';
                        setTimeout(() => {
                            card.remove();
                            location.reload(); // Refresh to update stats and empty state properly
                        }, 300);
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.classList.remove('removing');
                });
        }
    </script>
@endpush
