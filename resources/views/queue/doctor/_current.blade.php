<div class="card border-0 bg-light rounded-4 p-4 text-center">
    <span class="badge align-self-center rounded-pill px-3 py-2 text-xs font-bold uppercase tracking-wider mb-3
        {{ $ticket->status === 'calling' ? 'bg-danger text-white' : 'bg-success text-white' }}">
        @if($ticket->status === 'calling')
            🔔 ĐANG GỌI SỐ
        @else
            🩺 ĐANG KHÁM BỆNH
        @endif
    </span>

    <h2 class="text-7xl font-black text-gray-900 mb-2">#{{ $ticket->queue_number }}</h2>
    <h4 class="fw-bold text-gray-800 mb-1">{{ $ticket->patient_name }}</h4>
    <p class="text-secondary text-sm mb-4">
        Đối tượng: {{ $ticket->priority_icon }} <strong>{{ $ticket->priority_label }}</strong>
        @if($ticket->patient_phone)
            • SĐT: {{ $ticket->patient_phone }}
        @endif
    </p>

    @if($ticket->notes)
        <div class="bg-white rounded-3 p-3 text-start mb-4 border border-gray-150 shadow-inner">
            <small class="d-block text-secondary font-semibold uppercase tracking-wider mb-1">Ghi chú lâm sàng / Đón tiếp:</small>
            <p class="text-gray-700 mb-0 font-medium text-sm">{{ $ticket->notes }}</p>
        </div>
    @endif

    <div class="d-flex flex-column gap-2">
        @if($ticket->status === 'calling')
            <form method="POST" action="{{ route('queue.doctor.start', $ticket->ticket_id) }}">
                @csrf
                <button type="submit" class="btn btn-success w-100 rounded-pill py-2.5 font-black shadow">
                    <i class="bi bi-play-fill me-1"></i> BẮT ĐẦU KHÁM BỆNH
                </button>
            </form>
        @elseif($ticket->status === 'in_progress')
            <form method="POST" action="{{ route('queue.doctor.complete', $ticket->ticket_id) }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 font-black shadow-sm mb-2">
                    <i class="bi bi-check2-circle me-1"></i> HOÀN THÀNH CA KHÁM
                </button>
            </form>
        @endif
    </div>
</div>
