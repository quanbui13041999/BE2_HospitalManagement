<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nhật ký sức khỏe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --page-bg: #eef3f8;
            --ink: #132238;
            --muted: #667085;
            --line: #e3e9f2;
            --primary: #1266f1;
            --primary-soft: #e9f1ff;
            --success: #138a58;
            --warning: #b97900;
            --danger: #d92d3f;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--ink);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .app-shell {
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--line);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            font-size: 1.25rem;
        }

        .page-wrap {
            max-width: 1460px;
            margin: 0 auto;
            padding: 28px 24px 56px;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .page-title {
            margin: 0;
            font-size: clamp(1.55rem, 2vw, 2rem);
            font-weight: 800;
            letter-spacing: 0;
        }

        .page-subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            max-width: none;
            white-space: nowrap;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .summary-tile {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .summary-label {
            color: var(--muted);
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .summary-value {
            margin-top: 6px;
            font-size: 1.75rem;
            line-height: 1;
            font-weight: 800;
        }

        .summary-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .summary-tile.normal .summary-icon { background: #e8f7ef; color: var(--success); }
        .summary-tile.warning .summary-icon { background: #fff4d7; color: var(--warning); }
        .summary-tile.danger .summary-icon { background: #ffe7ea; color: var(--danger); }

        .filter-panel,
        .table-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
        }

        .filter-panel {
            padding: 16px;
            margin-bottom: 18px;
        }

        .form-label {
            color: #344054;
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-select,
        .form-control {
            min-height: 42px;
            border-color: #d7deea;
            border-radius: 8px;
        }

        .table-panel {
            overflow: hidden;
        }

        .table-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
        }

        .table-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
        }

        .table-subtitle {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .health-table {
            margin: 0;
        }

        .health-table thead th {
            background: #f8fafc;
            color: #667085;
            border-bottom: 1px solid var(--line);
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 14px 18px;
            white-space: nowrap;
        }

        .health-table tbody td {
            padding: 18px;
            vertical-align: middle;
            border-color: var(--line);
        }

        .health-table tbody tr {
            transition: background-color .15s ease;
        }

        .health-table tbody tr:hover {
            background: #fbfdff;
        }

        .date-chip {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            min-width: 92px;
        }

        .date-chip strong,
        .metric strong {
            color: #101828;
            font-weight: 800;
        }

        .date-chip span,
        .metric span {
            color: var(--muted);
            font-size: .86rem;
        }

        .patient-name {
            font-weight: 700;
        }

        .risk-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .78rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .risk-pill.normal {
            background: #e8f7ef;
            color: var(--success);
        }

        .risk-pill.warning {
            background: #fff4d7;
            color: var(--warning);
        }

        .risk-pill.danger {
            background: #ffe7ea;
            color: var(--danger);
        }

        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .empty-state {
            padding: 56px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 16px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 700;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        @media (max-width: 992px) {
            .page-subtitle {
                white-space: normal;
            }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .page-head,
            .table-top {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 576px) {
            .page-wrap {
                padding: 20px 12px 40px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .topbar .container-fluid {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <nav class="topbar">
        <div class="container-fluid px-4 py-3 d-flex justify-content-between gap-3">
            <a class="d-flex align-items-center gap-3 text-decoration-none text-dark" href="{{ route('health-tracking.index') }}">
                <span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>
                <span>
                    <span class="d-block fw-bold fs-5">Health Tracker</span>
                    <span class="d-block text-muted small">Nhật ký sức khỏe chủ động</span>
                </span>
            </a>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-circle me-1"></i> Hồ sơ
                </a>
                @if(auth()->user()->isPatient())
                    <a href="{{ route('health-tracking.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Thêm chỉ số
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <main class="page-wrap">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <section class="page-head">
            <div>
                <div class="eyebrow">Theo dõi sức khỏe</div>
                <h1 class="page-title">Nhật ký sức khỏe chủ động</h1>
                <p class="page-subtitle">
                    Tổng hợp các chỉ số huyết áp, nhịp tim, SpO2, cân nặng và đường huyết theo từng lần ghi nhận.
                </p>
            </div>
          
        </section>

        <section class="summary-grid">
            <div class="summary-tile">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="summary-label">Tổng bản ghi</div>
                        <div class="summary-value">{{ $summary['total'] ?? $trackings->total() }}</div>
                    </div>
                    <span class="summary-icon"><i class="bi bi-journal-medical"></i></span>
                </div>
            </div>
            <div class="summary-tile normal">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="summary-label">Bình thường</div>
                        <div class="summary-value">{{ $summary['normal'] ?? 0 }}</div>
                    </div>
                    <span class="summary-icon"><i class="bi bi-check2-circle"></i></span>
                </div>
            </div>
            <div class="summary-tile warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="summary-label">Cảnh báo</div>
                        <div class="summary-value">{{ $summary['warning'] ?? 0 }}</div>
                    </div>
                    <span class="summary-icon"><i class="bi bi-exclamation-circle"></i></span>
                </div>
            </div>
            <div class="summary-tile danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="summary-label">Nguy hiểm</div>
                        <div class="summary-value">{{ $summary['danger'] ?? 0 }}</div>
                    </div>
                    <span class="summary-icon"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
            </div>
        </section>

        <section class="filter-panel">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label" for="risk_level">Mức độ</label>
                    <select id="risk_level" name="risk_level" class="form-select">
                        <option value="">Tất cả mức độ</option>
                        <option value="normal"  {{ request('risk_level')=='normal'  ?'selected':'' }}>Bình thường</option>
                        <option value="warning" {{ request('risk_level')=='warning' ?'selected':'' }}>Cảnh báo</option>
                        <option value="danger"  {{ request('risk_level')=='danger'  ?'selected':'' }}>Nguy hiểm</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label" for="date_from">Từ ngày</label>
                    <input id="date_from" type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label" for="date_to">Đến ngày</label>
                    <input id="date_to" type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-lg-3 col-md-6 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="bi bi-search me-1"></i> Lọc
                    </button>
                    <a href="{{ route('health-tracking.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </section>

        <section class="table-panel">
            <div class="table-top">
                <div>
                    <h2 class="table-title">Danh sách ghi nhận</h2>
                    <p class="table-subtitle">Hiển thị {{ $trackings->firstItem() ?? 0 }}-{{ $trackings->lastItem() ?? 0 }} trong {{ $trackings->total() }} bản ghi</p>
                </div>
            </div>

            @if($trackings->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-journal-plus"></i></div>
                    <h3 class="h5 fw-bold">Chưa có nhật ký nào</h3>
                    @if(auth()->user()->isPatient())
                        <a href="{{ route('health-tracking.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Tạo nhật ký đầu tiên
                        </a>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table health-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                @if(!auth()->user()->isPatient())<th>Bệnh nhân</th>@endif
                                <th>Huyết áp</th>
                                <th>Nhịp tim</th>
                                <th>SpO2</th>
                                <th>Cân nặng</th>
                                <th>Đường huyết</th>
                                <th>Mức độ</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackings as $t)
                                @php
                                    $b = $t->risk_badge;
                                    $riskIcon = match($t->risk_level) {
                                        'danger' => 'bi-exclamation-triangle-fill',
                                        'warning' => 'bi-exclamation-circle-fill',
                                        default => 'bi-check-circle-fill',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="date-chip">
                                            <strong>{{ $t->created_at->format('d/m/Y') }}</strong>
                                            <span><i class="bi bi-clock me-1"></i>{{ $t->created_at->format('H:i') }}</span>
                                        </span>
                                    </td>
                                    @if(!auth()->user()->isPatient())
                                        <td><span class="patient-name">{{ $t->patient->full_name ?? 'N/A' }}</span></td>
                                    @endif
                                    <td>
                                        <div class="metric">
                                            <strong>{{ $t->systolic }}/{{ $t->diastolic }}</strong>
                                            <span>mmHg</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="metric">
                                            <strong>{{ $t->heart_rate }}</strong>
                                            <span>bpm</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="metric">
                                            <strong>{{ $t->spo2 }}</strong>
                                            <span>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="metric">
                                            <strong>{{ $t->weight }}</strong>
                                            <span>kg</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="metric">
                                            <strong>{{ $t->blood_sugar }}</strong>
                                            <span>mg/dL</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="risk-pill {{ $t->risk_level }}">
                                            <i class="bi {{ $riskIcon }}"></i>{{ $b['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('health-tracking.show', $t) }}" class="btn btn-outline-primary btn-sm action-btn" title="Xem">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @can('update', $t)
                                                <a href="{{ route('health-tracking.edit', $t) }}" class="btn btn-outline-secondary btn-sm action-btn" title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $t)
                                                <button class="btn btn-outline-danger btn-sm action-btn" title="Xóa"
                                                    onclick="confirmDelete('{{ route('health-tracking.destroy', $t) }}','{{ csrf_token() }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-top">{{ $trackings->links() }}</div>
            @endif
        </section>
    </main>
</div>

<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3-fill text-danger fs-1 d-block mb-3"></i>
                <h6 class="fw-bold">Xác nhận xóa nhật ký này?</h6>
                <p class="text-muted small mb-4">Thao tác này không thể hoàn tác.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger" id="delConfirmBtn">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let delUrl, delToken;
const delModal = new bootstrap.Modal(document.getElementById('delModal'));
function confirmDelete(url, token) { delUrl = url; delToken = token; delModal.show(); }
document.getElementById('delConfirmBtn').addEventListener('click', () => {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = delUrl;
    f.innerHTML = `<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="${delToken}">`;
    document.body.appendChild(f); f.submit();
});
</script>
</body>
</html>
