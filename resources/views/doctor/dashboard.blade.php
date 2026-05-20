{{-- resources/views/doctor/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Bác sĩ – MediBook')

@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');

        :root {
            --c-bg: #f0f4f8;
            --c-surface: #ffffff;
            --c-border: #e2e8f0;
            --c-text: #1a202c;
            --c-muted: #718096;
            --c-blue: #2563eb;
            --c-green: #059669;
            --c-yellow: #d97706;
            --c-red: #dc2626;
            --c-purple: #7c3aed;
            --c-teal: #0d9488;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 4px 12px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .12);
        }

        header svg,
        nav svg {
            width: 16px !important;
            height: 16px !important;
            flex-shrink: 0;
        }

        header .w-10 {
            width: 40px;
            height: 40px;
        }

        /* Nav layout */
        header .flex {
            display: flex;
        }

        header .items-center {
            align-items: center;
        }

        nav .flex {
            display: flex;
        }

        nav a {
            text-decoration: none;
            color: #4b5563;
        }

        nav a.active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--c-bg);
            color: var(--c-text);
        }

        /* ── Cards ── */
        .card {
            background: var(--c-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Stats ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: var(--c-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            border-top: 3px solid transparent;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-card.blue {
            border-color: var(--c-blue);
        }

        .stat-card.green {
            border-color: var(--c-green);
        }

        .stat-card.yellow {
            border-color: var(--c-yellow);
        }

        .stat-card.purple {
            border-color: var(--c-purple);
        }

        .stat-card.teal {
            border-color: var(--c-teal);
        }

        .stat-card.red {
            border-color: var(--c-red);
        }

        .stat-label {
            font-size: .72rem;
            color: var(--c-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-value.blue {
            color: var(--c-blue);
        }

        .stat-value.green {
            color: var(--c-green);
        }

        .stat-value.yellow {
            color: var(--c-yellow);
        }

        .stat-value.purple {
            color: var(--c-purple);
        }

        .stat-value.teal {
            color: var(--c-teal);
        }

        .stat-value.red {
            color: var(--c-red);
        }

        /* ── Appointments ── */
        .apt-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--c-border);
            transition: background .15s;
        }

        .apt-row:hover {
            background: #f7fafc;
        }

        .apt-row+.apt-row {
            margin-top: 10px;
        }

        .apt-queue {
            min-width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            color: var(--c-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .apt-info {
            flex: 1;
            min-width: 0;
        }

        .apt-name {
            font-weight: 600;
            font-size: .95rem;
        }

        .apt-sub {
            font-size: .8rem;
            color: var(--c-muted);
            margin-top: 2px;
        }

        .apt-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 6px;
            font-size: .78rem;
            color: var(--c-muted);
        }

        .apt-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .apt-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: filter .15s, transform .1s;
            font-family: inherit;
        }

        .btn:active {
            transform: scale(.97);
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-blue {
            background: var(--c-blue);
            color: #fff;
        }

        .btn-blue:not(:disabled):hover {
            filter: brightness(1.1);
        }

        .btn-green {
            background: var(--c-green);
            color: #fff;
        }

        .btn-green:not(:disabled):hover {
            filter: brightness(1.1);
        }

        .btn-red {
            background: var(--c-red);
            color: #fff;
        }

        .btn-red:not(:disabled):hover {
            filter: brightness(1.1);
        }

        .btn-yellow {
            background: var(--c-yellow);
            color: #fff;
        }

        .btn-yellow:not(:disabled):hover {
            filter: brightness(1.1);
        }

        .btn-outline {
            background: transparent;
            color: var(--c-blue);
            border: 1.5px solid var(--c-blue);
        }

        .btn-outline:hover {
            background: #eff6ff;
        }

        .btn-ghost {
            background: transparent;
            color: var(--c-muted);
            border: 1.5px solid var(--c-border);
        }

        .btn-ghost:hover {
            background: #f1f5f9;
            color: var(--c-text);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: .78rem;
        }

        .btn-icon {
            padding: 6px 8px;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 100px;
            font-size: .72rem;
            font-weight: 600;
        }

        .badge-warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-primary {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        /* ── Reviews ── */
        .review-card {
            border: 1px solid var(--c-border);
            border-radius: 10px;
            padding: 16px;
            transition: box-shadow .15s;
        }

        .review-card:hover {
            box-shadow: var(--shadow);
        }

        .review-card+.review-card {
            margin-top: 12px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .review-patient {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--c-muted);
            font-size: .85rem;
            overflow: hidden;
            flex-shrink: 0;
        }

        .review-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .review-name {
            font-weight: 600;
            font-size: .9rem;
        }

        .review-date {
            font-size: .75rem;
            color: var(--c-muted);
        }

        .stars {
            color: #f59e0b;
            font-size: .95rem;
            letter-spacing: 1px;
        }

        .review-comment {
            margin: 10px 0;
            font-size: .88rem;
            line-height: 1.55;
            color: #374151;
        }

        .reply-box {
            background: #f8fafc;
            border-left: 3px solid var(--c-teal);
            padding: 10px 14px;
            border-radius: 0 8px 8px 0;
            margin-top: 10px;
            font-size: .84rem;
        }

        .reply-label {
            font-size: .72rem;
            font-weight: 700;
            color: var(--c-teal);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .reply-form {
            margin-top: 10px;
        }

        .reply-form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            font-size: .85rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: border-color .15s;
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: var(--c-teal);
        }

        .reply-form-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            justify-content: flex-end;
        }

        /* ── Tabs ── */
        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--c-border);
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 9px 18px;
            font-size: .85rem;
            font-weight: 600;
            color: var(--c-muted);
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color .15s;
            font-family: inherit;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: var(--c-blue);
            border-bottom-color: var(--c-blue);
        }

        .tab-btn:hover:not(.active) {
            color: var(--c-text);
        }

        .tab-btn.tab-admin {
            color: #9333ea;
        }

        .tab-btn.tab-admin.active {
            color: var(--c-purple);
            border-bottom-color: var(--c-purple);
        }

        .tab-btn.tab-admin:hover:not(.active) {
            color: var(--c-purple);
        }

        /* ── Doctor filter dropdown ── */
        .doctor-filter {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: #eff6ff;
            border-radius: var(--radius);
            margin-bottom: 24px;
            border: 1px solid #bfdbfe;
        }

        .doctor-filter label {
            font-size: .85rem;
            font-weight: 600;
            color: #1d4ed8;
            white-space: nowrap;
        }

        .doctor-filter select {
            flex: 1;
            max-width: 340px;
            padding: 8px 12px;
            border: 1.5px solid #93c5fd;
            border-radius: 8px;
            font-size: .88rem;
            font-family: inherit;
            background: #fff;
        }

        .doctor-filter select:focus {
            outline: none;
            border-color: var(--c-blue);
        }

        /* ── Doctor CRUD table ── */
        .doc-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .doc-toolbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .search-wrap {
            position: relative;
        }

        .search-wrap svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: var(--c-muted);
            pointer-events: none;
        }

        .search-input {
            padding: 8px 12px 8px 34px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            font-size: .85rem;
            font-family: inherit;
            width: 220px;
            background: #fff;
            transition: border-color .15s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--c-blue);
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .doc-table th {
            text-align: left;
            padding: 10px 12px;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--c-muted);
            border-bottom: 2px solid var(--c-border);
            white-space: nowrap;
        }

        .doc-table td {
            padding: 12px 12px;
            border-bottom: 1px solid var(--c-border);
            vertical-align: middle;
        }

        .doc-table tr:last-child td {
            border-bottom: none;
        }

        .doc-table tbody tr {
            transition: background .12s;
        }

        .doc-table tbody tr:hover {
            background: #f7fafc;
        }

        .doc-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .8rem;
            color: var(--c-muted);
            overflow: hidden;
            flex-shrink: 0;
        }

        .doc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .doc-name {
            font-weight: 600;
            font-size: .88rem;
        }

        .doc-id {
            font-size: .72rem;
            color: var(--c-muted);
        }

        .doc-price {
            font-weight: 700;
            color: var(--c-green);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: .72rem;
            font-weight: 600;
        }

        .status-pill.on {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pill.off {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.on {
            background: var(--c-green);
        }

        .status-dot.off {
            background: var(--c-red);
        }

        .doc-action-cell {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        /* ── Pagination ── */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .page-btn {
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1.5px solid var(--c-border);
            background: #fff;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            font-family: inherit;
        }

        .page-btn.active {
            background: var(--c-blue);
            color: #fff;
            border-color: var(--c-blue);
        }

        .page-btn:hover:not(.active):not(:disabled) {
            background: #f1f5f9;
        }

        .page-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        /* ── Empty ── */
        .empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--c-muted);
            font-size: .9rem;
        }

        .empty svg {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            opacity: .25;
            display: block;
        }

        /* ── Toast ── */
        #toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            min-width: 260px;
            max-width: 360px;
            padding: 14px 18px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: toastIn .3s ease;
            border-left: 4px solid transparent;
            pointer-events: auto;
        }

        .toast.success {
            border-left-color: var(--c-green);
        }

        .toast.error {
            border-left-color: var(--c-red);
        }

        .toast-msg {
            font-size: .85rem;
            font-weight: 500;
            line-height: 1.4;
        }

        @keyframes toastIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        /* ── Modals ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9000;
            padding: 16px;
            animation: fadeIn .2s ease;
        }

        .modal-box {
            background: #fff;
            border-radius: var(--radius);
            padding: 28px;
            width: 100%;
            box-shadow: var(--shadow-md);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-box.sm {
            max-width: 420px;
        }

        .modal-box.md {
            max-width: 560px;
        }

        .modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--c-border);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* ── Form grid ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field.span2 {
            grid-column: span 2;
        }

        .field label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--c-text);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .field input,
        .field select,
        .field textarea {
            padding: 9px 12px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            font-size: .88rem;
            font-family: inherit;
            width: 100%;
            transition: border-color .15s;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--c-blue);
        }

        .field textarea {
            resize: vertical;
            min-height: 80px;
        }

        .field .err {
            font-size: .74rem;
            color: var(--c-red);
            margin-top: 2px;
            display: none;
        }

        .field.has-err input,
        .field.has-err select,
        .field.has-err textarea {
            border-color: var(--c-red);
        }

        .field.has-err .err {
            display: block;
        }

        /* delete modal */
        .danger-box {
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: .88rem;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        /* ── Skeleton ── */
        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 6px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0
            }

            100% {
                background-position: -200% 0
            }
        }

        @media(max-width:640px) {
            .apt-row {
                flex-direction: column
            }

            .apt-actions {
                width: 100%
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr)
            }

            .form-grid {
                grid-template-columns: 1fr
            }

            .field.span2 {
                grid-column: span 1
            }

            .doc-table th:nth-child(n+4),
            .doc-table td:nth-child(n+4) {
                display: none
            }

            .doc-table th:last-child,
            .doc-table td:last-child {
                display: table-cell
            }
        }
    </style>
