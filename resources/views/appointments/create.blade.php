<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đặt Lịch Khám – HospitalBooking | Trải nghiệm đặt khám hiện đại</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Be Vietnam Pro', sans-serif;
            background: linear-gradient(145deg, #f4f9ff 0%, #eef3fc 100%);
            color: #0b2b42;
            line-height: 1.5;
        }

        :root {
            --primary: #0f52ba;
            --primary-dark: #0a3d8f;
            --primary-soft: #eef4ff;
            --primary-glow: rgba(15, 82, 186, 0.08);
            --accent-teal: #1e8f9b;
            --accent-teal-light: #cff4f0;
            --gray-50: #f9fafc;
            --gray-100: #f2f5f9;
            --gray-200: #e9edf2;
            --gray-300: #dce2e8;
            --gray-400: #9aaebf;
            --gray-500: #6b7280;
            --gray-600: #4a5c6c;
            --gray-700: #374151;
            --gray-800: #1e2a3a;
            --white: #ffffff;
            --shadow-sm: 0 8px 20px rgba(0, 0, 0, .02), 0 2px 6px rgba(0, 20, 50, .05);
            --shadow-md: 0 12px 28px rgba(0, 0, 0, .04), 0 0 0 1px rgba(0, 0, 0, .01);
            --shadow-lg: 0 20px 35px -12px rgba(0, 32, 64, .12);
            --radius-card: 24px;
            --radius-panel: 20px;
            --radius-btn: 40px;
            --radius-input: 16px;
        }

        h1,
        h2,
        h3 {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        /* ── TOPBAR ── */
        .topbar {
            background: var(--white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .02), 0 1px 0 rgba(0, 0, 0, .03);
            padding: 0 32px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 110;
            border-bottom: 1px solid var(--gray-200);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #2b6ed7);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 12px -6px rgba(15, 82, 186, .3);
        }

        .brand-text {
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--gray-800);
            letter-spacing: -0.3px;
        }

        .brand-sub {
            font-size: .7rem;
            font-weight: 500;
            color: var(--gray-400);
            margin-top: 2px;
        }

        .topbar-center {
            display: flex;
            gap: 6px;
            background: var(--gray-100);
            padding: 4px;
            border-radius: 48px;
        }

        .topbar-center a {
            padding: 8px 20px;
            font-size: .85rem;
            font-weight: 600;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 40px;
            transition: all .2s;
        }

        .topbar-center a:hover,
        .topbar-center a.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--gray-100);
            padding: 5px 12px 5px 8px;
            border-radius: 48px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(145deg, var(--primary), #3279dc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: .85rem;
        }

        .user-name {
            font-weight: 600;
            font-size: .85rem;
            color: var(--gray-800);
        }

        .btn-logout {
            background: transparent;
            border: 1px solid var(--gray-300);
            border-radius: 32px;
            padding: 7px 18px;
            font-weight: 600;
            font-size: .75rem;
            color: var(--gray-600);
            transition: all .2s;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            color: var(--primary-dark);
        }

        /* ── BREADCRUMB ── */
        .breadcrumb-bar {
            padding: 14px 32px;
            font-size: .75rem;
            color: var(--gray-400);
            background: transparent;
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
        }

        .breadcrumb-bar a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        /* ── LAYOUT ── */
        .page {
            max-width: 1320px;
            margin: 24px auto 48px;
            padding: 0 28px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 32px;
        }

        /* ── PANEL ── */
        .panel {
            background: var(--white);
            border-radius: var(--radius-panel);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .panel-head {
            padding: 18px 28px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .icon-wrap {
            width: 44px;
            height: 44px;
            background: var(--primary-soft);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .panel-head h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .panel-body {
            padding: 24px 28px;
        }

        /* ── FORM ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gray-500);
            letter-spacing: .03em;
            margin-bottom: 8px;
            display: block;
        }

        .req {
            color: #e5484d;
        }

        .form-control,
        select.form-control,
        textarea.form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-input);
            font-family: 'Inter', sans-serif;
            font-size: .85rem;
            transition: all .2s;
            outline: none;
            color: var(--gray-800);
        }

        .form-control:focus,
        select:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(15, 82, 186, .08);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* ══════════════════════════════════════════
           FEATURE 1 — GỢI Ý BÁC SĨ
        ══════════════════════════════════════════ */
        .suggest-section {
            display: none;
            background: linear-gradient(135deg, #fffbeb 0%, #fff7e0 100%);
            border: 1px solid #fde68a;
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .suggest-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .suggest-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 100px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .suggest-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .suggest-sub {
            font-size: .72rem;
            color: var(--gray-500);
            margin-left: auto;
        }

        .suggest-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        @media (max-width: 640px) {
            .suggest-grid {
                grid-template-columns: 1fr;
            }
        }

        #hold-countdown-banner.hold-urgent {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #991b1b;
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .75;
            }
        }

        .hold-timer {
            font-variant-numeric: tabular-nums;
        }

        .suggest-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            padding: 16px 12px;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            position: relative;
        }

        .suggest-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 82, 186, .12);
        }

        .suggest-card.selected-sug {
            border-color: var(--primary);
            background: var(--primary-soft);
            box-shadow: 0 8px 22px rgba(15, 82, 186, .18);
        }

        .sug-rank {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: .62rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sug-rank.r1 {
            background: #f59e0b;
        }

        .sug-rank.r2 {
            background: #9ca3af;
        }

        .sug-rank.r3 {
            background: #cd7c5b;
        }

        .sug-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #2b6ed7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            margin: 0 auto 10px;
            overflow: hidden;
            border: 2px solid var(--primary-soft);
        }

        .sug-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sug-name {
            font-size: .82rem;
            font-weight: 800;
            color: var(--gray-800);
        }

        .sug-stars {
            color: #f59e0b;
            font-size: .72rem;
            margin: 4px 0;
        }

        .sug-tags {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .sug-tag {
            font-size: .62rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 100px;
        }

        .sug-tag.slot {
            background: #d1fae5;
            color: #065f46;
        }

        .sug-tag.exp {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .sug-tag.price {
            background: #ede9fe;
            color: #6d28d9;
        }

        .sug-cta {
            display: none;
            font-size: .68rem;
            font-weight: 700;
            color: var(--primary);
            margin-top: 8px;
        }

        .suggest-card.selected-sug .sug-cta {
            display: block;
        }

        .suggest-loading {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .8rem;
            color: var(--gray-500);
            padding: 8px 0;
        }

        /* ══════════════════════════════════════════
           FEATURE 2 — DYNAMIC TIME SLOTS
        ══════════════════════════════════════════ */
        .slot-stats-bar {
            display: none;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            font-size: .72rem;
            color: var(--gray-500);
        }

        .slot-stat {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .slot-stat-dot {
            width: 8px;
            height: 8px;
            border-radius: 3px;
        }

        .slot-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 12px 0 16px;
        }

        .slot-session-label {
            width: 100%;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--gray-400);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 2px;
        }

        .slot-session-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }

        .slot-btn {
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 60px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: .82rem;
            cursor: pointer;
            transition: all .2s cubic-bezier(.2, .9, .4, 1.1);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            min-width: 84px;
            color: var(--gray-700);
            font-family: 'Inter', sans-serif;
        }

        .slot-end {
            font-size: .6rem;
            font-weight: 400;
            color: var(--gray-400);
        }

        .slot-btn:hover:not(:disabled):not(.slot-full) {
            background: var(--primary-soft);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(15, 82, 186, .15);
            color: var(--primary);
        }

        .slot-btn:hover:not(:disabled):not(.slot-full) .slot-end {
            color: var(--primary);
            opacity: .7;
        }

        .slot-btn.selected {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 82, 186, .3);
        }

        .slot-btn.selected .slot-end {
            color: rgba(255, 255, 255, .7);
        }

        .slot-btn.slot-full {
            background: #fef2f2;
            border-color: #ffc9c9;
            color: #e5484d;
            cursor: not-allowed;
            opacity: .7;
        }

        .slot-btn.slot-full .slot-end {
            color: #fca5a5;
        }

        .slot-legend {
            display: flex;
            gap: 20px;
            margin: 4px 0;
            flex-wrap: wrap;
        }

        .legend-item {
            font-size: .7rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 20px;
            background: var(--gray-200);
            border: 1px solid var(--gray-300);
        }

        .legend-dot.full-dot {
            background: #fef2f2;
            border-color: #ffb4b4;
        }

        .legend-dot.sel-dot {
            background: var(--primary);
            border: none;
        }

        .slot-placeholder {
            font-size: .82rem;
            color: var(--gray-400);
            font-style: italic;
            padding: 4px 0;
        }

        .mini-spin {
            width: 16px;
            height: 16px;
            border: 2px solid var(--gray-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .6s linear infinite;
            flex-shrink: 0;
        }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 32px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-200);
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f5db8;
            margin-bottom: 8px;
        }

        .info-val {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .info-val.amber {
            color: #c2410c;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .doctor-card,
        .summary-card {
            background: var(--white);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(0, 0, 0, .03);
            overflow: hidden;
        }

        .card-head {
            padding: 16px 24px;
            background: var(--gray-50);
            font-weight: 700;
            font-size: .7rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--primary);
            border-bottom: 1px solid var(--gray-200);
        }

        .doctor-body {
            padding: 24px;
            text-align: center;
        }

        .doc-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(145deg, var(--primary), #448af2);
            border-radius: 50%;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.6rem;
            color: white;
            overflow: hidden;
            box-shadow: 0 12px 18px -8px rgba(15, 82, 186, .3);
        }

        .doc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .doc-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--gray-800);
        }

        .doc-bio {
            font-size: .74rem;
            color: var(--gray-500);
            margin-top: 6px;
            line-height: 1.5;
        }

        .doc-stars {
            color: #f5b042;
            margin: 8px 0;
            font-size: .8rem;
        }

        .doc-meta-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: .8rem;
            border-bottom: 1px dashed var(--gray-100);
        }

        .sum-body {
            padding: 18px 22px;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: .82rem;
        }

        .sum-row:last-child {
            border-bottom: none;
        }

        .sum-row .k {
            color: var(--gray-500);
        }

        .sum-row .v {
            font-weight: 600;
            color: var(--gray-800);
        }

        .sum-row .v.empty {
            color: var(--gray-400);
            font-weight: 400;
        }

        .status-pill {
            background: #fff6e0;
            border-radius: 100px;
            padding: 4px 12px;
            font-size: .7rem;
            font-weight: 700;
            color: #c47b2e;
        }

        /* ══════════════════════════════════════════
           FEATURE 3 — QUEUE WAITING TIME
        ══════════════════════════════════════════ */
        .queue-card {
            background: var(--white);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(0, 0, 0, .03);
            overflow: hidden;
        }

        .queue-card.hidden {
            display: none;
        }

        .queue-header {
            padding: 16px 24px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            font-weight: 700;
            font-size: .7rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #065f46;
            border-bottom: 1px solid #6ee7b7;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .queue-body {
            padding: 18px 22px;
        }

        .queue-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: .82rem;
        }

        .queue-row:last-child {
            border-bottom: none;
        }

        .queue-row .qlabel {
            color: var(--gray-500);
            font-weight: 500;
        }

        .queue-row .qvalue {
            font-weight: 700;
            color: var(--gray-800);
        }

        .queue-number-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f52ba, #2b6ed7);
            color: #fff;
            font-weight: 800;
            font-size: 1.2rem;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(15, 82, 186, .25);
        }

        .wait-time-info {
            background: #eff6ff;
            border-left: 3px solid #0f52ba;
            border-radius: 12px;
            padding: 12px 16px;
            margin: 12px 0;
            font-size: .8rem;
        }

        .wait-time-value {
            font-weight: 800;
            color: #0f52ba;
            font-size: 1.1rem;
            display: block;
            margin: 4px 0;
        }

        .wait-time-label {
            color: var(--gray-500);
            font-size: .7rem;
            display: block;
        }

        .queue-people-ahead {
            background: #f0fdf4;
            border-left: 3px solid #059669;
            border-radius: 12px;
            padding: 12px 16px;
            margin: 12px 0;
            font-size: .8rem;
        }

        .queue-people-count {
            font-weight: 800;
            color: #059669;
            display: block;
            margin: 4px 0;
        }

        .queue-list {
            background: var(--gray-50);
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 12px;
            font-size: .75rem;
        }

        .queue-list-title {
            font-weight: 700;
            color: var(--gray-600);
            margin-bottom: 8px;
            display: block;
        }

        .queue-person {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            color: var(--gray-600);
        }

        .queue-person-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
            font-size: .7rem;
            flex-shrink: 0;
        }

        .queue-person-name {
            color: var(--gray-700);
            font-weight: 500;
        }

        .queue-loading {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 0;
            font-size: .8rem;
            color: var(--gray-500);
        }

        /* ── BUTTONS ── */
        .btn-row {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 32px;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 48px;
            font-weight: 700;
            font-size: .85rem;
            transition: all .2s;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(105deg, var(--primary), #2065cf);
            color: white;
            box-shadow: 0 4px 12px rgba(15, 82, 186, .3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -6px rgba(15, 82, 186, .4);
        }

        .btn-primary:disabled {
            opacity: .55;
            transform: none;
            cursor: not-allowed;
            box-shadow: none;
        }

        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        /* ── FOOTER ── */
        .footer {
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            text-align: center;
            padding: 28px;
            font-size: .75rem;
            color: var(--gray-500);
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 860px) {
            .page {
                grid-template-columns: 1fr;
            }

            .topbar-center {
                display: none;
            }

            .suggest-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 520px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .suggest-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    {{-- ── TOPBAR ── --}}
    <nav class="topbar">
        <a href="{{ route('home') }}" class="topbar-brand">
            <div class="logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <div>
                <div class="brand-text">HospitalBooking</div>
                <div class="brand-sub">Đặt lịch thông minh</div>
            </div>
        </a>

        <div class="topbar-center">
            <a href="{{ route('home') }}">🏠 Trang chủ</a>
            <a href="{{ route('appointments.index') }}">📋 Lịch hẹn</a>
            <a href="{{ route('appointments.create') }}" class="active">✨ Đặt lịch mới</a>
            <a href="{{ route('news.index') }}">📰 Bản tin</a>
            @auth
                @if(auth()->user()->isPatient())
                    <a href="{{ route('medical_history.index') }}">📄 Hồ sơ bệnh án</a>
                @elseif(auth()->user()->isDoctor())
                    <a href="{{ route('doctor.appointments.index') }}">🩺 Danh sách khám</a>
                @endif
            @endauth
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            @auth
                <div class="user-pill">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                    <span class="user-name">{{ auth()->user()->name ?? 'Người dùng' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="btn-logout">Đăng xuất</button>
                </form>
            @endauth
        </div>
    </nav>

    {{-- ── BREADCRUMB ── --}}
    <div style="max-width:1320px;margin:0 auto;padding:14px 28px;font-size:.75rem;color:var(--gray-400)">
        <a href="{{ route('home') }}" style="color:var(--primary);text-decoration:none;font-weight:500">Trang chủ</a>
        <span style="margin:0 6px">/</span>
        <a href="{{ route('appointments.index') }}"
            style="color:var(--primary);text-decoration:none;font-weight:500">Lịch hẹn</a>
        <span style="margin:0 6px">/</span>
        <span style="color:var(--gray-600);font-weight:500">Đặt lịch khám</span>
    </div>

    {{-- ── PAGE ── --}}
    <div class="page">

        {{-- ── MAIN FORM ── --}}
        <div class="main-col">

            {{-- Alerts --}}
            @if(session('success'))
                <div
                    style="background:#e8f3ef;border-left:4px solid #059669;padding:14px 20px;border-radius:18px;margin-bottom:24px;font-size:.85rem;color:#065f46">
                    ✔️ {{ session('success') }}
                </div>
            @endif
            @if($errors->has('msg'))
                <div
                    style="background:#fee9e9;border-left:4px solid #e5484d;padding:14px 20px;border-radius:18px;margin-bottom:24px;font-size:.85rem;color:#991b1b">
                    ⚠️ {{ $errors->first('msg') }}
                </div>
            @endif

            <form id="booking-form" action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="schedule_id" id="schedule_id">
                <input type="hidden" name="appointment_time" id="appointment_time">

                {{-- ══ THÔNG TIN NGƯỜI ĐẶT ══ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <h2>Thông Tin Người Đặt Lịch</h2>
                    </div>
                    <div class="panel-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Họ Tên</div>
                                <div class="info-val">{{ $user->full_name ?? '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Số Điện Thoại</div>
                                <div class="info-val">{{ $user->phone ?? '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Địa Chỉ</div>
                                <div class="info-val">{{ $user->address ?? '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-val">{{ $user->email ?? '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Ngày Sinh</div>
                                <div class="info-val">
                                    {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ BƯỚC 1: Khoa & bác sĩ ══ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <h2>Bước 1 — Chuyên khoa &amp; Bác sĩ</h2>
                    </div>
                    <div class="panel-body">

                        {{-- ── FEATURE 1: Gợi ý bác sĩ ── --}}
                        <div id="suggest-section" class="suggest-section">
                            <div class="suggest-header">
                                <span class="suggest-badge">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                    AI gợi ý
                                </span>
                                <span class="suggest-title">Bác sĩ phù hợp nhất hôm nay</span>
                                <span class="suggest-sub" id="suggest-sub"></span>
                            </div>
                            <div class="suggest-grid" id="suggest-grid"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Chuyên Khoa <span class="req">*</span></label>
                                <select class="form-control" id="dept" onchange="onDeptChange()">
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bác Sĩ <span class="req">*</span></label>
                                <select class="form-control" id="doctor" onchange="onDoctorChange()" disabled>
                                    <option value="">-- Chọn bác sĩ --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Dịch Vụ</label>
                                <select name="service_id" class="form-control" id="service_id_select">
                                    <option value="">-- Không chọn --</option>
                                    @foreach($services as $svc)
                                        <option value="{{ $svc->service_id }}" {{ old('service_id') == $svc->service_id ? 'selected' : '' }}>
                                            {{ $svc->service_name }}
                                            @if($svc->price) – {{ number_format($svc->price, 0, ',', '.') }}₫ @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hình Thức Khám</label>
                                <select name="visit_type" class="form-control">
                                    <option value="Khám trực tiếp" selected>Khám trực tiếp</option>
                                    <option value="Khám online">Khám online</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══ BƯỚC 2: Ngày & khung giờ ══ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </div>
                        <h2>Bước 2 — Chọn ngày &amp; khung giờ</h2>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="form-label">Ngày Khám <span class="req">*</span></label>
                            <input type="date" id="work_date" name="work_date"
                                class="form-control @error('work_date') is-invalid @enderror" min="{{ date('Y-m-d') }}"
                                value="{{ old('work_date', date('Y-m-d')) }}" oninput="onDateChange()"
                                style="max-width:260px">
                            @error('work_date')
                                <span
                                    style="font-size:.7rem;color:#e5484d;display:block;margin-top:4px">{{ $message }}</span>
                            @enderror
                        </div>

                        <div
                            style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--gray-600);letter-spacing:.03em;margin:16px 0 4px">
                            Khung Giờ <span class="req">*</span>
                        </div>

                        {{-- FEATURE 2: slot stats --}}
                        <div id="slot-stats-bar" class="slot-stats-bar">
                            <span class="slot-stat">
                                <span class="slot-stat-dot" style="background:var(--gray-200)"></span>
                                <span id="stat-total">0</span> khung giờ
                            </span>
                            <span class="slot-stat" style="color:#059669">
                                <span class="slot-stat-dot" style="background:#d1fae5"></span>
                                <span id="stat-avail">0</span> trống
                            </span>
                            <span class="slot-stat" style="color:#e5484d">
                                <span class="slot-stat-dot" style="background:#fef2f2;border:1px solid #ffc9c9"></span>
                                <span id="stat-full">0</span> đầy
                            </span>
                        </div>

                        <div class="slot-wrap" id="slot-wrap">
                            <span class="slot-placeholder">Vui lòng chọn bác sĩ và ngày để xem khung giờ</span>
                        </div>

                        <div class="slot-legend" id="slot-legend" style="display:none">
                            <div class="legend-item">
                                <div class="legend-dot"></div>Còn trống
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot full-dot"></div>Đã đầy
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot sel-dot"></div>Đang chọn
                            </div>
                        </div>
                        <div id="hold-countdown-banner" style="display:none;align-items:center;gap:12px;
                            background:#fffbeb;border:1px solid #fcd34d;
                            border-radius:14px;padding:12px 18px;margin-top:14px;
                            font-size:.82rem;color:#92400e;transition:background .3s">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span id="hold-banner-msg">Khung giờ đang được giữ cho bạn.</span>
                            <strong>Thời gian còn lại:&nbsp;<span class="hold-timer">05:00</span></strong>
                        </div>

                        <div id="slot-error" style="font-size:.72rem;color:#e5484d;margin-top:10px;display:none">
                            ⚠️ Vui lòng chọn khung giờ trước khi đặt lịch.
                        </div>

                        <div class="form-group" style="margin-top:22px">
                            <label class="form-label">
                                <input type="checkbox" name="is_priority" id="is_priority" value="1" {{ old('is_priority') ? 'checked' : '' }} onchange="togglePriorityType()">
                                Đăng ký đối tượng ưu tiên
                            </label>

                            <div id="priority_type_container"
                                style="display: {{ old('is_priority') ? 'block' : 'none' }}; margin-top: 10px;">
                                <label class="form-label">Loại ưu tiên</label>
                                <select name="priority_type" class="form-control">
                                    <option value="">-- Chọn đối tượng --</option>
                                    <option value="Trẻ em dưới 6 tuổi" {{ old('priority_type') == 'Trẻ em dưới 6 tuổi' ? 'selected' : '' }}>Trẻ em dưới 6 tuổi</option>
                                    <option value="Người già trên 80 tuổi" {{ old('priority_type') == 'Người già trên 80 tuổi' ? 'selected' : '' }}>Người già trên 80 tuổi</option>
                                    <option value="Phụ nữ có thai" {{ old('priority_type') == 'Phụ nữ có thai' ? 'selected' : '' }}>Phụ nữ có thai</option>
                                    <option value="Người khuyết tật" {{ old('priority_type') == 'Người khuyết tật' ? 'selected' : '' }}>Người khuyết tật nặng</option>
                                    <option value="Cấp cứu" {{ old('priority_type') == 'Cấp cứu' ? 'selected' : '' }}>Tình
                                        trạng cấp cứu</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:12px">
                            <label class="form-label">Ghi Chú / Triệu Chứng</label>
                            <textarea name="note" class="form-control"
                                placeholder="VD: đau ngực, khó thở, tái khám sau điều trị...">{{ old('note') }}</textarea>
                        </div>

                        <div class="btn-row">
                            <a href="{{ route('home') }}" class="btn btn-secondary">← Quay lại</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="spinner" id="spinner" style="display:none"></span>
                                <svg id="submit-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 13l4 4L19 7" />
                                </svg>
                                Xác Nhận Đặt Lịch
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>{{-- /main-col --}}

        {{-- ── SIDEBAR ── --}}
        <div class="sidebar">
            <div class="doctor-card">
                <div class="card-head">👨‍⚕️ Bác Sĩ Được Chọn</div>
                <div class="doctor-body" id="doctor-body">
                    <div style="padding:20px 0;color:var(--gray-400);text-align:center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <p style="margin-top:10px;font-size:.82rem">Chưa chọn bác sĩ</p>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-head">📋 Tóm Tắt Đặt Lịch</div>
                <div class="sum-body">
                    <div class="sum-row"><span class="k">Khoa</span> <span class="v empty" id="sum-dept">—</span></div>
                    <div class="sum-row"><span class="k">Bác sĩ</span> <span class="v empty" id="sum-doctor">—</span>
                    </div>
                    <div class="sum-row"><span class="k">Dịch vụ</span> <span class="v empty" id="sum-svc">—</span>
                    </div>
                    <div class="sum-row"><span class="k">Ngày</span> <span class="v empty" id="sum-date"
                            style="color:var(--primary);font-weight:700">—</span></div>
                    <div class="sum-row"><span class="k">Giờ</span> <span class="v empty" id="sum-time"
                            style="color:var(--primary);font-weight:700">—</span></div>
                    <div class="sum-row"><span class="k">Trạng thái</span> <span class="status-pill">⏳ Chờ xác
                            nhận</span></div>
                </div>
            </div>

            <div class="queue-card hidden" id="queue-card">
                <div class="queue-header">
                    📊 Thông Tin Hàng Đợi
                </div>
                <div class="queue-body">
                    <div class="queue-row">
                        <span class="qlabel">Số thứ tự</span>
                        <span class="queue-number-badge" id="queue-number-display">—</span>
                    </div>

                    <div class="wait-time-info">
                        <span class="wait-time-label">⏱️ Thời gian chờ dự kiến</span>
                        <span class="wait-time-value" id="estimated-wait-time">— phút</span>
                    </div>

                    <div class="queue-people-ahead">
                        <span class="queue-people-count" id="people-ahead-count">— người</span>
                        <span class="queue-list-title">đứng trước bạn</span>
                    </div>

                    <div class="queue-list" id="queue-list-container" style="display:none">
                        <span class="queue-list-title">Danh sách khách hàng trước bạn:</span>
                        <div id="queue-people-list"></div>
                    </div>

                    <div class="queue-loading" id="queue-loading-spinner">
                        <div class="mini-spin"></div>
                        <span>Đang tải thông tin...</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /page --}}

    <footer class="footer">
        © {{ date('Y') }} HospitalBooking &nbsp;·&nbsp;
        <a href="#">Chính sách bảo mật</a> &nbsp;·&nbsp;
        <a href="#">Hỗ trợ</a>
    </footer>

    <script>
        // ══════════════════════════════════════════════════════════════
        // BASE DATA — truyền từ PHP (preload 14 ngày đầu cho các route
        // bác sĩ → không cần AJAX lần đầu)
        // ══════════════════════════════════════════════════════════════
        const doctorsByDept = JSON.parse('{!! json_encode($doctorsByDept, JSON_UNESCAPED_SLASHES) !!}');

        // Route URLs
        const ROUTE_SUGGEST = '{{ route("appointments.suggest") }}';
        const ROUTE_TIMESLOTS = '{{ route("appointments.timeslots") }}';
        const ROUTE_QUEUE_INFO = '{{ route("appointments.queue-info") }}';

        // ══════════════════════════════════════════════════════════════
        // STATE
        // ══════════════════════════════════════════════════════════════
        const state = {
            deptId: null,
            deptName: '',
            doctor: null,
            date: document.getElementById('work_date').value,
            scheduleId: null,
            time: null,
            timeEnd: null,
            holdAppointmentId: null,
            holdExpiresAt: null,
        };

        // AJAX caches — tránh gọi lại khi đã có data
        const suggestCache = {};   // key: `deptId_date`
        const timeslotCache = {};   // key: `doctorId_date`

        // ══════════════════════════════════════════════════════════════
        // 1. DEPT CHANGE
        // ══════════════════════════════════════════════════════════════
        function onDeptChange() {
            const sel = document.getElementById('dept');
            state.deptId = sel.value;
            state.deptName = sel.options[sel.selectedIndex]?.text || '';
            state.doctor = null;
            clearSlotState();

            // Reset doctor select
            const docSel = document.getElementById('doctor');
            docSel.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
            docSel.disabled = !state.deptId;

            (doctorsByDept[state.deptId] || []).forEach(d => {
                const o = document.createElement('option');
                o.value = d.doctor_id;
                o.textContent = `BS. ${d.full_name}`;
                docSel.appendChild(o);
            });

            renderDoctorCard(null);
            renderTimeslots([]);
            updateSummary();

            // Feature 1: tải gợi ý khi có khoa + ngày
            if (state.deptId && state.date) loadSuggestions();
            else hideSuggestions();
        }

        // ══════════════════════════════════════════════════════════════
        // 2. DOCTOR CHANGE
        // ══════════════════════════════════════════════════════════════
        function onDoctorChange() {
            const val = document.getElementById('doctor').value;
            if (!val) {
                state.doctor = null;
                renderDoctorCard(null);
                renderTimeslots([]);
                updateSummary();
                return;
            }
            const list = doctorsByDept[state.deptId] || [];
            state.doctor = list.find(d => String(d.doctor_id) === String(val)) || null;
            clearSlotState();

            renderDoctorCard(state.doctor);
            loadTimeslots();      // Feature 2
            updateSummary();

            // Highlight card gợi ý tương ứng
            highlightSuggestCard(val);
        }

        // ══════════════════════════════════════════════════════════════
        // 3. DATE CHANGE
        // ══════════════════════════════════════════════════════════════
        function onDateChange() {
            state.date = document.getElementById('work_date').value;
            clearSlotState();
            if (state.doctor) loadTimeslots();
            if (state.deptId && state.date) loadSuggestions();
            updateSummary();
        }

        // ══════════════════════════════════════════════════════════════
        // FEATURE 1 — GỢI Ý BÁC SĨ TỰ ĐỘNG
        // ══════════════════════════════════════════════════════════════
        function loadSuggestions() {
            if (!state.deptId || !state.date) { hideSuggestions(); return; }

            const key = `${state.deptId}_${state.date}`;
            if (suggestCache[key] !== undefined) {
                renderSuggestions(suggestCache[key]);
                return;
            }

            // Skeleton loading
            const section = document.getElementById('suggest-section');
            const grid = document.getElementById('suggest-grid');
            section.style.display = 'block';
            grid.innerHTML = `<div class="suggest-loading">
        <div class="mini-spin"></div>
        Đang tìm bác sĩ phù hợp nhất...
    </div>`;

            fetch(`${ROUTE_SUGGEST}?department_id=${state.deptId}&work_date=${state.date}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    suggestCache[key] = data.suggested || [];
                    renderSuggestions(suggestCache[key]);
                })
                .catch(() => hideSuggestions());
        }

        function renderSuggestions(doctors) {
            const section = document.getElementById('suggest-section');
            const grid = document.getElementById('suggest-grid');
            const sub = document.getElementById('suggest-sub');

            if (!doctors || doctors.length === 0) { hideSuggestions(); return; }

            section.style.display = 'block';
            sub.textContent = `${doctors.length} bác sĩ có lịch`;

            const rankClass = ['r1', 'r2', 'r3'];
            grid.innerHTML = doctors.map((doc, i) => {
                const rating = parseFloat(doc.avg_rating) || 0;
                const reviews = parseInt(doc.total_reviews) || 0;
                const avail = parseInt(doc.available_slots) || 0;
                const exp = parseInt(doc.experience) || 0;
                const price = parseInt(doc.price) || 0;
                const initials = doc.full_name.split(' ').slice(-2).map(w => w[0]).join('').toUpperCase();
                const avatar = doc.avatar_url
                    ? `<img src="${doc.avatar_url}" alt="${doc.full_name}">`
                    : initials;
                const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));

                return `
        <div class="suggest-card" id="sug-card-${doc.doctor_id}" onclick="selectSuggestedDoctor(${doc.doctor_id})">
            <div class="sug-rank ${rankClass[i]}">#${i + 1}</div>
            <div class="sug-avatar">${avatar}</div>
            <div class="sug-name">BS. ${doc.full_name}</div>
            <div class="sug-stars">${stars}
                <span style="color:var(--gray-400);font-size:.62rem">(${reviews})</span>
            </div>
            <div class="sug-tags">
                <span class="sug-tag slot">${avail} chỗ</span>
                ${exp ? `<span class="sug-tag exp">${exp}năm</span>` : ''}
                ${price ? `<span class="sug-tag price">${(price / 1000).toFixed(0)}k₫</span>` : ''}
            </div>
            <div class="sug-cta">✓ Đã chọn bác sĩ này</div>
        </div>`;
            }).join('');
        }

        function hideSuggestions() {
            document.getElementById('suggest-section').style.display = 'none';
        }

        // Click vào suggestion card → tự động chọn bác sĩ trong select
        function selectSuggestedDoctor(doctorId) {
            const docSel = document.getElementById('doctor');
            if (docSel.disabled) return;
            const opt = docSel.querySelector(`option[value="${doctorId}"]`);
            if (!opt) return;
            docSel.value = String(doctorId);
            onDoctorChange();
        }

        function highlightSuggestCard(doctorId) {
            document.querySelectorAll('.suggest-card').forEach(c => c.classList.remove('selected-sug'));
            const card = document.getElementById(`sug-card-${doctorId}`);
            if (card) card.classList.add('selected-sug');
        }

        // ══════════════════════════════════════════════════════════════
        // FEATURE 2 — DYNAMIC TIME SLOTS
        // ══════════════════════════════════════════════════════════════
        function loadTimeslots() {
            if (!state.doctor || !state.date) return;

            const key = `${state.doctor.doctor_id}_${state.date}`;
            if (timeslotCache[key] !== undefined) {
                if (timeslotCache[key] === null) {
                    showSlotDayOff();
                } else {
                    renderTimeslots(timeslotCache[key]);
                }
                return;
            }

            // Loading state
            document.getElementById('slot-wrap').innerHTML =
                '<div style="display:flex;align-items:center;gap:10px;font-size:.82rem;color:var(--gray-400)">' +
                '<div class="mini-spin"></div>Đang tải khung giờ...</div>';
            document.getElementById('slot-legend').style.display = 'none';
            document.getElementById('slot-stats-bar').style.display = 'none';

            fetch(`${ROUTE_TIMESLOTS}?doctor_id=${state.doctor.doctor_id}&work_date=${state.date}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.day_off) {
                        timeslotCache[key] = null;
                        showSlotDayOff();
                        return;
                    }
                    timeslotCache[key] = data.slots || [];
                    renderTimeslots(timeslotCache[key]);
                })
                .catch(() => {
                    document.getElementById('slot-wrap').innerHTML =
                        '<span class="slot-placeholder" style="color:#e5484d">⚠️ Lỗi tải khung giờ. Vui lòng thử lại.</span>';
                });
        }

        function showSlotDayOff() {
            document.getElementById('slot-wrap').innerHTML =
                '<span class="slot-placeholder">🚫 Bác sĩ nghỉ ngày này. Vui lòng chọn ngày khác.</span>';
            document.getElementById('slot-legend').style.display = 'none';
            document.getElementById('slot-stats-bar').style.display = 'none';
        }

        // ══════════════════════════════════════════════════════════════
        // FETCH & DISPLAY QUEUE INFO
        // ══════════════════════════════════════════════════════════════
        function fetchQueueInfo(scheduleId, appointmentTime = null) {
            const card = document.getElementById('queue-card');
            const spinner = document.getElementById('queue-loading-spinner');

            // Show card & spinner
            card.classList.remove('hidden');
            spinner.style.display = 'flex';
            document.getElementById('queue-list-container').style.display = 'none';

            // Build query string with schedule_id and optional appointment_time
            if (!scheduleId || !Number.isInteger(Number(scheduleId)) || Number(scheduleId) <= 0) {
                spinner.innerHTML = '<span class="slot-placeholder" style="color:#e5484d">⚠️ Thiếu thông tin khung giờ.</span>';
                console.warn('[fetchQueueInfo] invalid scheduleId', scheduleId);
                return;
            }

            let queryUrl = `${ROUTE_QUEUE_INFO}?schedule_id=${encodeURIComponent(scheduleId)}`;
            if (appointmentTime) {
                queryUrl += `&appointment_time=${encodeURIComponent(appointmentTime)}`;
            }

            fetch(queryUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
                .then(res => {
                    const contentType = res.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        return res.text().then(text => {
                            throw new Error('Unexpected response: ' + text.slice(0, 200));
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        spinner.innerHTML = '<span style="color:#e5484d">❌ Không thể tải thông tin hàng đợi</span>';
                        return;
                    }

                    // Hiển thị thông tin hàng đợi
                    displayQueueInfo(data);
                    spinner.style.display = 'none';
                })
                .catch(err => {
                    console.error('Queue fetch error:', err);
                    spinner.innerHTML = '<span class="slot-placeholder" style="color:#e5484d">❌ Lỗi kết nối</span>';
                });
        }

        function displayQueueInfo(data) {
            const {
                queue_number,
                people_ahead,
                estimated_wait_minutes,
                queue_details
            } = data;

            // Cập nhật số thứ tự
            document.getElementById('queue-number-display').textContent = queue_number;

            // Cập nhật thời gian chờ dự kiến
            let waitTimeText = '';
            if (estimated_wait_minutes === 0) {
                waitTimeText = '< 5 phút';
            } else if (estimated_wait_minutes < 60) {
                waitTimeText = estimated_wait_minutes + ' phút';
            } else {
                const hours = Math.floor(estimated_wait_minutes / 60);
                const mins = estimated_wait_minutes % 60;
                waitTimeText = hours + 'h ' + (mins > 0 ? mins + 'p' : '');
            }
            document.getElementById('estimated-wait-time').textContent = waitTimeText;

            // Cập nhật số người đứng trước
            document.getElementById('people-ahead-count').textContent = people_ahead + ' người';

            // Cập nhật danh sách người đứng trước
            const listContainer = document.getElementById('queue-list-container');
            const peopleList = document.getElementById('queue-people-list');

            if (queue_details && queue_details.length > 0) {
                listContainer.style.display = 'block';
                peopleList.innerHTML = queue_details.map((person, idx) => `
                    <div class="queue-person">
                        <span class="queue-person-num">${person.queue_number}</span>
                        <span class="queue-person-name">${person.abbreviated_name}</span>
                        <span style="color: var(--gray-400); font-size: .7rem; margin-left: auto;">
                            ${person.status === 'Đã xác nhận' ? '✓ Xác nhận' : '⏳ Chờ'}
                        </span>
                    </div>
                `).join('');
            } else {
                listContainer.style.display = 'none';
            }
        }

        // Phân loại slot theo buổi
        function getSession(timeStr) {
            const h = parseInt(timeStr.split(':')[0]);
            if (h < 12) return 'Buổi sáng';
            if (h < 17) return 'Buổi chiều';
            return 'Buổi tối';
        }

        function renderTimeslots(slots) {
            const wrap = document.getElementById('slot-wrap');
            const legend = document.getElementById('slot-legend');
            const stats = document.getElementById('slot-stats-bar');
            clearSlotState();

            if (!slots || slots.length === 0) {
                wrap.innerHTML = `<span class="slot-placeholder">${state.doctor ? 'Không có lịch khám cho ngày này' : 'Vui lòng chọn bác sĩ và ngày'
                    }</span>`;
                legend.style.display = 'none';
                stats.style.display = 'none';
                return;
            }

            // Cập nhật stats
            const totalCount = slots.length;
            const fullCount = slots.filter(s => s.is_booked).length;
            const availCount = totalCount - fullCount;
            document.getElementById('stat-total').textContent = totalCount;
            document.getElementById('stat-avail').textContent = availCount;
            document.getElementById('stat-full').textContent = fullCount;
            stats.style.display = 'flex';

            // Nhóm theo buổi
            const groups = {};
            slots.forEach(s => {
                const session = getSession(s.time);
                if (!groups[session]) groups[session] = [];
                groups[session].push(s);
            });

            let html = '';
            ['Buổi sáng', 'Buổi chiều', 'Buổi tối'].forEach(session => {
                if (!groups[session]) return;
                html += `<div class="slot-session-label">${session}</div>`;
                groups[session].forEach(s => {
                    const cls = s.is_booked ? 'slot-full' : '';
                    html += `<button type="button"
                        class="slot-btn ${cls}"
                        ${s.is_booked ? 'disabled title="Đã đầy"' : ''}
                        data-sid="${s.schedule_id}"
                        data-time="${s.time}"
                        data-end="${s.end_time || ''}"
                        onclick="selectTimeslot(this)">
                        ${s.time}
                        <span class="slot-end">${s.end_time ? '→ ' + s.end_time : ''}</span>
                    </button>`;
                });
            });

            wrap.innerHTML = html;
            legend.style.display = 'flex';
            updateSummary();
        }

        async function selectTimeslot(el) {
            if (el.disabled || el.classList.contains('slot-full')) return;
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');

            const scheduleId = parseInt(el.dataset.sid, 10);
            const appointmentTime = el.dataset.time?.trim() || null;
            const appointmentEnd = el.dataset.end?.trim() || '';

            if (!Number.isInteger(scheduleId) || scheduleId <= 0 || !appointmentTime) {
                console.warn('[selectTimeslot] invalid slot data', {
                    scheduleId: el.dataset.sid,
                    appointmentTime: el.dataset.time,
                    scheduleIdParsed: scheduleId,
                });
                showHoldError('Dữ liệu khung giờ không hợp lệ. Vui lòng chọn lại.');
                clearSlotSelection();
                return;
            }

            if (state.scheduleId && (state.scheduleId !== scheduleId || state.time !== appointmentTime)) {
                await releaseCurrentHold();
            }

            state.scheduleId = scheduleId;
            state.time = appointmentTime;
            state.timeEnd = appointmentEnd;

            document.getElementById('schedule_id').value = state.scheduleId;
            document.getElementById('appointment_time').value = state.time;
            document.getElementById('slot-error').style.display = 'none';
            updateSummary();

            const holdSuccess = await holdSelectedSlot(state.scheduleId, state.time);
            if (!holdSuccess) {
                return;
            }

            // ✅ Fetch queue info khi chọn slot (gửi cả appointment_time để filter chính xác)
            if (Number.isInteger(state.scheduleId) && state.scheduleId > 0) {
                fetchQueueInfo(state.scheduleId, state.time);
            }
        }

        function clearSlotState() {
            state.scheduleId = null;
            state.time = null;
            state.timeEnd = null;
            document.getElementById('schedule_id').value = '';
            document.getElementById('appointment_time').value = '';

            // Hide queue card when slot is cleared
            document.getElementById('queue-card').classList.add('hidden');
        }

        // ══════════════════════════════════════════════════════════════
        // RENDER DOCTOR CARD (sidebar)
        // ══════════════════════════════════════════════════════════════
        function renderDoctorCard(doc) {
            const body = document.getElementById('doctor-body');
            if (!doc) {
                body.innerHTML = `<div style="padding:20px 0;color:var(--gray-400);text-align:center">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <p style="margin-top:10px;font-size:.82rem">Chưa chọn bác sĩ</p>
        </div>`;
                return;
            }

            const rating = parseFloat(doc.avg_rating) || 0;
            const reviews = parseInt(doc.total_reviews) || 0;
            const price = parseInt(doc.price) || 0;
            const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
            const initials = doc.full_name.split(' ').slice(-2).map(w => w[0]).join('').toUpperCase();
            const avatar = doc.avatar_url
                ? `<img src="${doc.avatar_url}" alt="${doc.full_name}">`
                : initials;

            body.innerHTML = `
        <div class="doc-avatar">${avatar}</div>
        <div class="doc-name">BS. ${doc.full_name}</div>
        <div class="doc-bio">${doc.bio || ''}${doc.experience ? ' · ' + doc.experience + ' năm kinh nghiệm' : ''}</div>
        <div class="doc-stars">${stars}
            <span style="color:var(--gray-400)">(${reviews.toLocaleString('vi-VN')})</span>
        </div>
        <div class="doc-meta" style="margin-top:14px;border-top:1px solid var(--gray-100);padding-top:12px">
            <div class="doc-meta-row">
                <span style="color:var(--gray-500)">Đánh giá</span>
                <span style="font-weight:800">${rating.toFixed(1)} / 5.0</span>
            </div>
            <div class="doc-meta-row">
                <span style="color:var(--gray-500)">💰 Phí khám</span>
                <span style="font-weight:800;color:var(--primary)">${price.toLocaleString('vi-VN')} ₫</span>
            </div>
        </div>`;
        }

        // ══════════════════════════════════════════════════════════════
        // UPDATE SUMMARY SIDEBAR
        // ══════════════════════════════════════════════════════════════
        function updateSummary() {
            const svcSel = document.getElementById('service_id_select');
            const svcText = (svcSel && svcSel.selectedIndex > 0)
                ? svcSel.options[svcSel.selectedIndex].text : '';

            const timeDisplay = state.time
                ? (state.timeEnd ? `${state.time} – ${state.timeEnd}` : state.time)
                : '';

            setSum('sum-dept', state.deptName);
            setSum('sum-doctor', state.doctor ? `BS. ${state.doctor.full_name}` : '');
            setSum('sum-svc', svcText);
            setSum('sum-date', state.date ? state.date.split('-').reverse().join('/') : '');
            setSum('sum-time', timeDisplay);
        }

        function setSum(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (val) { el.textContent = val; el.classList.remove('empty'); }
            else { el.textContent = '—'; el.classList.add('empty'); }
        }

        // ══════════════════════════════════════════════════════════════════
        // SLOT HOLD — Giữ slot tạm thời
        // ══════════════════════════════════════════════════════════════════

        const ROUTE_SLOT_HOLD = '{{ route("slot.hold") }}';
        const ROUTE_SLOT_RELEASE = '{{ route("slot.release") }}';
        const ROUTE_SLOT_STATUS = '{{ route("slot.status") }}';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        let holdCountdownTimer = null;   // setInterval handle

        // ── Gọi khi bệnh nhân click vào một slot ─────────────────────────
        // Thay thế / bổ sung vào hàm selectTimeslot() hiện có.
        // Thêm dòng: holdSelectedSlot(state.scheduleId, state.time);
        // Ở cuối hàm selectTimeslot().

        async function holdSelectedSlot(scheduleId, appointmentTime) {
            if (!scheduleId || !appointmentTime) return false;

            try {
                const res = await fetch(ROUTE_SLOT_HOLD, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ schedule_id: scheduleId, appointment_time: appointmentTime }),
                });

                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await res.text();
                    throw new Error('Unexpected non-JSON response: ' + text.slice(0, 200));
                }

                const data = await res.json();

                if (!res.ok || !data.success) {
                    stopHoldCountdown();
                    clearSlotSelection();
                    showHoldError(data.message ?? 'Không thể giữ khung giờ này.');
                    return false;
                }

                state.holdAppointmentId = data.appointment_id ?? null;
                state.holdExpiresAt = data.expires_at ? new Date(data.expires_at) : null;
                startHoldCountdown(data.seconds_remaining ?? 300, data.expires_at);
                showHoldBanner(data.message);
                return true;

            } catch (err) {
                console.error('[SlotHold] hold error:', err);
                stopHoldCountdown();
                clearSlotSelection();
                showHoldError('Không thể giữ khung giờ này. Vui lòng thử lại.');
                return false;
            }
        }

        // ── Giải phóng hold khi user rời trang ───────────────────────────
        function releaseCurrentHold() {
            if (!state.scheduleId && !state.holdAppointmentId) return;

            const payload = state.holdAppointmentId
                ? JSON.stringify({ appointment_id: state.holdAppointmentId })
                : JSON.stringify({ schedule_id: state.scheduleId });

            fetch(ROUTE_SLOT_RELEASE, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: payload,
                keepalive: true,
            }).catch(err => {
                console.warn('[SlotHold] release failed on unload:', err);
            });

            state.holdAppointmentId = null;
            state.holdExpiresAt = null;
            stopHoldCountdown();
        }

        // Tự động giải phóng khi đóng tab / chuyển trang
        window.addEventListener('beforeunload', releaseCurrentHold);
        window.addEventListener('pagehide', releaseCurrentHold);

        // ── Countdown UI ─────────────────────────────────────────────────
        function startHoldCountdown(secondsRemaining, expiresAt) {
            stopHoldCountdown();

            if (expiresAt) {
                state.holdExpiresAt = new Date(expiresAt);
            }

            if (!state.holdExpiresAt) {
                secondsRemaining = parseInt(secondsRemaining, 10);
                if (Number.isNaN(secondsRemaining)) {
                    secondsRemaining = 0;
                }
                state.holdExpiresAt = new Date(Date.now() + secondsRemaining * 1000);
            }

            const updateTimer = () => {
                if (!state.holdExpiresAt) {
                    onHoldExpired();
                    return;
                }
                const diffMs = state.holdExpiresAt - new Date();
                const remainingSeconds = Math.max(0, Math.ceil(diffMs / 1000));
                if (remainingSeconds <= 0) {
                    onHoldExpired();
                    return;
                }
                renderCountdown(remainingSeconds);
            };

            updateTimer();
            holdCountdownTimer = setInterval(updateTimer, 1000);
        }

        function stopHoldCountdown() {
            if (holdCountdownTimer) {
                clearInterval(holdCountdownTimer);
                holdCountdownTimer = null;
            }
        }

        function renderCountdown(seconds) {
            const banner = document.getElementById('hold-countdown-banner');
            if (!banner) return;

            // Ensure we never render negative or non-numeric values
            const remaining = Math.max(0, Math.floor(Number(seconds) || 0));
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const s = String(remaining % 60).padStart(2, '0');

            banner.style.display = 'flex';
            const timerEl = banner.querySelector('.hold-timer');
            if (timerEl) timerEl.textContent = `${m}:${s}`;

            // Đổi màu đỏ khi còn < 60 giây
            banner.classList.toggle('hold-urgent', remaining < 60);
        }

        function onHoldExpired() {
            const banner = document.getElementById('hold-countdown-banner');
            if (banner) {
                banner.style.display = 'none';
            }
            state.holdAppointmentId = null;
            state.holdExpiresAt = null;
            // Reset lựa chọn slot
            clearSlotSelection();
            // Thông báo
            showHoldError('Thời gian giữ khung giờ đã hết. Vui lòng chọn lại khung giờ.');
            // Reload timeslots để cập nhật trạng thái
            if (state.doctor && state.date) {
                const key = `${state.doctor.doctor_id}_${state.date}`;
                delete timeslotCache[key];   // Xoá cache để fetch lại
                loadTimeslots();
            }
        }

        // ── Helper: xoá lựa chọn slot hiện tại (không xoá state.scheduleId ngay) ──
        function clearSlotSelection() {
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
            state.scheduleId = null;
            state.time = null;
            state.timeEnd = null;
            state.holdAppointmentId = null;
            state.holdExpiresAt = null;
            document.getElementById('schedule_id').value = '';
            document.getElementById('appointment_time').value = '';
            document.getElementById('queue-card').classList.add('hidden');
            stopHoldCountdown();
            const banner = document.getElementById('hold-countdown-banner');
            if (banner) banner.style.display = 'none';
        }

        // ── Banner & Error UI ─────────────────────────────────────────────
        function showHoldBanner(message) {
            // Banner được inject từ Blade (xem HTML bên dưới)
            const banner = document.getElementById('hold-countdown-banner');
            const msg = document.getElementById('hold-banner-msg');
            const errorEl = document.getElementById('slot-error');
            if (msg) msg.textContent = message;
            if (banner) banner.style.display = 'flex';
            if (errorEl) {
                errorEl.style.display = 'none';
                errorEl.textContent = '';
            }
        }

        function showHoldError(msg) {
            const errEl = document.getElementById('slot-error');
            if (!errEl) return;
            errEl.textContent = '⚠️ ' + msg;
            errEl.style.display = 'block';
            errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // FORM SUBMIT
        // ══════════════════════════════════════════════════════════════
        document.getElementById('booking-form').addEventListener('submit', function (e) {
            if (!state.scheduleId || !state.time) {
                e.preventDefault();
                const errEl = document.getElementById('slot-error');
                errEl.style.display = 'block';
                document.getElementById('slot-wrap').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            const btn = document.getElementById('submit-btn');
            const spinner = document.getElementById('spinner');
            const icon = document.getElementById('submit-icon');
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            icon.style.display = 'none';
        });

        // Service dropdown → cập nhật summary
        document.getElementById('service_id_select')?.addEventListener('change', updateSummary);

        // ── INIT ──
        updateSummary();
    </script>

    <script>
        function togglePriorityType() {
            var isPriority = document.getElementById('is_priority').checked;
            var container = document.getElementById('priority_type_container');
            container.style.display = isPriority ? 'block' : 'none';
        }
    </script>
</body>

</html>