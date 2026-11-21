<div class="card shadow-sm" style="border-radius:var(--card-radius)">
    <div class="card-body p-4">
        <div class="section-header">📈 BIỂU ĐỒ TUÂN THỦ 7 NGÀY</div>
        
        <div class="d-flex justify-content-between align-items-end mb-3" style="height: 120px; padding: 0 10px;">
            @foreach($weeklyStats as $label => $day)
                <div class="text-center" style="flex: 1;">
                    <div class="weekly-bar justify-content-center">
                        <div class="bar {{ $day['compliant'] ? 'full' : ($day['total'] > 0 ? '' : 'empty') }}" 
                             style="height: {{ max($day['rate'], 10) }}%;"
                             title="{{ $label }}: {{ $day['rate'] }}%">
                        </div>
                    </div>
                    <div class="mt-2" style="font-size: 11px; font-weight: 600; color: #6b7280;">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        
        <div class="d-flex gap-3 justify-content-center" style="font-size: 11px; color: #6b7280;">
            <div class="d-flex align-items-center gap-1">
                <span style="width:8px; height:8px; background:var(--primary); border-radius:2px;"></span> Đã hoàn thành
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width:8px; height:8px; background:var(--primary); opacity:.4; border-radius:2px;"></span> Chưa xong
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width:8px; height:8px; background:var(--gray-200); border-radius:2px;"></span> Không có lịch
            </div>
        </div>
    </div>
</div>
