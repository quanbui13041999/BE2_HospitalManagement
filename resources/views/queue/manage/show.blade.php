@extends('layouts.app')

@section('title', 'Quản lý hàng đợi - ' . $schedule->doctor->full_name)

@push('styles')
<style>
    .ticket-row { transition: all 0.3s ease; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="container py-5" x-data="queueManage()" x-init="init()">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 pb-3 border-b">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 text-xs font-semibold uppercase tracking-wider">
                    <li class="breadcrumb-item"><a href="{{ route('queue.manage.index') }}" class="text-primary text-decoration-none">Hàng Đợi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi Tiết</li>
                </ol>
            </nav>
            <h1 class="fw-black text-gray-900 text-3xl tracking-tight mb-1">
                Bác sĩ: {{ $schedule->doctor->full_name }}
            </h1>
            <p class="text-secondary mb-0">
                Phòng khám: <strong class="text-gray-800">{{ $schedule->room->room_code ?? '—' }}</strong> • 
                {{ $schedule->doctor->department->department_name ?? 'Khoa Khám Bệnh' }} • 
                Giờ làm việc: {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('queue.manage.checkin') }}?schedule_id={{ $schedule->schedule_id }}"
               class="btn btn-primary rounded-pill px-4 shadow">
                <i class="bi bi-person-plus-fill me-2"></i>Check-in Bệnh Nhân
            </a>
            <a href="{{ route('queue.display', $schedule->schedule_id) }}" target="_blank"
               class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-tv me-2"></i>Mở Màn Hình TV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 border-start border-success border-4 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4 text-success"></i>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Thống kê nhanh --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <p class="text-4xl font-black text-warning mb-1" x-text="stats.total_waiting">{{ $snapshot['stats']['total_waiting'] }}</p>
                <small class="text-secondary font-bold uppercase tracking-wider">Đang Chờ Khám</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <p class="text-4xl font-black text-emerald-500 mb-1" x-text="stats.total_completed">{{ $snapshot['stats']['total_completed'] }}</p>
                <small class="text-secondary font-bold uppercase tracking-wider">Đã Khám Xong</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <p class="text-4xl font-black text-primary mb-1" x-text="stats.total_today">{{ $snapshot['stats']['total_today'] }}</p>
                <small class="text-secondary font-bold uppercase tracking-wider">Tổng Hôm Nay</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <p class="text-4xl font-black text-purple-500 mb-1" id="current-time">{{ now()->format('H:i') }}</p>
                <small class="text-secondary font-bold uppercase tracking-wider">Thời Gian Hiện Tại</small>
            </div>
        </div>
    </div>

    <div class="row g-5">
        {{-- PANEL TRÁI: Bệnh nhân hiện tại + Lịch sử gần nhất --}}
        <div class="col-lg-4">
            <h3 class="fw-bold text-gray-800 text-lg uppercase tracking-wider mb-3">🩺 Trạng thái xử lý</h3>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4" id="current-section">
                    {{-- Render động qua Alpine hoặc SSR ban đầu --}}
                    <div x-show="current" x-cloak>
                        <div class="card border-0 bg-light rounded-4 p-4 text-center">
                            <span class="badge align-self-center rounded-pill px-3 py-2 text-xs font-bold uppercase tracking-wider mb-3"
                                  :class="current?.status === 'calling' ? 'bg-danger text-white' : 'bg-success text-white'"
                                  x-text="current?.status === 'calling' ? '🔔 ĐANG GỌI SỐ' : '🩺 ĐANG KHÁM BỆNH'">
                            </span>
                            <h2 class="text-7xl font-black text-gray-900 tracking-tight mb-2" x-text="'#' + current?.queue_number"></h2>
                            <h4 class="fw-bold text-gray-800 mb-1" x-text="current?.patient_name"></h4>
                            <p class="text-secondary text-sm mb-3">
                                <span x-text="current?.priority_icon"></span> <span x-text="current?.priority_label"></span>
                                <span x-show="current?.patient_phone" x-text="' • SĐT: ' + current?.patient_phone"></span>
                            </p>

                            <div x-show="current?.notes" class="bg-white rounded-3 p-3 text-start mb-3 border border-gray-100 shadow-inner">
                                <small class="d-block text-secondary font-semibold uppercase tracking-wider mb-1">Ghi chú:</small>
                                <p class="text-gray-700 mb-0 font-medium text-sm" x-text="current?.notes"></p>
                            </div>

                            <div class="d-flex justify-content-center gap-2">
                                <form method="POST" :action="'/queue/manage/ticket/' + current?.ticket_id + '/skip'" onsubmit="return confirm('Bạn có chắc chắn muốn bỏ qua bệnh nhân này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 font-bold shadow-sm">
                                        <i class="bi bi-x-circle me-1"></i> Bỏ Qua Ca Này
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div x-show="!current" class="text-center text-gray-400 py-5">
                        <p class="text-5xl mb-3">😊</p>
                        <p class="font-bold text-gray-600 mb-0">Chưa bắt đầu gọi số</p>
                        <p class="text-xs text-gray-400">Bác sĩ chưa gọi số tiếp theo</p>
                    </div>
                </div>
            </div>

            {{-- Lịch sử hôm nay --}}
            <h3 class="fw-bold text-gray-800 text-lg uppercase tracking-wider mb-3">📋 Lịch sử hoàn thành</h3>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-light border-0 py-3 px-4">
                    <span class="text-xs text-secondary font-semibold uppercase tracking-wider">Bệnh nhân đã khám / bỏ qua</span>
                </div>
                <div class="list-group list-group-flush divide-y max-h-64 overflow-y-auto" style="max-height: 320px;">
                    @forelse($history as $h)
                        <div class="list-group-item border-0 px-4 py-3 d-flex justify-content-between align-items-center bg-transparent">
                            <div class="min-w-0">
                                <span class="badge bg-secondary rounded-pill me-2">#{{ $h->queue_number }}</span>
                                <strong class="text-gray-800">{{ $h->patient_name }}</strong>
                            </div>
                            <span class="badge rounded-pill text-xs px-3 py-2 font-semibold
                                {{ $h->status === 'completed' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">
                                {{ $h->status_label }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-400 text-sm bg-transparent">
                            Chưa có dữ liệu lịch sử hôm nay
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- PANEL PHẢI: Danh sách chờ khám --}}
        <div class="col-lg-8">
            <h3 class="fw-bold text-gray-800 text-lg uppercase tracking-wider mb-3">📋 Hàng đợi đang chờ khám</h3>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-gradient bg-primary text-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="font-bold text-sm">Danh sách bệnh nhân</span>
                    <span class="badge bg-white/20 text-white rounded-pill px-3 py-1 font-bold text-xs" x-text="waiting.length + ' bệnh nhân'">
                        {{ count($snapshot['waiting']) }} bệnh nhân
                    </span>
                </div>
                
                <div class="list-group list-group-flush divide-y" id="waiting-list">
                    <template x-for="(ticket, index) in waiting" :key="ticket.ticket_id">
                        <div class="list-group-item border-0 p-4 d-flex align-items-center gap-3 ticket-row bg-transparent hover-bg-light transition-all duration-200">
                            
                            {{-- Thứ tự --}}
                            <div class="w-12 h-12 rounded-circle d-flex items-center justify-center font-black text-lg flex-shrink-0 shadow-inner"
                                 :class="{
                                     'bg-danger-subtle text-danger-emphasis border border-danger/20': ticket.priority === 'emergency',
                                     'bg-purple-subtle text-purple-emphasis border border-purple/20': ticket.priority === 'disabled',
                                     'bg-primary-subtle text-primary-emphasis border border-primary/20': ticket.priority === 'elderly',
                                     'bg-light text-secondary border border-gray-200': ticket.priority === 'normal',
                                 }"
                                 x-text="ticket.queue_number">
                            </div>

                            {{-- Thông tin --}}
                            <div class="flex-1 min-w-0">
                                <h5 class="fw-bold text-gray-800 mb-1 truncate" x-text="ticket.patient_name"></h5>
                                <div class="d-flex items-center gap-2 text-secondary text-xs font-semibold">
                                    <span x-text="ticket.priority_icon + ' ' + ticket.priority_label"></span>
                                    <span>•</span>
                                    <span>Check-in: <strong class="text-gray-700" x-text="ticket.checkin_time_formatted"></strong></span>
                                    <span>•</span>
                                    <span>Chờ ~<strong class="text-amber-500" x-text="ticket.est_wait_minutes + ' phút'"></strong></span>
                                </div>
                            </div>

                            {{-- Badge --}}
                            <div x-show="ticket.priority !== 'normal'" class="flex-shrink-0">
                                <span class="badge text-xs px-3 py-2 rounded-pill font-bold"
                                      :class="{
                                          'bg-danger text-white': ticket.priority === 'emergency',
                                          'bg-purple text-white': ticket.priority === 'disabled',
                                          'bg-primary text-white': ticket.priority === 'elderly',
                                      }"
                                      x-text="ticket.priority_label">
                                </span>
                            </div>

                            <div x-show="ticket.payment_required && !ticket.can_start_exam" class="flex-shrink-0">
                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill font-bold text-xs uppercase tracking-wider">
                                    Chưa thanh toán
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex-shrink-0">
                                <form method="POST" :action="'/queue/manage/ticket/' + ticket.ticket_id + '/skip'" onsubmit="return confirm('Bạn có chắc chắn muốn bỏ qua bệnh nhân này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-xs rounded-pill px-3 py-1 font-semibold text-xs">
                                        Bỏ Qua
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>

                    <div x-show="waiting.length === 0" class="text-center py-5 text-gray-400">
                        <p class="text-5xl mb-3">✅</p>
                        <p class="font-bold text-gray-600 mb-0">Hàng đợi đang trống!</p>
                        <p class="text-xs text-gray-400">Tất cả bệnh nhân hôm nay đã được tiếp đón</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
