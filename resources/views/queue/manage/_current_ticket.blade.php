<div class="card border-0 bg-light rounded-4 p-4 text-center">
    <span class="badge bg-warning-subtle text-warning-emphasis align-self-center rounded-pill px-3 py-2 text-xs font-bold uppercase tracking-wider mb-3">
        @if($ticket->status === 'calling')
            🔔 ĐANG GỌI SỐ
        @else
            🩺 ĐANG KHÁM BỆNH
        @endif
    </span>
    <h2 class="text-7xl font-black text-gray-900 tracking-tight mb-2">
        #{{ $ticket->queue_number }}
    </h2>
    <h4 class="fw-bold text-gray-800 mb-1">{{ $ticket->patient_name }}</h4>
    <p class="text-secondary text-sm mb-3">
        {{ $ticket->priority_icon }} {{ $ticket->priority_label }}
        @if($ticket->patient_phone)
            • SĐT: {{ $ticket->patient_phone }}
        @endif
    </p>

    @if($ticket->notes)
        <div class="bg-white rounded-3 p-3 text-start mb-3 border border-gray-100 shadow-inner">
            <small class="d-block text-secondary font-semibold uppercase tracking-wider mb-1">Ghi chú từ quầy check-in:</small>
            <p class="text-gray-700 mb-0 font-medium text-sm">{{ $ticket->notes }}</p>
        </div>
    @endif

    <div class="d-flex justify-content-center gap-2">
        <form method="POST" action="{{ route('queue.manage.ticket.skip', $ticket->ticket_id) }}" data-confirm="Bạn có chắc chắn muốn bỏ qua bệnh nhân này?">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 font-bold shadow-sm">
                <i class="bi bi-x-circle me-1"></i> Bỏ Qua Ca Này
            </button>
        </form>
    </div>
</div>
