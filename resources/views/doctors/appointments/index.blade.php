@extends('layouts.app')
@section('title', 'Lịch Khám Bệnh Nhân')

@section('content')
<div style="max-width:1100px;margin:24px auto;padding:0 16px">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">🩺 Lịch Khám Bệnh Nhân</h5>
    <small class="text-muted">Bác sĩ Khám : {{ $doctor->full_name }}</small>
  </div>

  <div class="d-flex align-items-center gap-2">
      <a href="{{ route('medical-records.index') }}" class="btn btn-primary btn-sm">
    Danh Sách phiếu khám
    </a>
    <a href="{{ url('/') }}"
       class="btn btn-warning btn-sm">
      <i class="bi bi-house-door-fill"></i> Trang chủ
    </a>
  
  </div>
</div>

  {{-- Stats --}}
  <div class="row g-3 mb-4">
    @foreach([
      ['label'=>'Hôm nay',       'val'=>$stats['today'],     'color'=>'#1a6fb3'],
      ['label'=>'Chờ xác nhận',  'val'=>$stats['pending'],   'color'=>'#f39c12'],
      ['label'=>'Đã xác nhận',   'val'=>$stats['confirmed'], 'color'=>'#27ae60'],
      ['label'=>'Đã khám',       'val'=>$stats['done'],      'color'=>'#7f8c9a'],
    ] as $s)
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm text-center py-3">
        <div class="fs-2 fw-bold" style="color:{{ $s['color'] }}">{{ $s['val'] }}</div>
        <div class="small text-muted">{{ $s['label'] }}</div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Filter --}}
  <form class="d-flex flex-wrap gap-2 mb-3" method="GET">
    <input type="date" name="date" class="form-control" style="max-width:170px"
           value="{{ $date }}">
    <select name="status" class="form-select" style="max-width:190px">
      <option value="all"          {{ $status==='all'          ?'selected':'' }}>Tất cả</option>
      <option value="Chờ xác nhận" {{ $status==='Chờ xác nhận'?'selected':'' }}>Chờ xác nhận</option>
      <option value="Đã xác nhận"  {{ $status==='Đã xác nhận' ?'selected':'' }}>Đã xác nhận</option>
      <option value="Đã Khám"      {{ $status==='Đã Khám'     ?'selected':'' }}>Đã khám</option>
    </select>
    <button class="btn btn-primary">🔍 Lọc</button>
    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-outline-secondary">↺ Reset</a>
  </form>

  {{-- Table --}}
  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#fafbfc;border-bottom:2px solid #e0e6ed">
          <tr>
            <th class="ps-3">Bệnh nhân</th>
            <th>Dịch vụ</th>
            <th>Giờ khám</th>
            <th>Ghi chú</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
        @forelse($appointments as $apt)
        <tr>
          <td class="ps-3">
            <div class="fw-semibold">{{ $apt->user->full_name ?? '—' }}</div>
            <small class="text-muted">{{ $apt->user->phone ?? '' }}</small>
          </td>
          <td>{{ $apt->service->service_name ?? '—' }}</td>
          <td>
            <div class="fw-semibold">
              {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}
            </div>
            <small class="text-muted">
              {{ \Carbon\Carbon::parse($apt->appointment_time)->format('d/m/Y') }}
            </small>
          </td>
          <td><small class="text-muted">{{ Str::limit($apt->note, 40) ?? '—' }}</small></td>
          <td>
            <span class="badge bg-{{ $apt->statusColor() }}">
              {{ $apt->statusIcon() }} {{ $apt->status }}
            </span>
          </td>
   <td>
    @php
        $record = $apt->medicalRecord;
        $myRecord = $record && $record->doctor_id == Auth::id();
    @endphp

    @if($myRecord)
        <a href="{{ route('medical-records.show', $record->record_id) }}"
           class="btn btn-sm btn-outline-primary">
            📄 Xem hồ sơ
        </a>
    @elseif(in_array($apt->status, ['Chờ xác nhận', 'Đã xác nhận']))
        <a href="{{ route('medical-records.create', ['appointment_id' => $apt->appointment_id]) }}"
           class="btn btn-sm btn-success">
            🩺 Khám bệnh
        </a>
    @else
        <span class="text-muted small">—</span>
    @endif
</td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center text-muted py-5">
            📭 Không có lịch hẹn nào
            @if($date)
              ngày {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            @endif
          </td>
        </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    @if($appointments->hasPages())
    <div class="card-footer bg-white">
      {{ $appointments->withQueryString()->links() }}
    </div>
    @endif
  </div>

</div>
@endsection