{{-- resources/views/emergency-contacts.blade.php --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ khẩn cấp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <div class="min-h-screen bg-slate-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Liên hệ khẩn cấp</h1>
                    <p class="text-sm text-slate-500 mt-1">Quản lý danh sách người thân nhận thông báo trong các trường hợp quan trọng.</p>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600 font-medium">LIVE</span>
                </div>
            </div>

            {{-- Flash success --}}
            @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6 flex gap-3 items-start">
                <div class="text-emerald-600 text-lg">✅</div>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
            @endif

            {{-- Validation errors tổng hợp --}}
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
                <p class="text-sm font-semibold text-red-700 mb-2">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li class="text-sm text-red-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Alert Info --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 mb-8 flex gap-3 items-start">
                <div class="text-sky-600 text-lg">ℹ️</div>
                <div>
                    <p class="text-sm font-medium text-sky-800">Tối đa 3 người thân.</p>
                    <p class="text-sm text-sky-700">Thông tin này được dùng khi bệnh nhân nhập viện khẩn cấp hoặc có kết quả xét nghiệm cảnh báo cao.</p>
                </div>
            </div>

            {{-- Form POST đến route name --}}
            <form action="{{ route('emergency-contacts.store') }}" method="POST" class="space-y-6">
                @csrf

                @php
                // Nếu validation fail, dùng old(); ngược lại dùng $contacts từ controller
                $formContacts = old('contacts', $contacts);
                @endphp

                @foreach($formContacts as $index => $contact)
                @php
                $priority = $index + 1;
                $isPopulated = filled($contact['name']);
                $avatarColor = match($index) {
                0 => 'bg-sky-600',
                1 => 'bg-cyan-600',
                default => 'bg-slate-300 text-slate-600',
                };
                $initials = $isPopulated
                ? collect(explode(' ', trim($contact['name'])))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('')
                : '?';
                @endphp

                <div class="rounded-3xl border {{ $isPopulated ? 'border-sky-200 bg-white shadow-sm' : 'border-dashed border-slate-300 bg-slate-50' }} overflow-hidden">
                    <div class="p-6">

                        {{-- Card Header --}}
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white {{ $avatarColor }}">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <h2 class="font-semibold text-slate-800">Ưu tiên {{ $priority }}</h2>
                                    <p class="text-sm text-slate-500">{{ $contact['relationship'] ?: 'Chưa thiết lập' }}</p>
                                </div>
                            </div>

                            @if($isPopulated)
                            @php
                            $telPhone = preg_replace('/[\s\-\(\)]/', '', $contact['phone']);
                            // Zalo deep link — mở thẳng Zalo dial pad
                            $zaloLink = 'zalo://call?phone=' . $telPhone;
                            @endphp
                            <div class="flex gap-3">
                                <a href="tel:{{ $telPhone }}"
                                    class="inline-flex items-center px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-medium hover:bg-sky-700 transition">
                                    📞 {{ $contact['phone'] }}
                                </a>
                                <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $telPhone }}'); this.textContent='✅ Đã copy';"
                                    class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 text-sm hover:bg-slate-100 transition">
                                    📋 Copy số
                                </button>
                                @if(filled($contact['email']))
                                <a href="mailto:{{ $contact['email'] }}"
                                    class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-100 transition">
                                    ✉️ Gửi thông báo
                                </a>
                                @else
                                <button type="button" disabled title="Chưa có email"
                                    class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 text-slate-400 text-sm font-medium cursor-not-allowed">
                                    ✉️ Gửi thông báo
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Họ tên --}}
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Họ tên</label>
                                <input type="text"
                                    name="contacts[{{ $index }}][name]"
                                    value="{{ $contact['name'] }}"
                                    placeholder="Nhập họ tên người thân..."
                                    class="w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500
                                              {{ $errors->has("contacts.{$index}.name") ? 'border-red-400' : '' }}">
                                @error("contacts.{$index}.name")
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Mối quan hệ --}}
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Mối quan hệ</label>
                                <select name="contacts[{{ $index }}][relationship]"
                                    class="w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500
                                               {{ $errors->has("contacts.{$index}.relationship") ? 'border-red-400' : '' }}">
                                    <option value="">-- Chọn --</option>
                                    @foreach($relationshipOptions as $option)
                                    <option value="{{ $option }}" {{ ($contact['relationship'] ?? '') === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                    @endforeach
                                </select>
                                @error("contacts.{$index}.relationship")
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Số điện thoại --}}
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Số điện thoại</label>
                                <input type="text"
                                    name="contacts[{{ $index }}][phone]"
                                    value="{{ $contact['phone'] }}"
                                    placeholder="0900 000 000"
                                    class="w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500
                                              {{ $errors->has("contacts.{$index}.phone") ? 'border-red-400' : '' }}">
                                @error("contacts.{$index}.phone")
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Email (tùy chọn)</label>
                                <input type="email"
                                    name="contacts[{{ $index }}][email]"
                                    value="{{ $contact['email'] }}"
                                    placeholder="email@gmail.com"
                                    class="w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500
                                              {{ $errors->has("contacts.{$index}.email") ? 'border-red-400' : '' }}">
                                @error("contacts.{$index}.email")
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Notification Options --}}
                        <div class="mt-6 flex flex-col md:flex-row gap-6">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox"
                                    name="contacts[{{ $index }}][lab_notifications]"
                                    value="1"
                                    {{ ($contact['lab_notifications'] ?? false) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                Nhận thông báo xét nghiệm / nhập viện
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox"
                                    name="contacts[{{ $index }}][recovery_updates]"
                                    value="1"
                                    {{ ($contact['recovery_updates'] ?? false) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                Nhận cập nhật tình trạng hồi phục sau phẫu thuật
                            </label>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Emergency Protocol Section --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-6 mt-8">
                    <h3 class="text-lg font-bold text-emerald-800 mb-6">🚨 Giao thức kích hoạt khẩn cấp</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-2xl border border-emerald-100 p-5 hover:shadow-md transition">
                            <div class="text-2xl mb-3">🏥</div>
                            <h4 class="font-semibold text-slate-800">Nhập viện khẩn cấp</h4>
                            <p class="text-sm text-slate-500 mt-1">Gửi SMS tự động đến tất cả liên hệ trong 2 phút</p>
                        </div>

                        <div class="bg-white rounded-2xl border border-emerald-100 p-5 hover:shadow-md transition">
                            <div class="text-2xl mb-3">🧪</div>
                            <h4 class="font-semibold text-slate-800">Kết quả xét nghiệm nghiêm trọng</h4>
                            <p class="text-sm text-slate-500 mt-1">Thông báo cho ưu tiên 1 ngay lập tức</p>
                        </div>

                        <div class="bg-white rounded-2xl border border-emerald-100 p-5 hover:shadow-md transition">
                            <div class="text-2xl mb-3">💊</div>
                            <h4 class="font-semibold text-slate-800">Theo dõi hậu phẫu</h4>
                            <p class="text-sm text-slate-500 mt-1">Gửi cập nhật định kỳ 6h/lần cho người được chỉ định</p>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-4 pt-4">
                    <a href="{{ url()->previous() }}"
                        class="px-6 py-3 rounded-2xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 transition">
                        Hủy
                    </a>

                    <button type="submit"
                        class="px-6 py-3 rounded-2xl bg-sky-700 text-white font-semibold hover:bg-sky-800 shadow-lg transition">
                        💾 Lưu danh sách liên hệ
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>