@endpush
<header style="background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:50">
    <div style="max-width:72rem;margin:0 auto;padding:0 1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;height:64px">
            <div style="display:flex;align-items:center;gap:12px">
                <div
                    style="width:40px;height:40px;background:#2563eb;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <a href="{{ route('home') }}" class="active">
                        <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin:0;">
                            MediCore<sup>®</sup>
                        </h1>
                    </a>

                    <p style="font-size:.75rem;color:#6b7280;margin:0">Quản lý bác sĩ</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:16px">
                <span style="font-size:.875rem;font-weight:500;color:#374151">
                    {{ auth()->user()->doctor->full_name ?? auth()->user()->full_name ?? 'Bác sĩ' }}
                </span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit"
                        style="font-size:.875rem;color:#6b7280;background:none;border:none;cursor:pointer;font-family:inherit"
                        onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#6b7280'">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<nav style="background:#fff;border-bottom:1px solid #e2e8f0">
    <div style="max-width:72rem;margin:0 auto;padding:0 1rem">
        <div style="display:flex;gap:4px;overflow-x:auto">
            <a href="{{ route('doctor.dashboard') }}"
                style="display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:.875rem;font-weight:500;white-space:nowrap;text-decoration:none;color:#2563eb;border-bottom:2px solid #2563eb">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ auth()->user()->doctor->full_name ?? auth()->user()->full_name ?? 'Bác sĩ' }}
            </a>
            <a href="{{ route('doctor.schedule') }}"
                style="display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:.875rem;font-weight:500;white-space:nowrap;text-decoration:none;color:#6b7280;border-bottom:2px solid transparent">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Lịch làm việc
            </a>
            @auth
                @if(auth()->user()->is_admin ?? false)
                    <a href="{{ route('admin.dashboard') }}"
                        style="display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:.875rem;font-weight:500;white-space:nowrap;text-decoration:none;color:#6b7280;border-bottom:2px solid transparent">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        Thống kê
                    </a>
                @endif
            @endauth
        </div>
    </div>
