@extends('layouts.admin')

@section('title', 'Chi Tiết Hàng Đợi')

@section('content')
<style>
    .queue-number-badge {
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.125rem;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background-color: var(--badge-bg-color, #6b7280);
    }
    
    .badge-priority {
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        color: white;
        background-color: var(--badge-bg-color, #6b7280);
    }
</style>
<div class="container-fluid">
    {{-- ══ HEADER ════════════════════════════════════════════════ --}}
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <a href="{{ route('admin.queue.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i>Quay Lại
            </a>
            <h1 class="fw-black text-gray-900 text-3xl tracking-tight mb-1">
                <i class="bi bi-collection-play-fill text-primary me-2"></i>Chi Tiết Hàng Đợi
            </h1>
            <p class="text-muted mb-0">
                BS. {{ $schedule->doctor->full_name }} • Phòng {{ $schedule->room->room_code ?? '—' }} 
                • {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </p>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary px-4 py-3 rounded-pill fs-6 fw-bold">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->translatedFormat('d/m/Y') }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        {{-- ══ CỘT TRÁI: STATS & CURRENT ═════════════════════════ --}}
        <div class="col-12 col-lg-4">
            {{-- Statistics --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0 py-3 px-4">
                    <h6 class="mb-0 fw-bold">📊 Thống Kê</h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-4 d-flex align-items-center justify-content-between border-bottom">
                        <div>
                            <small class="text-muted font-semibold uppercase d-block mb-1">Đang Chờ</small>
                            <h4 class="fw-bold text-warning mb-0">{{ $snapshot['stats']['total_waiting'] }}</h4>
                        </div>
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                    <div class="p-4 d-flex align-items-center justify-content-between border-bottom">
                        <div>
                            <small class="text-muted font-semibold uppercase d-block mb-1">Đã Xong</small>
                            <h4 class="fw-bold text-success mb-0">{{ $snapshot['stats']['total_completed'] }}</h4>
                        </div>
                        <i class="bi bi-check-circle text-success fs-3"></i>
                    </div>
                    <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted font-semibold uppercase d-block mb-1">Tổng Ngày</small>
                            <h4 class="fw-bold text-primary mb-0">{{ $snapshot['stats']['total_today'] }}</h4>
                        </div>
                        <i class="bi bi-calendar3 text-primary fs-3"></i>
                    </div>
                </div>
            </div>

            {{-- Current Patient --}}
            <div class="card border-0 shadow-sm {{ $snapshot['current'] ? 'border-start border-warning border-4' : '' }}">
                <div class="card-header bg-gradient {{ $snapshot['current'] ? 'bg-warning' : 'bg-light' }} border-0 py-3 px-4 text-white-unless-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-megaphone-fill me-2"></i>
                        {{ $snapshot['current'] ? 'Đang Gọi / Khám' : 'Không Có Bệnh Nhân' }}
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    @if($snapshot['current'])
                    <div class="mb-3">
                        <div class="display-4 fw-black text-primary mb-2">
                            #{{ $snapshot['current']['queue_number'] }}
                        </div>
                        <h4 class="fw-bold text-gray-800 mb-1">{{ $snapshot['current']['patient_name'] }}</h4>
                        <p class="text-muted mb-2">
                            <i class="bi bi-telephone me-1"></i>{{ $snapshot['current']['patient_phone'] ?? '—' }}
                        </p>
                    </div>
                    <div class="alert alert-info-subtle border-0 mb-3 p-3 rounded-3">
                        <span class="badge {{ $snapshot['current']['status'] === 'calling' ? 'bg-danger' : 'bg-success' }} text-white me-2">
                            {{ $snapshot['current']['status'] === 'calling' ? '🔔 ĐANG GỌI' : '🩺 ĐANG KHÁM' }}
                        </span>
                        <br><small class="text-muted mt-2 d-block">Đối tượng: {{ $snapshot['current']['priority_icon'] }} {{ $snapshot['current']['priority_label'] }}</small>
                    </div>
                    
                    {{-- Action Buttons based on Role --}}
                    @if($userRole === 2) {{-- Doctor --}}
                    <div class="d-flex flex-column gap-2">
                        @if($snapshot['current']['status'] === 'calling')
                        <form method="POST" action="{{ route('queue.doctor.start', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-3 py-2.5 fw-bold">
                                <i class="bi bi-play-fill me-2"></i>BẮT ĐẦU KHÁM
                            </button>
                        </form>
                        @elseif($snapshot['current']['status'] === 'in_progress')
                        <form method="POST" action="{{ route('queue.doctor.complete', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 rounded-3 py-2.5 fw-bold">
                                <i class="bi bi-check2-circle me-2"></i>HOÀN THÀNH
                            </button>
                        </form>
                        @endif
                    </div>
                    @elseif($userRole === 4) {{-- Receptionist --}}
                    <div class="d-flex flex-column gap-2">
                        <form method="POST" action="{{ route('queue.manage.ticket.skip', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 rounded-3 py-2.5 fw-bold" onclick="return confirm('Bạn chắc chắn muốn bỏ qua bệnh nhân này?')">
                                <i class="bi bi-skip-forward-fill me-2"></i>BỎ QUA
                            </button>
                        </form>
                    </div>
                    @elseif($userRole === 1) {{-- Admin - show all buttons --}}
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-info w-100 rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#adminActionsModal">
                            <i class="bi bi-sliders me-2"></i>QUẢN LÝ
                        </button>
                    </div>
                    @endif
                    @else
                    <div class="py-5">
                        <p class="text-6xl mb-3">😌</p>
                        <p class="text-muted">Không có bệnh nhân đang gọi</p>
                        <small class="text-secondary">Hãy thêm bệnh nhân hoặc gọi số tiếp theo</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══ CỘT PHẢI: HÀNG ĐỢI BỆNH NHÂN ═══════════════════ --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">📋 Danh Sách Chờ ({{ $snapshot['stats']['total_waiting'] }} bệnh nhân)</h6>
                </div>
                <div class="card-body p-0">
                    @if($snapshot['waiting']->count() > 0)
                    <div class="list-group list-group-flush divide-y max-vh-60" style="max-height: 600px; overflow-y: auto;">
                        @foreach($snapshot['waiting'] as $ticket)
                        @php
                            $priorityColors = [
                                'emergency' => '#ef4444',
                                'disabled' => '#a855f7',
                                'elderly' => '#3b82f6',
                                'normal' => '#6b7280',
                            ];
                            $bgColor = $priorityColors[$ticket['priority']] ?? '#6b7280';
                        @endphp
                        <div class="list-group-item border-0 p-4 d-flex gap-3 align-items-center hover-bg-light transition">
                            {{-- Số Thứ Tự --}}
                            <div class="queue-number-badge" style="--badge-bg-color: {{ $bgColor }};">
                                {{ $ticket['queue_number'] }}
                            </div>

                            {{-- Thông Tin Bệnh Nhân --}}
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-gray-800 mb-1">{{ $ticket['patient_name'] }}</div>
                                <div class="d-flex gap-2 align-items-center text-muted text-xs mb-1">
                                    <span>{{ $ticket['priority_icon'] }} {{ $ticket['priority_label'] }}</span>
                                    <span>•</span>
                                    <span>SĐT: {{ $ticket['patient_phone'] ?? '—' }}</span>
                                </div>
                                <div class="text-muted text-xs">
                                    <i class="bi bi-clock"></i> Ước tính: {{ $ticket['est_wait_minutes'] }} phút
                                </div>
                                @if($ticket['notes'])
                                <div class="bg-light rounded p-2 mt-2 text-xs">
                                    <small class="text-secondary"><strong>Ghi chú:</strong> {{ $ticket['notes'] }}</small>
                                </div>
                                @endif
                            </div>

                            {{-- Badge Ưu Tiên --}}
                            @if($ticket['priority'] !== 'normal')
                            <div class="flex-shrink-0">
                                <span class="badge-priority" style="--badge-bg-color: {{ $bgColor }};">
                                    {{ $ticket['priority_label'] }}
                                </span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <p class="text-5xl mb-3">✅</p>
                        <p class="text-muted fw-500 mb-0">Hàng đợi trống!</p>
                        <small class="text-secondary">Tất cả bệnh nhân đã được khám hoặc chưa có bệnh nhân mới.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ LỊCH SỬ NGÀY HÔM NAY ════════════════════════════════ --}}
    @if($history->count() > 0)
    <div class="mt-5">
        <h5 class="fw-bold text-gray-800 mb-3">📜 Lịch Sử Ngày Hôm Nay</h5>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th class="ps-4">Số TT</th>
                            <th>Bệnh Nhân</th>
                            <th>Trạng Thái</th>
                            <th>Đối Tượng</th>
                            <th>Hoàn Thành Lúc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $item)
                        <tr class="{{ $item->status === 'completed' ? '' : 'opacity-75' }}">
                            <td class="ps-4">
                                <span class="badge bg-secondary text-white">
                                    #{{ $item->queue_number }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->patient_name }}</strong><br>
                                <small class="text-muted">{{ $item->patient_phone ?? '—' }}</small>
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($item->status) {
                                        'completed' => 'bg-success-subtle text-success',
                                        'skipped' => 'bg-warning-subtle text-warning',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $item->status_label }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $item->priority_icon }} {{ $item->priority_label }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $item->completed_at ? \Carbon\Carbon::parse($item->completed_at)->format('H:i:s') : '—' }}
                                </small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Admin Actions Modal --}}
