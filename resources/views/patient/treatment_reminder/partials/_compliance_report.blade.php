<div class="card shadow-sm" style="border-radius:var(--card-radius)">
    <div class="card-body p-4">
        <div class="section-header">📊 BÁO CÁO TUÂN THỦ THÁNG {{ now()->format('m/Y') }}</div>
        
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="report-metric">
                    <div class="pct green">{{ $monthStats['compliance_rate'] }}%</div>
                    <div class="desc">Tổng quan</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="report-metric">
                    <div class="pct blue">{{ $monthStats['medicine_rate'] }}%</div>
                    <div class="desc">Uống thuốc</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="report-metric">
                    <div class="pct purple">{{ $monthStats['exercise_rate'] }}%</div>
                    <div class="desc">Vận động</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="report-metric">
                    <div class="pct orange">100%</div>
                    <div class="desc">Tái khám</div>
                </div>
            </div>
        </div>
    </div>
</div>