</nav>
@section('content')

    <div class="max-w-6xl mx-auto px-4 py-8">

        {{-- ── Page header ── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-1">Dashboard Bác sĩ</h1>
            <p style="color:var(--c-muted);font-size:.9rem">
                @if($isAdmin)
                    <span
                        style="background:#fee2e2;color:#991b1b;padding:2px 10px;border-radius:100px;font-size:.72rem;font-weight:700;letter-spacing:.04em">ADMIN</span>
                    Toàn quyền quản lý
                @else
                    Xin chào, <strong>{{ $currentDoctor->full_name }}</strong>
                @endif
            </p>
        </div>

        {{-- ── Admin: doctor filter ── --}}
        @if($isAdmin)
            <div class="doctor-filter">
                <svg style="width:18px;height:18px;color:#2563eb;flex-shrink:0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <label for="doctor-select">Xem theo bác sĩ:</label>
                <select id="doctor-select" onchange="onDoctorChange()">
                    <option value="">— Tất cả bác sĩ —</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->doctor_id }}">
                            {{ $doc->full_name }}@if($doc->department) — {{ $doc->department->name }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- ── Stats ── --}}
        <div class="stats-row mb-8">
            <div class="stat-card blue"> <span class="stat-label">Hôm nay</span> <span class="stat-value blue"
                    id="s-today">—</span> </div>
            <div class="stat-card green"> <span class="stat-label">Sắp tới</span> <span class="stat-value green"
                    id="s-upcoming">—</span> </div>
            <div class="stat-card teal"> <span class="stat-label">Hoàn thành / tháng</span> <span class="stat-value teal"
                    id="s-completed">—</span> </div>
            <div class="stat-card yellow"> <span class="stat-label">Đánh giá TB</span> <span class="stat-value yellow"
                    id="s-rating">—</span> </div>
            <div class="stat-card purple"> <span class="stat-label">Tổng đánh giá</span> <span class="stat-value purple"
                    id="s-reviews">—</span> </div>
            <div class="stat-card red"> <span class="stat-label">Chờ phản hồi</span> <span class="stat-value red"
                    id="s-pending">—</span> </div>
        </div>

        {{-- ── Tabs ── --}}
        <div class="tabs">
            <button class="tab-btn active" data-tab="today" onclick="switchTab('today')">📅 Hôm nay</button>
            <button class="tab-btn" data-tab="upcoming" onclick="switchTab('upcoming')">⏰ Sắp tới</button>
            <button class="tab-btn" data-tab="reviews" onclick="switchTab('reviews')">⭐ Đánh giá</button>
            @if($isAdmin)
                <button class="tab-btn tab-admin" data-tab="doctors" onclick="switchTab('doctors')">👨‍⚕️ Quản lý bác
                    sĩ</button>
            @endif
        </div>

        {{-- ══ Tab: Hôm nay ══ --}}
        <div id="tab-today">
            <div class="card">
                <div class="card-title">
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lịch hẹn hôm nay
                </div>
                <div id="today-list"></div>
            </div>
        </div>

        {{-- ══ Tab: Sắp tới ══ --}}
        <div id="tab-upcoming" style="display:none">
            <div class="card">
                <div class="card-title">
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Lịch hẹn sắp tới
                </div>
                <div id="upcoming-list"></div>
            </div>
        </div>

        {{-- ══ Tab: Đánh giá ══ --}}
        <div id="tab-reviews" style="display:none">
            <div class="card">
                <div class="card-title">
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    Đánh giá của bệnh nhân
                </div>
                <div id="reviews-list"></div>
                <div id="reviews-pagination" class="pagination"></div>
            </div>
        </div>

        {{-- ══ Tab: Quản lý bác sĩ (admin only) ══ --}}
        @if($isAdmin)
            <div id="tab-doctors" style="display:none">
                <div class="card">

                    {{-- Toolbar --}}
                    <div class="doc-toolbar">
                        <div class="doc-toolbar-left">
                            <div class="card-title" style="margin-bottom:0">
                                <svg style="width:18px;height:18px;color:var(--c-purple)" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                </svg>
                                Danh sách bác sĩ
                            </div>
                            <div class="search-wrap">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input class="search-input" id="doc-search" placeholder="Tìm tên, khoa..."
                                    oninput="debouncedDocSearch()">
                            </div>
                            <select id="doc-status-filter" onchange="loadDoctors(1)"
                                style="padding:8px 12px;border:1.5px solid var(--c-border);border-radius:8px;font-size:.85rem;font-family:inherit;background:#fff">
                                <option value="">Tất cả trạng thái</option>
                                <option value="1">Đang hoạt động</option>
                                <option value="0">Tạm ngưng</option>
                            </select>
                        </div>
                        <button class="btn btn-blue" onclick="openDoctorModal()">
                            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            Thêm bác sĩ
                        </button>
                    </div>

                    {{-- Table --}}
                    <div style="overflow-x:auto">
                        <table class="doc-table">
                            <thead>
                                <tr>
                                    <th>Bác sĩ</th>
                                    <th>Khoa</th>
                                    <th>Kinh nghiệm</th>
                                    <th>Giá khám</th>
                                    <th>Đánh giá</th>
                                    <th>Trạng thái</th>
                                    <th style="text-align:right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="doc-tbody">
                                <tr>
                                    <td colspan="7">
                                        <div style="height:60px;margin:4px 0" class="skeleton"></div>
                                        <div style="height:60px;margin:4px 0" class="skeleton"></div>
                                        <div style="height:60px;margin:4px 0" class="skeleton"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="doc-pagination" class="pagination"></div>
                </div>
            </div>
        @endif

    </div>{{-- end max-w --}}

    {{-- ════════════════════════════════════════
    MODAL: Hủy lịch hẹn
    ════════════════════════════════════════ --}}
    <div id="cancel-modal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeCancelModal()">
        <div class="modal-box sm">
            <div class="modal-title">❌ Hủy lịch hẹn</div>
            <p style="font-size:.85rem;color:var(--c-muted);margin-bottom:14px">Hệ thống sẽ gửi email gợi ý lịch mới cho
                bệnh nhân.</p>
            <div class="field">
                <label>Lý do hủy (tùy chọn)</label>
                <textarea id="cancel-reason" placeholder="VD: Bác sĩ bận công việc đột xuất..."></textarea>
            </div>
            <div class="modal-actions">
                <button class="btn btn-ghost" onclick="closeCancelModal()">Quay lại</button>
                <button class="btn btn-red" onclick="confirmCancel()">Xác nhận hủy</button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
    MODAL: Thêm / Sửa bác sĩ (admin only)
    ════════════════════════════════════════ --}}
    @if($isAdmin)
        <div id="doctor-modal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeDoctorModal()">
            <div class="modal-box md">
                <div class="modal-title">
                    <svg style="width:20px;height:20px;color:var(--c-purple)" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span id="doc-modal-title">Thêm bác sĩ mới</span>
                </div>

                <form id="doctor-form" onsubmit="submitDoctorForm(event)" novalidate>
                    <input type="hidden" id="f-id">

                    <div class="form-grid">
                        <div class="field span2">
                            <label>Họ và tên *</label>
                            <input id="f-full-name" type="text" placeholder="VD: BS. Nguyễn Văn An" maxlength="100">
                            <span class="err">Vui lòng nhập họ tên.</span>
                        </div>

                        <div class="field">
                            <label>User ID (tài khoản) *</label>
                            <input id="f-user-id" type="number" placeholder="ID tài khoản" min="1">
                            <span class="err">Vui lòng nhập user ID hợp lệ.</span>
                        </div>

                        <div class="field">
                            <label>Khoa *</label>
                            <select id="f-department-id">
                                <option value="">— Chọn khoa —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <span class="err">Vui lòng chọn khoa.</span>
                        </div>

                        <div class="field">
                            <label>Kinh nghiệm (năm)</label>
                            <input id="f-experience" type="number" placeholder="VD: 10" min="0" max="60">
                        </div>

                        <div class="field">
                            <label>Giá khám (VNĐ)</label>
                            <input id="f-price" type="number" placeholder="VD: 200000" min="0" step="1000">
                        </div>

                        <div class="field span2">
                            <label>URL ảnh đại diện</label>
                            <input id="f-avatar-url" type="text" placeholder="VD: images/doctors/bs-an.jpg">
                        </div>

                        <div class="field span2">
                            <label>Giới thiệu / Bio</label>
                            <textarea id="f-bio" placeholder="Chuyên môn, kinh nghiệm nổi bật..."></textarea>
                        </div>

                        <div class="field">
                            <label>Trạng thái</label>
                            <select id="f-status">
                                <option value="1">✅ Đang hoạt động</option>
                                <option value="0">⏸ Tạm ngưng</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-ghost" onclick="closeDoctorModal()">Hủy</button>
                        <button type="submit" class="btn btn-blue" id="doc-submit-btn">
                            <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span id="doc-submit-label">Thêm bác sĩ</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ════════════════════════════════════════
        MODAL: Xác nhận xóa bác sĩ (admin only)
        ════════════════════════════════════════ --}}
        <div id="delete-modal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeDeleteModal()">
            <div class="modal-box sm">
                <div class="modal-title" style="color:var(--c-red)">
                    <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Xác nhận xóa bác sĩ
                </div>
                <div class="danger-box">
                    Bạn sắp xóa <strong id="del-name"></strong>.<br>
                    Thao tác này <strong>không thể hoàn tác</strong> và sẽ xóa toàn bộ dữ liệu liên quan.
                </div>
                <div class="modal-actions">
                    <button class="btn btn-ghost" onclick="closeDeleteModal()">Hủy bỏ</button>
                    <button class="btn btn-red" id="del-confirm-btn" onclick="confirmDelete()">Xóa vĩnh viễn</button>
                </div>
            </div>
        </div>
    @endif

    <div id="toast-container"></div>
