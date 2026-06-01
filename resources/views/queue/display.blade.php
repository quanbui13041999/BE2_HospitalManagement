<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Màn hình hiển thị hàng đợi - {{ $schedule->doctor?->full_name ?? 'Bác sĩ' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <x-typography-base />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;700;900&display=swap');
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .bg-gray-750 { background-color: #1f2937; }
        .bg-gray-850 { background-color: #111827; }
        .blink { animation: blink 1s step-start infinite; }
        @keyframes blink { 50% { opacity: 0; } }
        .slide-in { animation: slideIn 0.5s ease-out; }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen" x-data="queueDisplay()" x-init="init()">

    {{-- Header --}}
    <div class="bg-blue-800 px-8 py-4 flex justify-between items-center shadow-lg border-b border-blue-900">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">{{ $schedule->doctor?->full_name ?? 'Bác sĩ' }}</h1>
            <p class="text-blue-200 text-sm mt-1">
                {{ $schedule->doctor?->department?->department_name ?? 'Khoa Khám Bệnh' }} •
                Phòng {{ $schedule->room->room_code ?? '—' }} •
                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-4xl font-mono font-black tracking-widest text-emerald-400" x-text="currentTime">{{ now()->format('H:i:s') }}</p>
            <p class="text-blue-200 text-sm font-semibold mt-1">{{ now()->translatedFormat('d/m/Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-5 gap-0" style="height: calc(100vh - 92px)">

        {{-- CỘT TRÁI: Đang gọi + Đang khám --}}
        <div class="col-span-2 bg-gray-800 p-8 flex flex-col gap-6 border-r border-gray-700">

            {{-- Đang gọi --}}
            <div class="bg-gradient-to-br from-rose-600 to-red-700 rounded-3xl p-8 text-center shadow-2xl flex-1 flex flex-col justify-center transition-all duration-300 transform hover:scale-105" x-show="current && current.status === 'calling'" x-cloak>
                <p class="text-red-100 text-xl font-bold uppercase tracking-widest mb-4 animate-pulse">🔔 Xin Mời Vào Phòng Khám</p>
                <p class="text-9xl font-black tracking-tighter blink text-yellow-300" x-text="current?.queue_number">—</p>
                <p class="text-3xl font-bold mt-6 text-white" x-text="current?.patient_name">—</p>
                <p class="mt-3 text-red-200 text-lg" x-text="(current?.priority_icon || '👤') + ' ' + (current?.priority_label || 'Thường')">—</p>
            </div>

            {{-- Đang khám --}}
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-8 text-center shadow-2xl flex-1 flex flex-col justify-center transition-all duration-300 transform hover:scale-105" x-show="current && current.status === 'in_progress'" x-cloak>
                <p class="text-emerald-100 text-xl font-bold uppercase tracking-widest mb-4">🩺 Đang Khám Bệnh</p>
                <p class="text-9xl font-black tracking-tighter text-yellow-200" x-text="current?.queue_number">—</p>
                <p class="text-3xl font-bold mt-6 text-white" x-text="current?.patient_name">—</p>
                <p class="mt-3 text-emerald-200 text-lg">Vui lòng chờ bên ngoài cho đến khi được gọi</p>
            </div>

            {{-- Không có ai --}}
            <div class="bg-gray-750 border-2 border-dashed border-gray-600 rounded-3xl p-8 text-center flex-1 flex flex-col justify-center items-center" x-show="!current" x-cloak>
                <p class="text-8xl mb-6">😊</p>
                <p class="text-gray-300 text-2xl font-bold">Chưa bắt đầu gọi số</p>
                <p class="text-gray-500 mt-2 text-base">Bác sĩ chuẩn bị khám bệnh</p>
            </div>

            {{-- Thống kê hàng đợi --}}
            <div class="grid grid-cols-3 gap-4 mt-auto">
                <div class="bg-gray-850 rounded-2xl p-4 text-center border border-gray-700 shadow-lg">
                    <p class="text-4xl font-extrabold text-amber-400" x-text="stats.total_waiting">0</p>
                    <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-2">Đang chờ</p>
                </div>
                <div class="bg-gray-850 rounded-2xl p-4 text-center border border-gray-700 shadow-lg">
                    <p class="text-4xl font-extrabold text-emerald-400" x-text="stats.total_completed">0</p>
                    <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-2">Đã khám</p>
                </div>
                <div class="bg-gray-850 rounded-2xl p-4 text-center border border-gray-700 shadow-lg">
                    <p class="text-4xl font-extrabold text-sky-400" x-text="stats.total_today">0</p>
                    <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-2">Tổng số ca</p>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: Danh sách chờ --}}
        <div class="col-span-3 bg-gray-950 p-8 flex flex-col overflow-hidden">
            <h2 class="text-2xl font-black text-gray-300 mb-6 uppercase tracking-widest flex items-center gap-3">
                <span>📋 DANH SÁCH BỆNH NHÂN CHỜ KHÁM</span>
                <span class="text-sm bg-blue-900/60 text-blue-300 px-3 py-1 rounded-full border border-blue-800" x-text="waiting.length + ' đang chờ'">0 đang chờ</span>
            </h2>

            <div class="space-y-3 overflow-y-auto flex-1 pr-2" style="max-height: calc(100vh - 200px)">
                <template x-for="(ticket, index) in waiting" :key="ticket.ticket_id">
                    <div class="flex items-center gap-5 bg-gray-800/80 rounded-2xl px-6 py-5 border border-gray-700/60 shadow-md slide-in transition-all duration-300 hover:bg-gray-800"
                         :class="index === 0 ? 'border-2 border-amber-400 bg-gray-800' : ''">

                        {{-- Số thứ tự --}}
                        <div class="w-16 h-16 rounded-full flex items-center justify-center font-black text-3xl flex-shrink-0 shadow-inner"
                             :class="{
                                'bg-red-500/20 text-red-400 border border-red-500/40': ticket.priority === 'emergency',
                                'bg-purple-500/20 text-purple-400 border border-purple-500/40': ticket.priority === 'disabled',
                                'bg-sky-500/20 text-sky-400 border border-sky-500/40': ticket.priority === 'elderly',
                                'bg-gray-700/50 text-gray-300 border border-gray-600/40': ticket.priority === 'normal',
                             }"
                             x-text="ticket.queue_number">
                        </div>

                        {{-- Thông tin bệnh nhân --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold text-xl text-white truncate" x-text="ticket.patient_name">—</p>
                            <div class="flex items-center gap-3 text-gray-400 text-sm mt-1 font-medium">
                                <span class="flex items-center gap-1">
                                    <span x-text="ticket.priority_icon || '👤'"></span>
                                    <span x-text="ticket.priority_label || 'Thường'"></span>
                                </span>
                                <span>•</span>
                                <span>Thời gian chờ ước tính: <strong class="text-amber-400" x-text="'~' + ticket.est_wait_minutes + ' phút'"></strong></span>
                            </div>
                        </div>

                        {{-- Thứ hạng tiếp theo --}}
                        <div x-show="index === 0" class="flex-shrink-0">
                            <span class="bg-amber-400 text-gray-900 px-4 py-2 rounded-xl text-sm font-black uppercase tracking-wider animate-pulse shadow-md">Kế Tiếp</span>
                        </div>
                    </div>
                </template>

                <div x-show="waiting.length === 0" class="text-center py-24 text-gray-500">
                    <p class="text-7xl mb-4">🎉</p>
                    <p class="text-2xl font-bold text-gray-400">Không có bệnh nhân chờ khám</p>
                    <p class="text-gray-600 mt-2">Hàng đợi trống hoàn toàn</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $queueDisplayConfig = [
            'scheduleId' => $schedule->schedule_id,
            'snapshot' => $snapshot,
            'pusherKey' => config('broadcasting.connections.pusher.key'),
            'pusherCluster' => config('broadcasting.connections.pusher.options.cluster', 'mt1'),
        ];
    @endphp
    <script type="application/json" id="queue-display-config">
        @json($queueDisplayConfig)
    </script>
    <script>
    const queueDisplayConfigEl = document.getElementById('queue-display-config');
    const queueDisplayConfig = queueDisplayConfigEl
        ? JSON.parse(queueDisplayConfigEl.textContent || '{}')
        : {};

    window.queueDisplay = function () {
        return {
            current: null,
            waiting: [],
            stats: { total_waiting: 0, total_completed: 0, total_today: 0 },
            currentTime: '',
            scheduleId: queueDisplayConfig.scheduleId,
            initialSnapshot: queueDisplayConfig.snapshot || null,

            init() {
                this.currentTime = new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
                this.applySnapshot(this.initialSnapshot);

                // Cập nhật đồng hồ
                setInterval(() => {
                    this.currentTime = new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
                }, 1000);

                // Lấy dữ liệu lần đầu
                this.refresh();

                // Pusher realtime
                try {
                    const pusherKey = queueDisplayConfig.pusherKey;
                    const pusherCluster = queueDisplayConfig.pusherCluster || 'mt1';

                    if (!pusherKey) {
                        throw new Error('Missing Pusher key');
                    }

                    const pusher = new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true });

                    const channel = pusher.subscribe(`queue.${this.scheduleId}`);

                    channel.bind('queue.updated', () => this.refresh());
                    channel.bind('ticket.called', (data) => {
                        this.playBeep();
                        this.refresh();
                    });
                } catch (e) {
                    console.warn("Pusher configuration incomplete, using polling fallback.", e);
                }

                // Polling fallback mỗi 5 giây cho màn hình TV để đảm bảo luôn chính xác
                setInterval(() => this.refresh(), 5000);
            },

            async refresh() {
                try {
                    const res = await fetch(`/api/queue/${this.scheduleId}/snapshot`);
                    if (res.ok) {
                        const payload = await res.json();
                        const data = payload.data || payload; // fixed: ho tro JSON wrapper {success,message,data}
                        this.applySnapshot(data);
                    }
                } catch (e) {
                    console.error("Failed to fetch queue snapshot", e);
                }
            },

            applySnapshot(data) {
                data = data || {};
                this.current = data.current || null;
                this.waiting = Array.isArray(data.waiting) ? data.waiting : [];
                this.stats = Object.assign(
                    { total_waiting: 0, total_completed: 0, total_today: 0 },
                    data.stats || {}
                );
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    
                    osc.type = 'sine';
                    osc.frequency.value = 660; // Giai điệu nhẹ nhàng lịch sự
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15);

                    // Tiếng bíp thứ 2
                    setTimeout(() => {
                        const ctx2 = new (window.AudioContext || window.webkitAudioContext)();
                        const osc2 = ctx2.createOscillator();
                        const gain2 = ctx2.createGain();
                        osc2.connect(gain2);
                        gain2.connect(ctx2.destination);
                        osc2.type = 'sine';
                        osc2.frequency.value = 880;
                        gain2.gain.setValueAtTime(0.3, ctx2.currentTime);
                        osc2.start();
                        osc2.stop(ctx2.currentTime + 0.25);
                    }, 200);

                } catch (e) {
                    console.warn("Audio playback not allowed or unsupported:", e);
                }
            }
        }
    }
    </script>
    <script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
</body>
</html>