@if($userRole === 1 && $snapshot['current'])
<div class="modal fade" id="adminActionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-sliders me-2"></i>Quản Lý Bệnh Nhân
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <strong class="text-primary">{{ $snapshot['current']['patient_name'] }} (#{{ $snapshot['current']['queue_number'] }})</strong>
                    <p class="text-muted small mt-1">Trạng thái: {{ $snapshot['current']['status'] === 'calling' ? '🔔 ĐANG GỌI' : '🩺 ĐANG KHÁM' }}</p>
                </div>
                
                <div class="d-flex flex-column gap-2">
                    @if($snapshot['current']['status'] === 'calling')
                    <form method="POST" action="{{ route('queue.doctor.start', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 rounded-3">
                            <i class="bi bi-play-fill me-2"></i>Bắt Đầu Khám
                        </button>
                    </form>
                    @elseif($snapshot['current']['status'] === 'in_progress')
                    <form method="POST" action="{{ route('queue.doctor.complete', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 rounded-3">
                            <i class="bi bi-check2-circle me-2"></i>Hoàn Thành
                        </button>
                    </form>
                    @endif
                    
                    <form method="POST" action="{{ route('queue.manage.ticket.skip', $snapshot['current']['ticket_id']) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 rounded-3" onclick="return confirm('Bạn chắc chắn muốn bỏ qua bệnh nhân này?')">
                            <i class="bi bi-skip-forward-fill me-2"></i>Bỏ Qua
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    // Auto-refresh every 5 seconds
    setInterval(function() {
        fetch('{{ route("admin.queue.api.snapshot", $schedule->schedule_id) }}')
            .then(r => r.json())
            .then(data => {
                console.log('Updated snapshot:', data);
                // You can update specific parts without full page reload
            });
    }, 5000);
</script>
@endsection