@endsection

@push('scripts')
    <script>
        // ══════════════════════════════════════════════════════
        //  CONFIG & STATE
        // ══════════════════════════════════════════════════════
        const IS_ADMIN = @json($isAdmin);
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const BASE_URL = '/doctor/dashboard';

        let currentDoctorId = IS_ADMIN ? '' : @json($currentDoctor?->doctor_id ?? 0);
        let cancelTargetId = null;
        let reviewPage = 1, reviewMeta = {};
        let docPage = 1, docMeta = {};
        let deleteTargetId = null;
        let docSearchTimer = null;

        // ══════════════════════════════════════════════════════
        //  BOOT
        // ══════════════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadToday();
        });

        // ══════════════════════════════════════════════════════
        //  UTILITIES
        // ══════════════════════════════════════════════════════
        function qs(extra = {}) {
            const p = {};
            if (currentDoctorId) p.doctor_id = currentDoctorId;
            Object.assign(p, extra);
            const s = new URLSearchParams(p).toString();
            return s ? '?' + s : '';
        }

        async function api(method, path, body = null) {
            const opts = {
                method,
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            };
            if (body) opts.body = JSON.stringify(body);
            const res = await fetch(BASE_URL + path, opts);
            return res.json();
        }

        const skeletonRows = (n = 3, h = 72) =>
            Array.from({ length: n }, () =>
                `<div style="height:${h}px;border-radius:10px;margin-bottom:10px" class="skeleton"></div>`
            ).join('');

        function statusBadge(s) {
            const map = { 'Chờ xác nhận': 'warning', 'Đã xác nhận': 'info', 'Đang khám': 'primary', 'Hoàn thành': 'success', 'Đã hủy': 'danger', 'Dời lịch': 'secondary' };
            return `<span class="badge badge-${map[s] ?? 'secondary'}">${s}</span>`;
        }
        function stars(n) { return '★'.repeat(n) + '☆'.repeat(5 - n); }
        function initials(n = '') { return (n || '').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase(); }
        function vnd(n) { return Number(n).toLocaleString('vi-VN') + ' đ'; }
        function escapeJs(s = '') { return (s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\n/g, '\\n'); }
        function escHtml(s = '') { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

        function toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span><span class="toast-msg">${escHtml(msg)}</span>`;
            document.getElementById('toast-container').appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }

        function buildPagination(containerId, meta, loadFn, currentPage) {
            const el = document.getElementById(containerId);
            if (!el || !meta.last_page || meta.last_page <= 1) { if (el) el.innerHTML = ''; return; }
            const name = loadFn.name;
            let html = `<button class="page-btn" onclick="${name}(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>‹</button>`;
            for (let p = 1; p <= meta.last_page; p++)
                html += `<button class="page-btn ${p === currentPage ? 'active' : ''}" onclick="${name}(${p})">${p}</button>`;
            html += `<button class="page-btn" onclick="${name}(${currentPage + 1})" ${currentPage >= meta.last_page ? 'disabled' : ''}>›</button>`;
            el.innerHTML = html;
        }

        // ══════════════════════════════════════════════════════
        //  TABS
        // ══════════════════════════════════════════════════════
        const ALL_TABS = ['today', 'upcoming', 'reviews', 'doctors'];

        function switchTab(name) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
            ALL_TABS.forEach(t => { const el = document.getElementById(`tab-${t}`); if (el) el.style.display = t === name ? '' : 'none'; });
            if (name === 'upcoming') loadUpcoming();
            if (name === 'reviews') loadReviews();
            if (name === 'doctors') loadDoctors();
        }

        function onDoctorChange() {
            currentDoctorId = document.getElementById('doctor-select')?.value ?? '';
            reviewPage = 1;
            loadStats(); loadToday();
            const active = document.querySelector('.tab-btn.active')?.dataset.tab;
            if (active === 'upcoming') loadUpcoming();
            if (active === 'reviews') loadReviews();
        }

        // ══════════════════════════════════════════════════════
        //  STATS
        // ══════════════════════════════════════════════════════
        async function loadStats() {
            const data = await api('GET', '/stats' + qs());
            if (!data.success) return;
            const d = data.data;
            document.getElementById('s-today').textContent = d.today;
            document.getElementById('s-upcoming').textContent = d.upcoming;
            document.getElementById('s-completed').textContent = d.completed_month;
            document.getElementById('s-rating').textContent = d.avg_rating ? `${d.avg_rating} ⭐` : '—';
            document.getElementById('s-reviews').textContent = d.total_reviews;
            document.getElementById('s-pending').textContent = d.pending_replies;
        }

        // ══════════════════════════════════════════════════════
        //  APPOINTMENTS
        // ══════════════════════════════════════════════════════
        async function loadToday() {
            const el = document.getElementById('today-list');
            el.innerHTML = skeletonRows(2);
            const data = await api('GET', '/appointments/today' + qs());
            renderAppointments(el, data.data ?? [], true);
        }

        async function loadUpcoming() {
            const el = document.getElementById('upcoming-list');
            el.innerHTML = skeletonRows(3);
            const data = await api('GET', '/appointments/upcoming' + qs());
            renderAppointments(el, data.data ?? [], false);
        }

        function renderAppointments(el, list, isToday) {
            if (!list.length) {
                el.innerHTML = `<div class="empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Không có lịch hẹn nào</div>`;
                return;
            }
            el.innerHTML = list.map((a, i) => {
                const timeStr = isToday
                    ? `<span>🕐 ${(a.appointment_time || '').split(' ')[1] ?? ''} · ${a.slot_duration || 30} phút</span>`
                    : `<span>📅 ${a.appointment_time || ''}</span>`;
                const canFinish = isToday && ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'].includes(a.status);
                const canCancel = ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'].includes(a.status);
                return `
                        <div class="apt-row" id="apt-${a.id}">
                            <div class="apt-queue">${a.queue_number || (i + 1)}</div>
                            <div class="apt-info">
                                <div class="apt-name">${escHtml(a.patient_name || '—')} ${statusBadge(a.status)}</div>
                                <div class="apt-sub">${escHtml(a.service_name || '')}${IS_ADMIN && a.doctor_name ? ' · ' + escHtml(a.doctor_name) : ''}</div>
                                <div class="apt-meta">
                                    ${timeStr}
                                    ${a.patient_phone ? `<span>📞 ${escHtml(a.patient_phone)}</span>` : ''}
                                    ${a.note ? `<span title="${escHtml(a.note)}">📝 Ghi chú</span>` : ''}
                                </div>
                            </div>
                            <div class="apt-actions">
                                ${canFinish ? `<button class="btn btn-green btn-sm" onclick="doComplete(${a.id},this)">✓ Hoàn thành</button>` : ''}
                                ${canCancel ? `<button class="btn btn-red btn-sm"   onclick="openCancelModal(${a.id})">✕ Hủy</button>` : ''}
                            </div>
                        </div>`;
            }).join('');
        }

        async function doComplete(id, btn) {
            btn.disabled = true; btn.textContent = '⏳';
            const data = await api('PATCH', `/appointments/${id}/complete`);
            toast(data.message, data.success ? 'success' : 'error');
            if (data.success) { loadStats(); loadToday(); } else { btn.disabled = false; btn.textContent = '✓ Hoàn thành'; }
        }

        function openCancelModal(id) { cancelTargetId = id; document.getElementById('cancel-reason').value = ''; document.getElementById('cancel-modal').style.display = 'flex'; }
        function closeCancelModal() { document.getElementById('cancel-modal').style.display = 'none'; cancelTargetId = null; }

        async function confirmCancel() {
            if (!cancelTargetId) return;
            const reason = document.getElementById('cancel-reason').value.trim();
            const data = await api('PATCH', `/appointments/${cancelTargetId}/cancel`, { reason });
            toast(data.message, data.success ? 'success' : 'error');
            closeCancelModal();
            if (data.success) { loadStats(); loadToday(); loadUpcoming(); }
        }

        // ══════════════════════════════════════════════════════
        //  REVIEWS
        // ══════════════════════════════════════════════════════
        async function loadReviews(page = 1) {
            reviewPage = page;
            const el = document.getElementById('reviews-list');
            el.innerHTML = skeletonRows(4);
            const data = await api('GET', '/reviews' + qs({ page, per_page: 8 }));
            reviewMeta = data.meta ?? {};
            renderReviews(el, data.data ?? []);
            buildPagination('reviews-pagination', reviewMeta, loadReviews, reviewPage);
        }

        function renderReviews(el, list) {
            if (!list.length) {
                el.innerHTML = `<div class="empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Chưa có đánh giá nào</div>`;
                return;
            }
            el.innerHTML = list.map(r => {
                const hasReply = !!r.doctor_reply;
                const avatarHtml = r.patient_avatar ? `<img src="/storage/${r.patient_avatar}" alt="">` : initials(r.patient_name);
                return `
                        <div class="review-card" id="rv-${r.id}">
                            <div class="review-header">
                                <div class="review-patient">
                                    <div class="review-avatar">${avatarHtml}</div>
                                    <div>
                                        <div class="review-name">${escHtml(r.patient_name || 'Ẩn danh')}</div>
                                        <div class="review-date">${r.created_at || ''}${IS_ADMIN && r.doctor_name ? ' · ' + escHtml(r.doctor_name) : ''}</div>
                                    </div>
                                </div>
                                <span class="stars" title="${r.rating}/5">${stars(r.rating)} (${r.rating}/5)</span>
                            </div>
                            <div class="review-comment">${r.comment ? escHtml(r.comment) : '<em style="color:var(--c-muted)">Không có bình luận</em>'}</div>

                            ${hasReply ? `
                            <div class="reply-box" id="reply-display-${r.id}">
                                <div class="reply-label">💬 Phản hồi của bác sĩ <span style="font-weight:400;color:var(--c-muted)">${r.doctor_reply_updated_at || ''}</span></div>
                                <div id="reply-text-${r.id}">${escHtml(r.doctor_reply)}</div>
                                <div style="margin-top:8px;display:flex;gap:8px">
                                    <button class="btn btn-ghost btn-sm" onclick="showReplyForm(${r.id},'${escapeJs(r.doctor_reply)}')">✏️ Sửa</button>
                                    ${IS_ADMIN ? `<button class="btn btn-ghost btn-sm" style="color:var(--c-red)" onclick="doDeleteReply(${r.id})">🗑 Xóa</button>` : ''}
                                </div>
                            </div>` : ''}

                            <div id="reply-form-${r.id}" class="reply-form" style="${hasReply ? 'display:none' : ''}">
                                <textarea id="reply-input-${r.id}" placeholder="Nhập phản hồi của bạn...">${hasReply ? escHtml(r.doctor_reply) : ''}</textarea>
                                <div class="reply-form-actions">
                                    ${hasReply ? `<button class="btn btn-ghost btn-sm" onclick="hideReplyForm(${r.id})">Hủy</button>` : ''}
                                    <button class="btn btn-outline btn-sm" onclick="doReply(${r.id})">💬 Gửi phản hồi</button>
                                </div>
                            </div>
                            ${!hasReply ? `<button class="btn btn-ghost btn-sm" style="margin-top:8px" onclick="showReplyForm(${r.id})">+ Thêm phản hồi</button>` : ''}
                        </div>`;
            }).join('');
        }

        function showReplyForm(id, existing = '') {
            const form = document.getElementById(`reply-form-${id}`), disp = document.getElementById(`reply-display-${id}`), inp = document.getElementById(`reply-input-${id}`);
            if (form) form.style.display = ''; if (disp) disp.style.display = 'none';
            if (inp && existing) inp.value = existing; inp?.focus();
        }
        function hideReplyForm(id) {
            const form = document.getElementById(`reply-form-${id}`), disp = document.getElementById(`reply-display-${id}`);
            if (form) form.style.display = 'none'; if (disp) disp.style.display = '';
        }

        async function doReply(id) {
            const inp = document.getElementById(`reply-input-${id}`);
            const val = inp?.value?.trim();
            if (!val) { toast('Vui lòng nhập nội dung phản hồi.', 'error'); return; }
            const data = await api('POST', `/reviews/${id}/reply`, { reply: val });
            toast(data.message, data.success ? 'success' : 'error');
            if (data.success) { loadStats(); loadReviews(reviewPage); }
        }

        async function doDeleteReply(id) {
            if (!confirm('Xóa phản hồi này?')) return;
            const data = await api('DELETE', `/reviews/${id}/reply`);
            toast(data.message, data.success ? 'success' : 'error');
            if (data.success) { loadStats(); loadReviews(reviewPage); }
        }

        // ══════════════════════════════════════════════════════
        //  DOCTORS CRUD  ── admin only ──
        // ══════════════════════════════════════════════════════
        async function loadDoctors(page = 1) {
            docPage = page;
            const tbody = document.getElementById('doc-tbody');
            tbody.innerHTML = `<tr><td colspan="7">${skeletonRows(4, 56)}</td></tr>`;

            const search = document.getElementById('doc-search')?.value?.trim() ?? '';
            const status = document.getElementById('doc-status-filter')?.value ?? '';
            const data = await api('GET', `/doctors/list` + qs({ page, per_page: 10, search, ...(status !== '' ? { status } : {}) }));
            docMeta = data.meta ?? {};
            renderDoctorRows(data.data ?? []);
            buildPagination('doc-pagination', docMeta, loadDoctors, docPage);
        }

        function debouncedDocSearch() {
            clearTimeout(docSearchTimer);
            docSearchTimer = setTimeout(() => loadDoctors(1), 380);
        }

        function renderDoctorRows(list) {
            const tbody = document.getElementById('doc-tbody');
            if (!list.length) {
                tbody.innerHTML = `<tr><td colspan="7"><div class="empty" style="padding:28px 0">Không tìm thấy bác sĩ nào</div></td></tr>`;
                return;
            }
            tbody.innerHTML = list.map(d => {
                const on = d.status == 1;
                const avatar = d.avatar_url
                    ? `<img src="/storage/${d.avatar_url}" alt="" onerror="this.remove()">`
                    : initials(d.full_name);
                // serialize for edit button safely
                const json = encodeURIComponent(JSON.stringify(d));
                return `
                        <tr>
                            <td>
                                <div class="doc-name-cell">
                                    <div class="doc-avatar">${avatar}</div>
                                    <div>
                                        <div class="doc-name">${escHtml(d.full_name)}</div>
                                        <div class="doc-id">ID #${d.doctor_id}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${escHtml(d.department_name || '—')}</td>
                            <td>${d.experience ?? 0} năm</td>
                            <td class="doc-price">${d.price ? vnd(d.price) : '—'}</td>
                            <td>
                                <span class="stars" style="font-size:.8rem">${stars(Math.round(d.avg_rating ?? 0))}</span>
                                <span style="font-size:.74rem;color:var(--c-muted);margin-left:4px">
                                    ${d.avg_rating ? Number(d.avg_rating).toFixed(1) : '—'} (${d.total_reviews ?? 0})
                                </span>
                            </td>
                            <td>
                                <span class="status-pill ${on ? 'on' : 'off'}">
                                    <span class="status-dot ${on ? 'on' : 'off'}"></span>
                                    ${on ? 'Hoạt động' : 'Tạm ngưng'}
                                </span>
                            </td>
                            <td>
                                <div class="doc-action-cell">
                                    <button class="btn btn-ghost btn-sm btn-icon" title="Chỉnh sửa"
                                        onclick="openDoctorModal(JSON.parse(decodeURIComponent('${json}')))">
                                        <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button class="btn btn-ghost btn-sm btn-icon" title="Xóa" style="color:var(--c-red)"
                                        onclick="openDeleteModal(${d.doctor_id}, '${escapeJs(d.full_name)}')">
                                        <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
            }).join('');
        }

        // ── Doctor modal open / close ──────────────────────
        function openDoctorModal(doc = null) {
            const isEdit = !!doc;
            document.getElementById('doc-modal-title').textContent = isEdit ? 'Chỉnh sửa bác sĩ' : 'Thêm bác sĩ mới';
            document.getElementById('doc-submit-label').textContent = isEdit ? 'Lưu thay đổi' : 'Thêm bác sĩ';

            // clear validation
            document.querySelectorAll('#doctor-form .field').forEach(f => f.classList.remove('has-err'));

            // fill form
            document.getElementById('f-id').value = doc?.doctor_id ?? '';
            document.getElementById('f-full-name').value = doc?.full_name ?? '';
            document.getElementById('f-user-id').value = doc?.user_id ?? '';
            document.getElementById('f-department-id').value = doc?.department_id ?? '';
            document.getElementById('f-experience').value = doc?.experience ?? '';
            document.getElementById('f-price').value = doc?.price ?? '';
            document.getElementById('f-avatar-url').value = doc?.avatar_url ?? '';
            document.getElementById('f-bio').value = doc?.bio ?? '';
            document.getElementById('f-status').value = doc?.status ?? 1;

            document.getElementById('doctor-modal').style.display = 'flex';
            setTimeout(() => document.getElementById('f-full-name').focus(), 80);
        }

        function closeDoctorModal() { document.getElementById('doctor-modal').style.display = 'none'; }

        // ── Submit add / edit ──────────────────────────────
        async function submitDoctorForm(e) {
            e.preventDefault();
            let ok = true;

            function validate(inputId, condition) {
                const inp = document.getElementById(inputId);
                const field = inp?.closest('.field');
                const pass = condition(inp?.value?.trim() ?? '');
                field?.classList.toggle('has-err', !pass);
                if (!pass) ok = false;
                return inp?.value?.trim();
            }

            const full_name = validate('f-full-name', v => v.length > 0);
            const user_id = validate('f-user-id', v => v > 0);
            const department_id = validate('f-department-id', v => v !== '');
            if (!ok) return;

            const doctorId = document.getElementById('f-id').value;
            const payload = {
                full_name,
                user_id: parseInt(user_id),
                department_id: parseInt(department_id),
                experience: parseInt(document.getElementById('f-experience').value) || 0,
                price: parseFloat(document.getElementById('f-price').value) || 0,
                avatar_url: document.getElementById('f-avatar-url').value.trim() || null,
                bio: document.getElementById('f-bio').value.trim() || null,
                status: parseInt(document.getElementById('f-status').value),
            };

            const btn = document.getElementById('doc-submit-btn');
            btn.disabled = true;

            const isEdit = !!doctorId;
            const data = await api(isEdit ? 'PUT' : 'POST', isEdit ? `/doctors/${doctorId}` : '/doctors', payload);

            btn.disabled = false;
            toast(data.message, data.success ? 'success' : 'error');

            if (data.success) {
                closeDoctorModal();
                loadDoctors(docPage);
                syncDropdown(data.doctor, isEdit);
            }
        }

        // keep the filter dropdown in sync
        function syncDropdown(doc, isEdit) {
            if (!doc) return;
            const sel = document.getElementById('doctor-select');
            if (!sel) return;
            const existing = sel.querySelector(`option[value="${doc.doctor_id}"]`);
            if (isEdit && existing) { existing.textContent = doc.full_name; return; }
            if (!isEdit && !existing) {
                const opt = Object.assign(document.createElement('option'), { value: doc.doctor_id, textContent: doc.full_name });
                sel.appendChild(opt);
            }
        }

        // ── Delete doctor ──────────────────────────────────
        function openDeleteModal(id, name) {
            deleteTargetId = id;
            document.getElementById('del-name').textContent = name;
            document.getElementById('delete-modal').style.display = 'flex';
        }
        function closeDeleteModal() { document.getElementById('delete-modal').style.display = 'none'; deleteTargetId = null; }

        async function confirmDelete() {
            if (!deleteTargetId) return;
            const btn = document.getElementById('del-confirm-btn');
            btn.disabled = true; btn.textContent = 'Đang xóa...';

            const data = await api('DELETE', `/doctors/${deleteTargetId}`);
            btn.disabled = false; btn.textContent = 'Xóa vĩnh viễn';
            toast(data.message, data.success ? 'success' : 'error');

            if (data.success) {
                closeDeleteModal();
                loadDoctors(docPage > 1 && docMeta.total - 1 <= (docPage - 1) * 10 ? docPage - 1 : docPage);
                // remove from filter dropdown
                document.querySelector(`#doctor-select option[value="${deleteTargetId}"]`)?.remove();
            }
        }
    </script>
@endpush