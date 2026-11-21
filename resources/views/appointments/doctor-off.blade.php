<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bác sĩ đã nghỉ - HospitalC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-5xl mx-auto px-4 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Bác sĩ đã nghỉ</h1>
                    <p class="text-sm text-slate-500">Lịch hẹn của bạn bị ảnh hưởng do bác sĩ không làm việc.</p>
                </div>
                <a href="{{ route('appointments.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    ← Quay lại lịch hẹn
                </a>
            </div>
        </header>

        <main class="flex-1 max-w-5xl mx-auto px-4 py-10">
            <div class="grid gap-6">
                <div class="rounded-3xl bg-white p-8 shadow-sm border border-slate-200">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 h-12 w-12 rounded-2xl bg-orange-100 text-orange-700 flex items-center justify-center text-2xl">⚠️</div>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Lịch của bạn đã bị ảnh hưởng</h2>
                            <p class="mt-2 text-slate-600">Bác sĩ <strong>BS. {{ $appointment->doctor_name }}</strong> đã đăng ký nghỉ. Lịch hẹn này hiện đang ở trạng thái <strong>"Bác sĩ nghỉ"</strong>.</p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs text-slate-500 uppercase tracking-[0.24em]">Ngày hẹn</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ \Carbon\Carbon::parse($appointment->work_date)->format('l, d/m/Y') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs text-slate-500 uppercase tracking-[0.24em]">Giờ hẹn</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-sm text-slate-700 leading-7">Nếu bạn muốn tiếp tục khám bệnh với bác sĩ khác hoặc chọn khung giờ khác, vui lòng chọn <strong>Dời lịch</strong>. Nếu không thể đi khám, bạn có thể <strong>Hủy lịch</strong> ngay.</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-8 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Hành động</h3>
                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <a href="{{ route('appointments.edit', $appointment->appointment_id) }}" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            🔄 Dời lịch ngay
                        </a>
                        <form method="POST" action="{{ route('appointments.cancel', $appointment->appointment_id) }}" class="inline-flex">
                            @csrf
                            <input type="hidden" name="cancel_reason" value="Bác sĩ nghỉ">
                            <button type="submit" class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                                ❌ Hủy lịch
                            </button>
                        </form>
                        <a href="{{ route('appointments.create') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            📆 Đặt lịch mới
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl bg-slate-50 p-8 border border-slate-200 text-slate-700">
                    <h3 class="text-base font-semibold text-slate-900">Lưu ý</h3>
                    <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-6">
                        <li>Hệ thống đã đánh dấu lịch này là <strong>Bác sĩ nghỉ</strong>.</li>
                        <li>Bạn có thể chọn dời sang bác sĩ khác hoặc huỷ nếu không muốn giữ lịch.</li>
                        <li>Nếu bạn đã thanh toán trước, khoản tiền sẽ được hoàn trả theo chính sách của bệnh viện.</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    @include('components.back-to-previous')
</body>
</html>
