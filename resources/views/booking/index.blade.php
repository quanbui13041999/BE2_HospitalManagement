<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Khám Của Tôi</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0c1a2e 0%, #0f3460 50%, #0c1a2e 100%);
            padding: 2.5rem 1.5rem;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        .container {
            max-width: 860px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 2rem;
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #38bdf8, #34d399);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo svg {
            width: 22px;
            height: 22px;
        }

        .header-text h2 {
            font-size: 20px;
            font-weight: 600;
            color: #f1f5f9;
        }

        .header-text p {
            font-size: 13px;
            color: #64748b;
            margin-top: 2px;
        }

        .alert-success {
            background: rgba(52, 211, 153, 0.1);
            border: 0.5px solid rgba(52, 211, 153, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #6ee7b7;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            background: rgba(255, 255, 255, 0.04);
            border: 0.5px solid rgba(255, 255, 255, 0.10);
            border-radius: 16px;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.08);
        }

        thead th {
            padding: 14px 16px;
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-align: left;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.05);
            transition: background 0.15s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        tbody td {
            padding: 14px 16px;
            font-size: 14px;
            color: #cbd5e1;
            vertical-align: middle;
        }

        .doctor-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #34d399);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: #0c1a2e;
            flex-shrink: 0;
        }

        .doctor-name {
            font-size: 14px;
            color: #f1f5f9;
            font-weight: 500;
        }

        .date-cell .date {
            font-size: 14px;
            color: #f1f5f9;
            font-weight: 500;
        }

        .date-cell .time {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border: 0.5px solid rgba(245, 158, 11, 0.25);
        }
        .badge-pending .badge-dot { background: #f59e0b; }

        .badge-confirmed {
            background: rgba(52, 211, 153, 0.12);
            color: #34d399;
            border: 0.5px solid rgba(52, 211, 153, 0.25);
        }
        .badge-confirmed .badge-dot { background: #34d399; }

        .badge-cancelled {
            background: rgba(239, 68, 68, 0.10);
            color: #f87171;
            border: 0.5px solid rgba(239, 68, 68, 0.2);
        }
        .badge-cancelled .badge-dot { background: #ef4444; }

        .badge-done {
            background: rgba(148, 163, 184, 0.10);
            color: #94a3b8;
            border: 0.5px solid rgba(148, 163, 184, 0.2);
        }
        .badge-done .badge-dot { background: #64748b; }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: rgba(56, 189, 248, 0.1);
            border: 0.5px solid rgba(56, 189, 248, 0.25);
            border-radius: 8px;
            color: #38bdf8;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Be Vietnam Pro', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-edit:hover {
            background: rgba(56, 189, 248, 0.18);
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            background: rgba(239, 68, 68, 0.08);
            border: 0.5px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            color: #f87171;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-cancel:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #475569;
            font-size: 14px;
        }

        .empty-state svg {
            width: 40px;
            height: 40px;
            margin: 0 auto 12px;
            color: #334155;
            display: block;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 100;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: #0f2040;
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 360px;
            text-align: center;
        }

        .modal-icon {
            width: 52px;
            height: 52px;
            background: rgba(239, 68, 68, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .modal h3 {
            font-size: 17px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 8px;
        }

        .modal p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .modal-btns {
            display: flex;
            gap: 10px;
        }

        .modal-cancel-btn {
            flex: 1;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-cancel-btn:hover { background: rgba(255, 255, 255, 0.09); }

        .modal-confirm-btn {
            flex: 1;
            padding: 10px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .modal-confirm-btn:hover { opacity: 0.88; }

        @media (max-width: 640px) {
            thead th:nth-child(2) { display: none; }
            tbody td:nth-child(2) { display: none; }
            .avatar { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="header-text">
            <h2>Lịch Khám Của Tôi</h2>
            <p>Quản lý và theo dõi các lịch hẹn của bạn</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Bác sĩ</th>
                    <th>Dịch vụ</th>
                    <th>Ngày khám</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $item)
                <tr>
                    <td>
                        <div class="doctor-cell">
                            <div class="avatar">
                                {{ strtoupper(substr($item->doctor_name, -2)) }}
                            </div>
                            <span class="doctor-name">BS. {{ $item->doctor_name }}</span>
                        </div>
                    </td>
                    <td>{{ $item->service_name }}</td>
                    <td>
                        <div class="date-cell">
                            <div class="date">{{ $item->work_date }}</div>
                            <div class="time">{{ $item->start_time }}</div>
                        </div>
                    </td>
                    <td>
                        @php
                            $statusMap = [
                                'Chờ xác nhận' => 'badge-pending',
                                'Đã xác nhận'  => 'badge-confirmed',
                                'Đã hủy'       => 'badge-cancelled',
                                'Hoàn thành'   => 'badge-done',
                            ];
                            $badgeClass = $statusMap[$item->status] ?? 'badge-pending';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            <span class="badge-dot"></span>
                            {{ $item->status }}
                        </span>
                    </td>
                    <td>
                        @if($item->status != 'Đã hủy' && $item->status != 'Hoàn thành')
                            <div class="actions">
                                <a href="{{ route('booking.edit', $item->appointment_id) }}" class="btn-edit">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    Dời lịch
                                </a>
                                 <button type="submit" onclick="return confirm('Bạn có chắc muốn hủy?')" style="color:red; border:none; background:none; cursor:pointer;">Hủy</button>
                            </div>
                        @else
                            <span style="font-size: 12px; color: #334155;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Bạn chưa có lịch khám nào.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal xác nhận hủy -->
<div class="modal-overlay" id="cancelModal">
    <div class="modal">
        <div class="modal-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3>Xác nhận hủy lịch</h3>
        <p>Bạn có chắc muốn hủy lịch khám này không? Hành động này không thể hoàn tác.</p>
        <div class="modal-btns">
            <button class="modal-cancel-btn" onclick="closeModal()">Không, giữ lại</button>
            <form id="cancelForm" method="POST" style="flex: 1;">
                @csrf
                <button type="submit" class="modal-confirm-btn" style="width: 100%;">Xác nhận hủy</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(action) {
        document.getElementById('cancelForm').action = action;
        document.getElementById('cancelModal').classList.add('active');
    }
    function closeModal() {
        document.getElementById('cancelModal').classList.remove('active');
    }
    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>

</body>
</html>