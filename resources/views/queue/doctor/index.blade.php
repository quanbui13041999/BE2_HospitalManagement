@extends('layouts.app')

@section('title', 'Hàng đợi khám của tôi')

@push('styles')
<style>
    .ticket-row { transition: all 0.3s ease; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-b">
        <div>
            <h1 class="fw-black text-gray-900 text-3xl tracking-tight mb-1">
                <i class="bi bi-person-workspace text-primary me-2"></i>Bảng Điểu Khiển Khám Bệnh
            </h1>
            <p class="text-secondary mb-0">Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }} • Theo dõi sát hàng đợi và gọi số khám bệnh</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary rounded-pill px-4 py-2 font-bold shadow-sm">
                Bác sĩ: {{ auth()->user()->full_name }}
            </span>
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm border-0 border-start border-info border-4 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4 text-info"></i>
                <div>
                    {{ session('info') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @forelse($schedules as $s)
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" 
             x-data="doctorSchedule({{ $s->schedule_id }})" 
             x-init="init()">
            
            {{-- Header ca khám --}}
            <div class="card-header bg-gradient bg-primary text-white border-0 py-4 px-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1 fs-5">
                        <i class="bi bi-clock-fill me-2 text-white/80"></i>
                        Thời gian trực: {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                    </h3>
                    <p class="text-blue-100 text-sm mb-0">
                        Phòng: <strong class="text-white">{{ $s->room->room_code ?? '—' }}</strong> • 
                        Tầng: <strong class="text-white">{{ $s->room->floor ?? '—' }}</strong> • 
                        Bác sĩ: <strong class="text-white">{{ $s->doctor->full_name }}</strong>
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end text-white/90 text-xs font-semibold uppercase tracking-wider me-2">
                        <span x-text="stats.total_waiting">{{ $s->snapshot['stats']['total_waiting'] }}</span> Đang chờ •
                        <span x-text="stats.total_completed">{{ $s->snapshot['stats']['total_completed'] }}</span> Đã xong
                    </div>
                    <form method="POST" :action="'/queue/doctor/schedule/' + scheduleId + '/call-next'">
                        @csrf
                        <button type="submit" class="btn btn-white text-primary font-black px-4 py-2.5 rounded-pill shadow-sm transition-all duration-200 hover:bg-blue-50">
                            📣 GỌI SỐ TIẾP THEO
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-5">
                <div class="row g-5">
                    {{-- Cột Trái: Đang xử lý --}}
                    <div class="col-lg-4">
                        <h4 class="fw-bold text-gray-700 text-base uppercase tracking-wider mb-3">🩺 Bệnh nhân đang gọi / Khám</h4>
                        <div class="card border-0 bg-light rounded-4 p-4 text-center h-100 d-flex flex-col justify-center align-items-center" style="min-height: 300px;">
                            
                            <div x-show="current" class="w-100" x-cloak>
                                <span class="badge align-self-center rounded-pill px-3 py-2 text-xs font-bold uppercase tracking-wider mb-3 shadow-sm"
                                      :class="current?.status === 'calling' ? 'bg-danger text-white' : 'bg-success text-white'"
                                      x-text="current?.status === 'calling' ? '🔔 ĐANG GỌI VÀO PHÒNG' : '🩺 ĐANG TRONG CA KHÁM'">
                                </span>
                                <h2 class="text-7xl font-black text-gray-900 tracking-tight mb-2" x-text="'#' + current?.queue_number"></h2>
                                <h4 class="fw-bold text-gray-800 mb-1" x-text="current?.patient_name"></h4>
                                <p class="text-secondary text-sm mb-4">
                                    Đối tượng: <strong class="text-gray-700" x-text="current?.priority_icon + ' ' + current?.priority_label"></strong>
                                </p>

                                <div x-show="current?.notes" class="bg-white rounded-3 p-3 text-start mb-4 border border-gray-150 shadow-inner">
                                    <small class="d-block text-secondary font-semibold uppercase tracking-wider mb-1">Ghi chú tiếp đón:</small>
                                    <p class="text-gray-700 mb-0 font-medium text-sm" x-text="current?.notes"></p>
                                </div>

                                <div class="d-flex flex-column gap-2 w-100">
                                    <template x-if="current?.status === 'calling'">
                                        <form method="POST" :action="'/queue/doctor/ticket/' + current?.ticket_id + '/start'">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100 rounded-pill py-2.5 font-black shadow">
                                                <i class="bi bi-play-fill me-1"></i> BẮT ĐẦU KHÁM BỆNH
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="current?.status === 'in_progress'">
                                        <form method="POST" :action="'/queue/doctor/ticket/' + current?.ticket_id + '/complete'">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 font-black shadow">
                                                <i class="bi bi-check2-circle me-1"></i> HOÀN THÀNH CA KHÁM
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </div>

                            <div x-show="!current" class="text-center text-gray-400" x-cloak>
                                <p class="text-6xl mb-4">🩺</p>
                                <h5 class="fw-bold text-gray-700 mb-1">Hàng đợi đang rảnh</h5>
                                <p class="text-xs text-gray-400">Ấn "Gọi số tiếp theo" để tiếp đón bệnh nhân</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cột Phải: Danh sách chờ tiếp đón --}}
                    <div class="col-lg-8">
                        <h4 class="fw-bold text-gray-700 text-base uppercase tracking-wider mb-3">📋 Hàng đợi bệnh nhân đang chờ</h4>
                        <div class="card border border-gray-150 rounded-4 overflow-hidden">
                            <div class="list-group list-group-flush divide-y" style="max-height: 380px; overflow-y: auto;">
                                <template x-for="(ticket, index) in waiting" :key="ticket.ticket_id">
                                    <div class="list-group-item border-0 p-4 d-flex align-items-center gap-3 ticket-row bg-transparent hover-bg-light transition-all duration-200"
                                         :class="index === 0 ? 'bg-amber-50/40 border-l border-amber-300' : ''">
                                        
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
                                                <span>Ước tính chờ: <strong class="text-amber-500" x-text="ticket.est_wait_minutes + ' phút'"></strong></span>
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

                                        {{-- Kế tiếp --}}
                                        <div x-show="index === 0" class="flex-shrink-0">
                                            <span class="badge bg-amber-400 text-gray-900 px-3 py-2 rounded-pill font-bold text-xs uppercase tracking-wider animate-pulse">Kế Tiếp</span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="waiting.length === 0" class="text-center py-5 text-gray-400">
                                    <p class="text-5xl mb-3">✅</p>
                                    <p class="font-bold text-gray-600 mb-0">Hàng đợi đang trống!</p>
                                    <p class="text-xs text-gray-400">Không có bệnh nhân chờ trực tiếp</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="p-4 bg-gray-50 rounded-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-calendar-x fs-1 text-secondary"></i>
            </div>
            <h4 class="fw-bold text-gray-700">Không tìm thấy ca trực khám bệnh hôm nay</h4>
            <p class="text-secondary">Hôm nay bạn không có lịch trực hoặc lịch trực chưa được chuyển sang trạng thái "Hoạt động".</p>
        </div>
    @endforelse
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
function doctorSchedule(scheduleId) {
    return {
        scheduleId: scheduleId,
        current: null,
        waiting: [],
        stats: { total_waiting: 0, total_completed: 0, total_today: 0 },

        init() {
            this.refresh();

            // Pusher Realtime Listener
            try {
                const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
                    cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}',
                    forceTLS: true
                });
                const channel = pusher.subscribe(`queue.${this.scheduleId}`);
                channel.bind('queue.updated', () => this.refresh());
                channel.bind('ticket.called', () => this.refresh());
            } catch (e) {
                console.warn("Pusher configuration missing, doctor queue listening via polling.", e);
            }

            // Polling backup
            setInterval(() => this.refresh(), 8000);
        },

        async refresh() {
            try {
                const res = await fetch(`/queue/doctor/api/${this.scheduleId}/snapshot`);
                if (res.ok) {
                    const data = await res.json();
                    this.current = data.current;
                    this.waiting = data.waiting.map(t => ({
                        ...t,
                        priority_label: this.getPriorityLabel(t.priority),
                        priority_icon: this.getPriorityIcon(t.priority),
                    }));
                    this.stats = data.stats;
                }
            } catch (e) {
                console.error("Failed to load doctor schedule snapshots:", e);
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