function queueManage() {
    return {
        current: null,
        waiting: [],
        stats: { total_waiting: 0, total_callable: 0, total_completed: 0, total_today: 0 },
        scheduleId: {{ $schedule->schedule_id }},

        init() {
            // Lấy dữ liệu lần đầu
            this.refresh();

            // Đồng hồ giờ hiện tại
            setInterval(() => {
                const now = new Date();
                document.getElementById('current-time').textContent =
                    now.toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'});
            }, 1000);

            // Pusher Realtime
            try {
                const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
                    cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}',
                    forceTLS: true
                });
                const channel = pusher.subscribe(`queue.${this.scheduleId}`);
                channel.bind('queue.updated', () => this.refresh());
                channel.bind('ticket.called', () => this.refresh());
            } catch (e) {
                console.warn("Pusher failed to initialize. Falling back to HTTP Polling.", e);
            }

            // Polling fallback mỗi 8 giây
            setInterval(() => this.refresh(), 8000);
        },

        async refresh() {
            try {
                const res = await fetch(`/queue/manage/api/${this.scheduleId}/snapshot`);
                if (res.ok) {
                    const payload = await res.json();
                    const data = payload.data || payload; // fixed: ho tro JSON wrapper {success,message,data}
                    this.current = data.current;
                    this.waiting = data.waiting.map(t => {
                        const checkin = new Date(t.checkin_time);
                        const formattedTime = checkin.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
                        return {
                            ...t,
                            priority_label: this.getPriorityLabel(t.priority),
                            priority_icon: this.getPriorityIcon(t.priority),
                            checkin_time_formatted: formattedTime
                        };
                    });
                    this.stats   = data.stats;
                }
            } catch (e) {
                console.error("Failed to load queue updates:", e);
            }
        },

        getPriorityLabel(priority) {
            const labels = {
                'normal': 'Thường',
                'elderly': 'Cao tuổi',
                'disabled': 'Khuyết tật',
                'emergency': 'Cấp cứu'
            };
            return labels[priority] || 'Thường';
        },

        getPriorityIcon(priority) {
            const icons = {
                'normal': '👤',
                'elderly': '👴',
                'disabled': '♿',
                'emergency': '🚨'
            };
            return icons[priority] || '👤';
        }
    }
}
</script>
@endpush
@endsection
