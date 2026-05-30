@extends('layouts.app')

@section('title', 'Check-in Bệnh nhân')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-b">
        <div>
            <h1 class="fw-black text-primary text-3xl tracking-tight mb-1">
                <i class="bi bi-person-badge-fill me-2"></i>Tiếp Đón & Check-in Hàng Đợi
            </h1>
            <p class="text-secondary mb-0">Tìm kiếm lịch hẹn trực tuyến hoặc đăng ký bệnh nhân khám trực tiếp (Walk-in)</p>
        </div>
        <div>
            <a href="{{ route('queue.manage.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="row g-5">
        {{-- PANEL TRÁI: Tìm kiếm bệnh nhân --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-gradient bg-primary text-white border-0 py-3 px-4">
                    <h5 class="mb-0 font-bold text-sm"><i class="bi bi-search me-2"></i>Tìm kiếm lịch hẹn / Bệnh nhân</h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('queue.manage.checkin') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="keyword" class="form-control rounded-start-3" 
                                   placeholder="Mã lịch hẹn, SĐT, hoặc Email..." 
                                   value="{{ $keyword ?? '' }}" required>
                            <button class="btn btn-primary rounded-end-3 px-4 font-bold" type="submit">
                                Tìm kiếm
                            </button>
                        </div>
                        <small class="text-secondary d-block mt-2">Ví dụ: nhập mã lịch hẹn (ví dụ: 12) hoặc SĐT bệnh nhân</small>
                    </form>

                    <hr class="text-gray-200 my-4">

                    {{-- Kết quả tìm kiếm --}}
                    @if(isset($result))
                        @if($result['found'])
                            <div class="alert alert-success border-0 bg-success-subtle text-success-emphasis rounded-3 p-3 mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i> Đã tìm thấy thông tin bệnh nhân!
                            </div>

                            @if($result['type'] === 'appointment')
                                @php $appointment = $result['data']; @endphp
                                <div class="bg-light rounded-4 p-4 mb-3 border border-gray-100">
                                    <h6 class="text-xs uppercase tracking-wider text-secondary font-black mb-3">Thông Tin Lịch Hẹn Trực Tuyến</h6>
                                    <p class="mb-2">Mã lịch hẹn: <strong class="text-primary">#{{ $appointment->appointment_id }}</strong></p>
                                    <p class="mb-2">Bệnh nhân: <strong class="text-gray-800">{{ $appointment->user->full_name ?? '—' }}</strong></p>
                                    <p class="mb-2">Bác sĩ khám: <strong class="text-gray-800">{{ $appointment->schedule->doctor->full_name ?? '—' }}</strong></p>
                                    <p class="mb-2">Ca khám: <strong class="text-gray-800">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i, d/m/Y') }}</strong></p>
                                    
                                    <button type="button" class="btn btn-success w-100 rounded-3 py-2 mt-3 font-bold"
                                            onclick="fillCheckinForm({
                                                appointment_id: '{{ $appointment->appointment_id }}',
                                                user_id: '{{ $appointment->user_id }}',
                                                patient_name: '{{ $appointment->user->full_name ?? '' }}',
                                                patient_phone: '{{ $appointment->user->phone ?? '' }}',
                                                patient_email: '{{ $appointment->user->email ?? '' }}',
                                                schedule_id: '{{ $appointment->schedule_id }}'
                                            })">
                                        <i class="bi bi-check2-square me-2"></i>Sử dụng thông tin này
                                    </button>
                                </div>
                            @elseif($result['type'] === 'user')
                                @php $user = $result['data']; @endphp
                                <div class="bg-light rounded-4 p-4 mb-3 border border-gray-100">
                                    <h6 class="text-xs uppercase tracking-wider text-secondary font-black mb-3">Tài Khoản Bệnh Nhân</h6>
                                    <p class="mb-2">Họ & tên: <strong class="text-gray-800">{{ $user->full_name }}</strong></p>
                                    <p class="mb-2">Số điện thoại: <strong class="text-gray-800">{{ $user->phone ?? '—' }}</strong></p>
                                    <p class="mb-3">Email: <strong class="text-gray-800">{{ $user->email ?? '—' }}</strong></p>

                                    @if(count($result['appointments']) > 0)
                                        <h6 class="text-xs uppercase tracking-wider text-primary font-black mt-4 mb-2">Các lịch hẹn hôm nay:</h6>
                                        <div class="list-group rounded-3 mb-3">
                                            @foreach($result['appointments'] as $app)
                                                <button type="button" class="list-group-item list-group-item-action text-start p-3 text-sm"
                                                        onclick="fillCheckinForm({
                                                            appointment_id: '{{ $app->appointment_id }}',
                                                            user_id: '{{ $user->user_id }}',
                                                            patient_name: '{{ $user->full_name }}',
                                                            patient_phone: '{{ $user->phone }}',
                                                            patient_email: '{{ $user->email }}',
                                                            schedule_id: '{{ $app->schedule_id }}'
                                                        })">
                                                    <strong>Lịch #{{ $app->appointment_id }}</strong> - BS. {{ $app->schedule->doctor->full_name }}<br>
                                                    <small class="text-secondary">Ca: {{ \Carbon\Carbon::parse($app->appointment_time)->format('H:i') }}</small>
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-secondary text-sm my-3 border border-dashed rounded p-3 text-center">
                                            Không có lịch hẹn đặt trước trong ngày hôm nay. Bệnh nhân sẽ được đăng ký diện <strong>Khám trực tiếp (Walk-in)</strong>.
                                        </p>
                                        <button type="button" class="btn btn-secondary w-100 rounded-3 py-2 font-bold"
                                                onclick="fillCheckinForm({
                                                    user_id: '{{ $user->user_id }}',
                                                    patient_name: '{{ $user->full_name }}',
                                                    patient_phone: '{{ $user->phone }}',
                                                    patient_email: '{{ $user->email }}'
                                                })">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng ký Khám trực tiếp
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis rounded-3 p-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Không tìm thấy lịch hẹn hoặc bệnh nhân. Vui lòng nhập thông tin thủ công ở biểu mẫu bên phải.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- PANEL PHẢI: Biểu mẫu check-in --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-gradient bg-emerald-600 text-white border-0 py-3 px-4">
                    <h5 class="mb-0 font-bold text-sm"><i class="bi bi-file-earmark-medical me-2"></i>Thông tin Check-in Xếp Hàng</h5>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-4 border-0 p-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('queue.manage.checkin.store') }}" id="checkinForm">
                        @csrf
                        
                        {{-- Hidden inputs --}}
                        <input type="hidden" name="appointment_id" id="hidden_appointment_id">
                        <input type="hidden" name="user_id" id="hidden_user_id">

                        <div class="row g-3">
                            {{-- Ca khám bác sĩ --}}
                            <div class="col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Chọn Ca Khám & Bác Sĩ Hôm Nay <span class="text-danger">*</span></label>
                                <select name="schedule_id" id="form_schedule_id" class="form-select rounded-3 p-2.5" required>
                                    <option value="" disabled selected>-- Chọn bác sĩ đang trực hôm nay --</option>
                                    @foreach($schedules as $s)
                                        <option value="{{ $s->schedule_id }}" {{ request()->get('schedule_id') == $s->schedule_id ? 'selected' : '' }}>
                                            BS. {{ $s->doctor->full_name }} (Phòng {{ $s->room->room_code ?? '—' }}) 
                                            - Ca: {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Đối tượng ưu tiên --}}
                            <div class="col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Đối Tượng Tiếp Đón <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <input type="radio" class="btn-check" name="priority" id="p_normal" value="normal" checked>
                                        <label class="btn btn-outline-secondary w-100 py-3.5 rounded-3 font-semibold text-sm d-flex flex-column align-items-center gap-1 shadow-sm" for="p_normal">
                                            <span>👤</span>
                                            <span>Thường</span>
                                        </label>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <input type="radio" class="btn-check" name="priority" id="p_elderly" value="elderly">
                                        <label class="btn btn-outline-primary w-100 py-3.5 rounded-3 font-semibold text-sm d-flex flex-column align-items-center gap-1 shadow-sm" for="p_elderly">
                                            <span>👴</span>
                                            <span>Cao tuổi (≥60)</span>
                                        </label>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <input type="radio" class="btn-check" name="priority" id="p_disabled" value="disabled">
                                        <label class="btn btn-outline-purple w-100 py-3.5 rounded-3 font-semibold text-sm d-flex flex-column align-items-center gap-1 shadow-sm" for="p_disabled">
                                            <span>♿</span>
                                            <span>Khuyết tật</span>
                                        </label>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <input type="radio" class="btn-check" name="priority" id="p_emergency" value="emergency">
                                        <label class="btn btn-outline-danger w-100 py-3.5 rounded-3 font-semibold text-sm d-flex flex-column align-items-center gap-1 shadow-sm" for="p_emergency">
                                            <span>🚨</span>
                                            <span>Cấp cứu</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Tên bệnh nhân --}}
                            <div class="col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Họ và Tên Bệnh Nhân <span class="text-danger">*</span></label>
                                <input type="text" name="patient_name" id="form_patient_name" class="form-control rounded-3 p-2.5" 
                                       placeholder="Nhập tên bệnh nhân hiển thị..." required>
                            </div>

                            {{-- Số điện thoại --}}
                            <div class="col-md-6 col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Số Điện Thoại</label>
                                <input type="text" name="patient_phone" id="form_patient_phone" class="form-control rounded-3 p-2.5" 
                                       placeholder="Số điện thoại liên lạc...">
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Email</label>
                                <input type="email" name="patient_email" id="form_patient_email" class="form-control rounded-3 p-2.5" 
                                       placeholder="Địa chỉ email...">
                            </div>

                            {{-- Ghi chú --}}
                            <div class="col-12">
                                <label class="form-label font-bold text-gray-700 text-sm">Ghi Chú Triệu Chứng / Trạng Thái</label>
                                <textarea name="notes" id="form_notes" rows="2" class="form-control rounded-3 p-2.5" 
                                          placeholder="Triệu chứng khám sơ bộ, đối tượng miễn phí, bảo hiểm thẻ..."></textarea>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill shadow font-black py-2.5">
                                <i class="bi bi-printer-fill me-2"></i>IN SỐ & THÊM VÀO HÀNG ĐỢI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCheckinForm(data) {
    // Hidden fields
    document.getElementById('hidden_appointment_id').value = data.appointment_id || '';
    document.getElementById('hidden_user_id').value = data.user_id || '';

    // Visible fields
    document.getElementById('form_patient_name').value = data.patient_name || '';
    document.getElementById('form_patient_phone').value = data.patient_phone || '';
    document.getElementById('form_patient_email').value = data.patient_email || '';

    // Auto select schedule if matched
    if (data.schedule_id) {
        document.getElementById('form_schedule_id').value = data.schedule_id;
    }

    // Dynamic warning alert
    if (data.appointment_id) {
        document.getElementById('form_notes').value = "Khớp lịch hẹn trực tuyến #" + data.appointment_id;
    } else {
        document.getElementById('form_notes').value = "Walk-in từ tài khoản bệnh nhân";
    }

    // Scroll to form smoothly
    document.getElementById('checkinForm').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endsection
