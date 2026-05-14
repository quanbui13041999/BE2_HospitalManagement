{{-- resources/views/doctor/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Bác sĩ – MediBook')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');

    :root {
        --c-bg:       #f0f4f8;
        --c-surface:  #ffffff;
        --c-border:   #e2e8f0;
        --c-text:     #1a202c;
        --c-muted:    #718096;
        --c-blue:     #2563eb;
        --c-green:    #059669;
        --c-yellow:   #d97706;
        --c-red:      #dc2626;
        --c-purple:   #7c3aed;
        --c-teal:     #0d9488;
        --radius:     12px;
        --shadow:     0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);
        --shadow-md:  0 4px 16px rgba(0,0,0,.12);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Be Vietnam Pro', sans-serif; background: var(--c-bg); color: var(--c-text); }

    /* ── Layout ── */
    .dash-grid { display: grid; gap: 24px; }

    /* ── Cards ── */
    .card {
        background: var(--c-surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 24px;
    }
    .card-title {
        font-size: 1rem; font-weight: 700;
        margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }

    /* ── Stat cards ── */
    .stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .stat-card {
        background: var(--c-surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 20px;
        display: flex; flex-direction: column; gap: 6px;
        border-top: 3px solid transparent;
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .stat-card.blue   { border-color: var(--c-blue); }
    .stat-card.green  { border-color: var(--c-green); }
    .stat-card.yellow { border-color: var(--c-yellow); }
    .stat-card.purple { border-color: var(--c-purple); }
    .stat-card.teal   { border-color: var(--c-teal); }
    .stat-card.red    { border-color: var(--c-red); }
    .stat-label { font-size: .75rem; color: var(--c-muted); font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
    .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
    .stat-value.blue   { color: var(--c-blue); }
    .stat-value.green  { color: var(--c-green); }
    .stat-value.yellow { color: var(--c-yellow); }
    .stat-value.purple { color: var(--c-purple); }
    .stat-value.teal   { color: var(--c-teal); }
    .stat-value.red    { color: var(--c-red); }

    /* ── Appointment rows ── */
    .apt-row {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 12px; padding: 14px 16px;
        border-radius: 10px; border: 1px solid var(--c-border);
        transition: background .15s;
    }
    .apt-row:hover { background: #f7fafc; }
    .apt-row + .apt-row { margin-top: 10px; }
    .apt-queue {
        min-width: 38px; height: 38px; border-radius: 50%;
        background: #dbeafe; color: var(--c-blue);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .9rem; flex-shrink: 0;
    }
    .apt-info { flex: 1; min-width: 0; }
    .apt-name  { font-weight: 600; font-size: .95rem; }
    .apt-sub   { font-size: .8rem; color: var(--c-muted); margin-top: 2px; }
    .apt-meta  { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 6px; font-size: .78rem; color: var(--c-muted); }
    .apt-meta span { display: flex; align-items: center; gap: 4px; }
    .apt-actions { display: flex; gap: 8px; flex-shrink: 0; }

    /* ── Buttons ── */
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px; font-size: .82rem; font-weight: 600;
        border: none; cursor: pointer; transition: opacity .15s, transform .1s;
        font-family: inherit;
    }
    .btn:active { transform: scale(.97); }
    .btn:disabled { opacity: .5; cursor: not-allowed; }
    .btn-green  { background: var(--c-green); color: #fff; }
    .btn-green:hover  { background: #047857; }
    .btn-red    { background: var(--c-red); color: #fff; }
    .btn-red:hover    { background: #b91c1c; }
    .btn-outline { background: transparent; color: var(--c-blue); border: 1.5px solid var(--c-blue); }
    .btn-outline:hover { background: #eff6ff; }
    .btn-ghost  { background: transparent; color: var(--c-muted); border: 1.5px solid var(--c-border); }
    .btn-ghost:hover  { background: #f1f5f9; color: var(--c-text); }
    .btn-sm { padding: 5px 10px; font-size: .78rem; }

    /* ── Status badge ── */
    .badge {
        display: inline-block; padding: 2px 10px; border-radius: 100px;
        font-size: .72rem; font-weight: 600;
    }
    .badge-warning  { background: #fef9c3; color: #854d0e; }
    .badge-info     { background: #dbeafe; color: #1e40af; }
    .badge-success  { background: #d1fae5; color: #065f46; }
    .badge-danger   { background: #fee2e2; color: #991b1b; }
    .badge-primary  { background: #ede9fe; color: #5b21b6; }
    .badge-secondary{ background: #f1f5f9; color: #475569; }

    /* ── Review cards ── */
    .review-card {
        border: 1px solid var(--c-border); border-radius: 10px;
        padding: 16px; transition: box-shadow .15s;
    }
    .review-card:hover { box-shadow: var(--shadow); }
    .review-card + .review-card { margin-top: 12px; }
    .review-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .review-patient { display: flex; align-items: center; gap: 10px; }
    .review-avatar  { width: 36px; height: 36px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--c-muted); font-size: .85rem; overflow: hidden; flex-shrink: 0; }
    .review-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .review-name { font-weight: 600; font-size: .9rem; }
    .review-date { font-size: .75rem; color: var(--c-muted); }
    .stars { color: #f59e0b; font-size: .95rem; letter-spacing: 1px; }
    .review-comment { margin: 10px 0; font-size: .88rem; line-height: 1.55; color: #374151; }
    .reply-box {
        background: #f8fafc; border-left: 3px solid var(--c-teal);
        padding: 10px 14px; border-radius: 0 8px 8px 0; margin-top: 10px;
        font-size: .84rem;
    }
    .reply-label { font-size: .72rem; font-weight: 700; color: var(--c-teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
    .reply-form { margin-top: 10px; }
    .reply-form textarea {
        width: 100%; padding: 10px 12px; border: 1.5px solid var(--c-border);
        border-radius: 8px; font-size: .85rem; font-family: inherit;
        resize: vertical; min-height: 80px; transition: border-color .15s;
    }
    .reply-form textarea:focus { outline: none; border-color: var(--c-teal); }
    .reply-form-actions { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }

    /* ── Tabs ── */
    .tabs { display: flex; gap: 4px; border-bottom: 2px solid var(--c-border); margin-bottom: 20px; }
    .tab-btn {
        padding: 8px 16px; font-size: .85rem; font-weight: 600;
        color: var(--c-muted); background: none; border: none; cursor: pointer;
        border-bottom: 2px solid transparent; margin-bottom: -2px;
        transition: color .15s; font-family: inherit;
    }
    .tab-btn.active { color: var(--c-blue); border-bottom-color: var(--c-blue); }
    .tab-btn:hover:not(.active) { color: var(--c-text); }

    /* ── Doctor selector (admin) ── */
    .doctor-filter {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 20px; background: #eff6ff; border-radius: var(--radius);
        margin-bottom: 24px; border: 1px solid #bfdbfe;
    }
    .doctor-filter label { font-size: .85rem; font-weight: 600; color: #1d4ed8; white-space: nowrap; }
    .doctor-filter select {
        flex: 1; max-width: 340px; padding: 8px 12px;
        border: 1.5px solid #93c5fd; border-radius: 8px;
        font-size: .88rem; font-family: inherit; background: #fff;
    }
    .doctor-filter select:focus { outline: none; border-color: var(--c-blue); }

    /* ── Empty state ── */
    .empty { text-align: center; padding: 40px 20px; color: var(--c-muted); font-size: .9rem; }
    .empty svg { width: 48px; height: 48px; margin: 0 auto 12px; opacity: .3; }

    /* ── Pagination ── */
    .pagination { display: flex; justify-content: center; gap: 6px; margin-top: 20px; }
    .page-btn {
        min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px;
        border: 1.5px solid var(--c-border); background: #fff;
        font-size: .82rem; font-weight: 600; cursor: pointer;
        transition: background .15s, border-color .15s; font-family: inherit;
    }
    .page-btn.active  { background: var(--c-blue); color: #fff; border-color: var(--c-blue); }
    .page-btn:hover:not(.active) { background: #f1f5f9; }
    .page-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* ── Toast ── */
    #toast-container { position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
    .toast {
        min-width: 260px; max-width: 380px;
        padding: 14px 18px; border-radius: 10px; background: #fff;
        box-shadow: 0 8px 24px rgba(0,0,0,.14);
        display: flex; align-items: flex-start; gap: 10px;
        animation: toastIn .3s ease;
        border-left: 4px solid transparent;
    }
    .toast.success { border-left-color: var(--c-green); }
    .toast.error   { border-left-color: var(--c-red); }
    .toast-msg { font-size: .85rem; font-weight: 500; line-height: 1.4; }
    @keyframes toastIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* ── Cancel modal ── */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.4);
        display: flex; align-items: center; justify-content: center;
        z-index: 9000; padding: 20px; animation: fadeIn .2s ease;
    }
    .modal-box { background: #fff; border-radius: var(--radius); padding: 28px; max-width: 440px; width: 100%; box-shadow: var(--shadow-md); }
    .modal-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 12px; }
    .modal-box textarea {
        width: 100%; padding: 10px 12px; border: 1.5px solid var(--c-border);
        border-radius: 8px; font-size: .88rem; font-family: inherit;
        resize: none; height: 90px; margin-top: 8px;
    }
    .modal-box textarea:focus { outline: none; border-color: var(--c-red); }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* ── Loading skeleton ── */
    .skeleton { background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 6px; }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    @media (max-width: 640px) {
        .apt-row { flex-direction: column; }
        .apt-actions { width: 100%; }
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-1">Dashboard Bác sĩ</h1>
        <p style="color:var(--c-muted);font-size:.9rem">
            @if($isAdmin)
                <span style="background:#fee2e2;color:#991b1b;padding:2px 10px;border-radius:100px;font-size:.75rem;font-weight:700;">ADMIN</span>
                Toàn quyền quản lý
            @else
                Xin chào, <strong>{{ $currentDoctor->full_name }}</strong>
            @endif
        </p>
    </div>

    {{-- Admin: chọn bác sĩ --}}
    @if($isAdmin)
    <div class="doctor-filter">
        <svg style="width:20px;height:20px;color:#2563eb;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <label for="doctor-select">Xem theo bác sĩ:</label>
        <select id="doctor-select" onchange="onDoctorChange()">
            <option value="">— Tất cả bác sĩ —</option>
            @foreach($doctors as $doc)
                <option value="{{ $doc->doctor_id }}">
                    {{ $doc->full_name }}
                    @if($doc->department) — {{ $doc->department->name }} @endif
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Stats row --}}
    <div class="stats-row mb-8" id="stats-row">
        <div class="stat-card blue">
            <span class="stat-label">Hôm nay</span>
            <span class="stat-value blue" id="s-today">—</span>
        </div>
        <div class="stat-card green">
            <span class="stat-label">Sắp tới</span>
            <span class="stat-value green" id="s-upcoming">—</span>
        </div>
        <div class="stat-card teal">
            <span class="stat-label">Hoàn thành / tháng</span>
            <span class="stat-value teal" id="s-completed">—</span>
        </div>
        <div class="stat-card yellow">
            <span class="stat-label">Đánh giá TB</span>
            <span class="stat-value yellow" id="s-rating">—</span>
        </div>
        <div class="stat-card purple">
            <span class="stat-label">Tổng đánh giá</span>
            <span class="stat-value purple" id="s-reviews">—</span>
        </div>
        <div class="stat-card red">
            <span class="stat-label">Chờ phản hồi</span>
            <span class="stat-value red" id="s-pending">—</span>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs">
        <button class="tab-btn active" data-tab="today"    onclick="switchTab('today')">📅 Hôm nay</button>
        <button class="tab-btn"        data-tab="upcoming" onclick="switchTab('upcoming')">⏰ Sắp tới</button>
        <button class="tab-btn"        data-tab="reviews"  onclick="switchTab('reviews')">⭐ Đánh giá</button>
    </div>

    {{-- Tab: Today --}}
    <div id="tab-today">
        <div class="card">
            <div class="card-title">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Lịch hẹn hôm nay
            </div>
            <div id="today-list"></div>
        </div>
    </div>

    {{-- Tab: Upcoming --}}
    <div id="tab-upcoming" style="display:none">
        <div class="card">
            <div class="card-title">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Lịch hẹn sắp tới
            </div>
            <div id="upcoming-list"></div>
        </div>
    </div>

    {{-- Tab: Reviews --}}
    <div id="tab-reviews" style="display:none">
        <div class="card">
            <div class="card-title">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                Đánh giá của bệnh nhân
            </div>
            <div id="reviews-list"></div>
            <div id="reviews-pagination" class="pagination"></div>
        </div>
    </div>

</div>

{{-- Cancel modal --}}
<div id="cancel-modal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeCancelModal()">
    <div class="modal-box">
        <div class="modal-title">❌ Hủy lịch hẹn</div>
        <p style="font-size:.85rem;color:var(--c-muted)">Hệ thống sẽ gửi email gợi ý lịch mới cho bệnh nhân.</p>
        <label style="font-size:.82rem;font-weight:600;display:block;margin-top:14px">Lý do hủy (tùy chọn)</label>
        <textarea id="cancel-reason" placeholder="VD: Bác sĩ bận công việc đột xuất..."></textarea>
        <div class="modal-actions">
            <button class="btn btn-ghost" onclick="closeCancelModal()">Quay lại</button>
            <button class="btn btn-red"   onclick="confirmCancel()">Xác nhận hủy</button>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container"></div>
@endsection

@push('scripts')
<script>
// ── Config ────────────────────────────────────────────────────────────
const IS_ADMIN  = @json($isAdmin);
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BASE_URL  = '/doctor/dashboard';

// ── State ─────────────────────────────────────────────────────────────
let currentDoctorId  = IS_ADMIN ? '' : @json($currentDoctor?->doctor_id ?? 0);
let cancelTargetId   = null;
let reviewPage       = 1;
let reviewMeta       = {};

// ── Init ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadAll();
});

function loadAll() {
    loadStats();
    loadToday();
    // upcoming & reviews load lazily on tab switch
}

function onDoctorChange() {
    currentDoctorId = document.getElementById('doctor-select')?.value ?? '';
    reviewPage = 1;
    loadAll();
    // re-load active tab if it's not "today"
    const active = document.querySelector('.tab-btn.active')?.dataset.tab;
    if (active === 'upcoming') loadUpcoming();
    if (active === 'reviews')  loadReviews();
}

// ── Tab switching ──────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
    ['today','upcoming','reviews'].forEach(t => {
        document.getElementById(`tab-${t}`).style.display = t === name ? '' : 'none';
    });
    if (name === 'upcoming') loadUpcoming();
    if (name === 'reviews')  loadReviews();
}

// ── Helpers ────────────────────────────────────────────────────────────
function qs(extraParams = {}) {
    const p = {};
    if (IS_ADMIN && currentDoctorId) p.doctor_id = currentDoctorId;
    Object.assign(p, extraParams);
    const str = new URLSearchParams(p).toString();
    return str ? '?' + str : '';
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

function renderSkeleton(lines = 3) {
    return Array.from({length: lines}, () =>
        `<div style="height:80px;border-radius:10px;margin-bottom:10px" class="skeleton"></div>`
    ).join('');
}

function statusBadge(s) {
    const map = {
        'Chờ xác nhận': 'warning', 'Đã xác nhận': 'info',
        'Đang khám': 'primary', 'Hoàn thành': 'success',
        'Đã hủy': 'danger', 'Dời lịch': 'secondary'
    };
    return `<span class="badge badge-${map[s] ?? 'secondary'}">${s}</span>`;
}

function stars(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function initials(name = '') {
    return name.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
}

// ── Stats ──────────────────────────────────────────────────────────────
async function loadStats() {
    const data = await api('GET', '/stats' + qs());
    if (!data.success) return;
    const d = data.data;
    document.getElementById('s-today').textContent     = d.today;
    document.getElementById('s-upcoming').textContent  = d.upcoming;
    document.getElementById('s-completed').textContent = d.completed_month;
    document.getElementById('s-rating').textContent    = d.avg_rating ? `${d.avg_rating} ⭐` : '—';
    document.getElementById('s-reviews').textContent   = d.total_reviews;
    document.getElementById('s-pending').textContent   = d.pending_replies;
}

// ── Today ──────────────────────────────────────────────────────────────
async function loadToday() {
    const el = document.getElementById('today-list');
    el.innerHTML = renderSkeleton(2);
    const data = await api('GET', '/appointments/today' + qs());
    if (!data.success) { el.innerHTML = '<div class="empty">Không thể tải dữ liệu.</div>'; return; }
    renderAppointments(el, data.data, true);
}

async function loadUpcoming() {
    const el = document.getElementById('upcoming-list');
    el.innerHTML = renderSkeleton(3);
    const data = await api('GET', '/appointments/upcoming' + qs());
    if (!data.success) { el.innerHTML = '<div class="empty">Không thể tải dữ liệu.</div>'; return; }
    renderAppointments(el, data.data, false);
}

function renderAppointments(el, list, isToday) {
    if (!list.length) {
        el.innerHTML = `
            <div class="empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Không có lịch hẹn nào
            </div>`;
        return;
    }
    el.innerHTML = list.map((a, i) => {
        const timeStr = isToday
            ? `<span>🕐 ${a.appointment_time?.split(' ')[1] ?? ''} · ${a.slot_duration} phút</span>`
            : `<span>📅 ${a.appointment_time}</span>`;

        const canComplete = isToday && ['Chờ xác nhận','Đã xác nhận','Đang khám'].includes(a.status);
        const canCancel   = ['Chờ xác nhận','Đã xác nhận','Đang khám'].includes(a.status);

        return `
        <div class="apt-row" id="apt-${a.id}">
            <div class="apt-queue">${a.queue_number ?? (i + 1)}</div>
            <div class="apt-info">
                <div class="apt-name">${a.patient_name ?? '—'} ${statusBadge(a.status)}</div>
                <div class="apt-sub">${a.service_name ?? ''} ${IS_ADMIN && a.doctor_name ? '· ' + a.doctor_name : ''}</div>
                <div class="apt-meta">
                    ${timeStr}
                    ${a.patient_phone ? `<span>📞 ${a.patient_phone}</span>` : ''}
                    ${a.note ? `<span title="${a.note}">📝 Ghi chú</span>` : ''}
                </div>
            </div>
            <div class="apt-actions">
                ${canComplete ? `<button class="btn btn-green btn-sm" onclick="doComplete(${a.id})">✓ Hoàn thành</button>` : ''}
                ${canCancel   ? `<button class="btn btn-red btn-sm"   onclick="openCancelModal(${a.id})">✕ Hủy</button>` : ''}
            </div>
        </div>`;
    }).join('');
}

// ── Reviews ────────────────────────────────────────────────────────────
async function loadReviews(page = 1) {
    reviewPage = page;
    const el = document.getElementById('reviews-list');
    el.innerHTML = renderSkeleton(4);

    const data = await api('GET', '/reviews' + qs({ page, per_page: 8 }));
    if (!data.success) { el.innerHTML = '<div class="empty">Không thể tải dữ liệu.</div>'; return; }

    reviewMeta = data.meta;
    renderReviews(el, data.data);
    renderPagination();
}

function renderReviews(el, list) {
    if (!list.length) {
        el.innerHTML = `<div class="empty">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            Chưa có đánh giá nào
        </div>`;
        return;
    }

    el.innerHTML = list.map(r => {
        const hasReply = !!r.doctor_reply;
        const avatarHtml = r.patient_avatar
            ? `<img src="/storage/${r.patient_avatar}" alt="">`
            : initials(r.patient_name);

        return `
        <div class="review-card" id="rv-${r.id}">
            <div class="review-header">
                <div class="review-patient">
                    <div class="review-avatar">${avatarHtml}</div>
                    <div>
                        <div class="review-name">${r.patient_name}</div>
                        <div class="review-date">${r.created_at} ${IS_ADMIN && r.doctor_name ? '· ' + r.doctor_name : ''}</div>
                    </div>
                </div>
                <span class="stars">${stars(r.rating)} (${r.rating}/5)</span>
            </div>
            <div class="review-comment">${r.comment ?? '<em style="color:var(--c-muted)">Không có bình luận</em>'}</div>

            ${hasReply ? `
                <div class="reply-box" id="reply-display-${r.id}">
                    <div class="reply-label">💬 Phản hồi của bác sĩ
                        <span style="font-weight:400;color:var(--c-muted)">${r.doctor_reply_updated_at ?? ''}</span>
                    </div>
                    <div id="reply-text-${r.id}">${r.doctor_reply}</div>
                    <div style="margin-top:8px;display:flex;gap:8px">
                        <button class="btn btn-ghost btn-sm" onclick="showReplyForm(${r.id}, '${escapeJs(r.doctor_reply)}')">✏️ Sửa</button>
                        ${IS_ADMIN ? `<button class="btn btn-ghost btn-sm" style="color:var(--c-red)" onclick="doDeleteReply(${r.id})">🗑 Xóa</button>` : ''}
                    </div>
                </div>` : ''}

            <div id="reply-form-${r.id}" class="reply-form" style="${hasReply ? 'display:none' : ''}">
                <textarea id="reply-input-${r.id}" placeholder="Nhập phản hồi của bạn...">${hasReply ? r.doctor_reply : ''}</textarea>
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
    const form = document.getElementById(`reply-form-${id}`);
    const display = document.getElementById(`reply-display-${id}`);
    const input = document.getElementById(`reply-input-${id}`);
    if (form)    form.style.display    = '';
    if (display) display.style.display = 'none';
    if (input && existing) input.value = existing;
    input?.focus();
}

function hideReplyForm(id) {
    const form    = document.getElementById(`reply-form-${id}`);
    const display = document.getElementById(`reply-display-${id}`);
    if (form)    form.style.display    = 'none';
    if (display) display.style.display = '';
}

function renderPagination() {
    const el = document.getElementById('reviews-pagination');
    if (!reviewMeta.last_page || reviewMeta.last_page <= 1) { el.innerHTML = ''; return; }
    let html = '';
    html += `<button class="page-btn" onclick="loadReviews(${reviewPage - 1})" ${reviewPage <= 1 ? 'disabled' : ''}>‹</button>`;
    for (let p = 1; p <= reviewMeta.last_page; p++) {
        html += `<button class="page-btn ${p === reviewPage ? 'active' : ''}" onclick="loadReviews(${p})">${p}</button>`;
    }
    html += `<button class="page-btn" onclick="loadReviews(${reviewPage + 1})" ${reviewPage >= reviewMeta.last_page ? 'disabled' : ''}>›</button>`;
    el.innerHTML = html;
}

// ── Actions ────────────────────────────────────────────────────────────
async function doComplete(id) {
    const btn = event.target;
    btn.disabled = true; btn.textContent = '...';
    const data = await api('PATCH', `/appointments/${id}/complete`);
    toast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        loadStats();
        loadToday();
    } else btn.disabled = false;
}

function openCancelModal(id) {
    cancelTargetId = id;
    document.getElementById('cancel-reason').value = '';
    document.getElementById('cancel-modal').style.display = 'flex';
}
function closeCancelModal() {
    document.getElementById('cancel-modal').style.display = 'none';
    cancelTargetId = null;
}

async function confirmCancel() {
    if (!cancelTargetId) return;
    const reason = document.getElementById('cancel-reason').value.trim();
    const data = await api('PATCH', `/appointments/${cancelTargetId}/cancel`, { reason });
    toast(data.message, data.success ? 'success' : 'error');
    closeCancelModal();
    if (data.success) {
        loadStats();
        loadToday();
        loadUpcoming();
    }
}

async function doReply(id) {
    const input = document.getElementById(`reply-input-${id}`);
    const reply = input?.value?.trim();
    if (!reply) { toast('Vui lòng nhập nội dung phản hồi.', 'error'); return; }

    const data = await api('POST', `/reviews/${id}/reply`, { reply });
    toast(data.message, data.success ? 'success' : 'error');
    if (data.success) {
        loadStats();
        loadReviews(reviewPage);
    }
}

async function doDeleteReply(id) {
    if (!confirm('Xóa phản hồi này?')) return;
    const data = await api('DELETE', `/reviews/${id}/reply`);
    toast(data.message, data.success ? 'success' : 'error');
    if (data.success) { loadStats(); loadReviews(reviewPage); }
}

// ── Toast ──────────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    const icon = type === 'success' ? '✅' : '❌';
    el.innerHTML = `<span>${icon}</span><span class="toast-msg">${msg}</span>`;
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

function escapeJs(s = '') {
    return s.replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\n/g,'\\n');
}
</script>
@endpush