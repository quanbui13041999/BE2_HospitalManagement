{{--
    Partial form - include trong create.blade.php và edit.blade.php
    Biến cần truyền vào:
      $action  = route url
      $method  = POST | PUT
      $old     = array giá trị cũ (old() hoặc $tracking attributes)
      $tracking = model nếu đang edit (optional)
--}}
<form method="POST" action="{{ $action }}" id="healthForm" novalidate autocomplete="off">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
        <input type="hidden" name="version" value="{{ $tracking->version }}">
    @endif

    {{-- Cảnh báo realtime --}}
    <div id="riskBox"></div>

    <div class="row g-3 mb-3">
        @php
        $fields = [
            ['id'=>'systolic',    'label'=>'Huyết áp tâm thu',    'unit'=>'mmHg', 'icon'=>'bi-arrow-up-circle text-danger',   'type'=>'integer','min'=>50,  'max'=>250,  'hint'=>'50–250'],
            ['id'=>'diastolic',   'label'=>'Huyết áp tâm trương', 'unit'=>'mmHg', 'icon'=>'bi-arrow-down-circle text-warning','type'=>'integer','min'=>30,  'max'=>150,  'hint'=>'30–150'],
            ['id'=>'heart_rate',  'label'=>'Nhịp tim',            'unit'=>'bpm',  'icon'=>'bi-activity text-success',         'type'=>'integer','min'=>30,  'max'=>220,  'hint'=>'30–220','notZero'=>true],
            ['id'=>'spo2',        'label'=>'SpO2',                'unit'=>'%',    'icon'=>'bi-lungs text-info',               'type'=>'integer','min'=>50,  'max'=>100,  'hint'=>'50–100'],
            ['id'=>'weight',      'label'=>'Cân nặng',            'unit'=>'kg',   'icon'=>'bi-person text-secondary',         'type'=>'decimal','min'=>1,   'max'=>500,  'hint'=>'1–500'],
            ['id'=>'blood_sugar', 'label'=>'Đường huyết',         'unit'=>'mg/dL','icon'=>'bi-droplet text-danger',           'type'=>'integer','min'=>20,  'max'=>1000, 'hint'=>'20–1000'],
        ];
        @endphp

        @foreach($fields as $f)
        <div class="col-md-6">
            <label class="form-label fw-semibold small">
                <i class="bi {{ $f['icon'] }} me-1"></i>{{ $f['label'] }}
                <span class="text-danger">*</span>
                <span class="text-muted fw-normal">({{ $f['unit'] }})</span>
            </label>
            <input
                type="text"
                inputmode="{{ $f['type'] === 'decimal' ? 'decimal' : 'numeric' }}"
                id="{{ $f['id'] }}"
                name="{{ $f['id'] }}"
                class="form-control @error($f['id']) is-invalid @enderror"
                value="{{ $errors->any() ? old($f['id']) : ($old[$f['id']] ?? '') }}"
                placeholder="{{ $f['hint'] }}"
                data-type="{{ $f['type'] }}"
                data-min="{{ $f['min'] }}"
                data-max="{{ $f['max'] }}"
                data-label="{{ $f['label'] }}"
                {{ isset($f['notZero']) ? 'data-notzero=1' : '' }}
            >
            <div class="invalid-feedback">{{ $errors->first($f['id']) }}</div>
            <div class="field-warn" id="w_{{ $f['id'] }}"></div>
        </div>
        @endforeach
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold small">
            <i class="bi bi-chat-text text-muted me-1"></i>Triệu chứng <span class="text-muted fw-normal">(tùy chọn)</span>
        </label>
        <textarea id="symptoms" name="symptoms" rows="3" maxlength="1000"
            class="form-control @error('symptoms') is-invalid @enderror"
            placeholder="Mô tả triệu chứng bạn đang gặp phải...">{{ $errors->any() ? old('symptoms') : ($old['symptoms'] ?? '') }}</textarea>
        <div class="d-flex justify-content-between mt-1">
            <div class="invalid-feedback d-block" id="symptomsError">{{ $errors->first('symptoms') }}</div>
            <small class="text-muted ms-auto"><span id="symCount">0</span>/1000</small>
        </div>
    </div>

    <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="bi bi-save2 me-2"></i>{{ $method === 'PUT' ? 'Cập nhật' : 'Lưu nhật ký' }}
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
</form>

<style>
.form-control{border-radius:10px;border:1.5px solid #dee2e6}
.form-control:focus{border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.12)}
.form-control.is-valid{border-color:#198754}
.form-control.is-invalid{border-color:#dc3545}
.field-warn{font-size:.8rem;margin-top:3px;padding:5px 10px;border-radius:8px;display:none}
.field-warn.warning{display:flex;gap:6px;align-items:center;background:#fff3cd;color:#856404}
.field-warn.danger{display:flex;gap:6px;align-items:center;background:#f8d7da;color:#842029}
</style>

<script>
const RULES = {
    systolic:    {type:'integer',min:50,  max:250,  label:'Huyết áp tâm thu'},
    diastolic:   {type:'integer',min:30,  max:150,  label:'Huyết áp tâm trương'},
    heart_rate:  {type:'integer',min:30,  max:220,  label:'Nhịp tim', notZero:true},
    spo2:        {type:'integer',min:50,  max:100,  label:'SpO2'},
    weight:      {type:'decimal',min:1,   max:500,  label:'Cân nặng'},
    blood_sugar: {type:'integer',min:20,  max:1000, label:'Đường huyết'},
};

const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

function validate(name, raw) {
    const rule = RULES[name], v = raw.trim();
    if (!v) return null;
    if (rule.type === 'integer' && !/^-?\d+$/.test(v))   return {ok:false, msg:`${rule.label} chỉ được nhập số nguyên`};
    if (rule.type === 'decimal' && !/^-?\d+(\.\d+)?$/.test(v)) return {ok:false, msg:`${rule.label} chỉ được nhập số`};
    const n = parseFloat(v);
    if (n < 0)              return {ok:false, msg:`Không được nhập số âm`};
    if (rule.notZero && !n) return {ok:false, msg:`${rule.label} không được bằng 0`};
    if (n < rule.min)       return {ok:false, msg:`${rule.label} tối thiểu ${rule.min}`};
    if (n > rule.max)       return {ok:false, msg:`${rule.label} tối đa ${rule.max}`};
    return {ok:true};
}

function applyState(input, result) {
    input.classList.remove('is-valid','is-invalid');
    const fb = input.nextElementSibling;
    if (!result) return;
    if (result.ok) { input.classList.add('is-valid'); }
    else { input.classList.add('is-invalid'); if (fb) { fb.textContent = result.msg; fb.style.display = 'block'; } }
}

function setWarn(name, level, msg) {
    const el = document.getElementById('w_' + name);
    if (!el) return;
    el.className = `field-warn ${level}`;
    el.innerHTML = `<i class="bi bi-exclamation-${level==='danger'?'triangle':'circle'}-fill"></i>${msg}`;
}
function clearWarn(name) { const el = document.getElementById('w_' + name); if (el) { el.className='field-warn'; el.textContent=''; } }

const checkRisk = debounce(async () => {
    const body = {};
    ['systolic','diastolic','heart_rate','spo2','blood_sugar'].forEach(f => {
        const v = document.getElementById(f)?.value.trim();
        if (v && /^\d+$/.test(v)) body[f] = +v;
    });
    if (!Object.keys(body).length) return;
    try {
        const res = await fetch('{{ route("health-tracking.check-risk") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
            body:JSON.stringify(body)
        });
        const {warnings} = await res.json();
        Object.keys(RULES).forEach(f => clearWarn(f));
        const box = document.getElementById('riskBox');
        box.innerHTML = '';
        if (warnings?.length) {
            warnings.forEach(w => setWarn(w.field, w.level, w.message));
            const lvl = warnings.some(w => w.level==='danger') ? 'danger' : 'warning';
            box.innerHTML = `<div class="alert alert-${lvl} alert-dismissible d-flex gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>${warnings.map(w=>w.message).join('<br>')}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        }
    } catch {}
}, 600);

Object.keys(RULES).forEach(name => {
    const input = document.getElementById(name);
    if (!input) return;
    const isDecimal = RULES[name].type === 'decimal';

    input.addEventListener('keydown', e => {
        const ok = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Enter'];
        if (ok.includes(e.key)) return;
        if (isDecimal && e.key === '.') return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });
    input.addEventListener('paste', e => {
        const p = e.clipboardData.getData('text');
        if (!(isDecimal ? /^\d*\.?\d*$/ : /^\d+$/).test(p)) e.preventDefault();
    });
    const dv = debounce(v => { applyState(input, validate(name, v)); checkRisk(); }, 300);
    input.addEventListener('input', e => dv(e.target.value));
    input.addEventListener('blur',  e => applyState(input, validate(name, e.target.value)));
});

// Symptoms guard
const symp = document.getElementById('symptoms'), sc = document.getElementById('symCount');
const symptomError = document.getElementById('symptomsError');
const symptomPattern = /^[\p{L}\s]*$/u;

function validateSymptoms() {
    const value = symp.value.trim();
    sc.textContent = symp.value.length;
    symp.classList.remove('is-valid', 'is-invalid');

    if (!value) {
        symptomError.textContent = '';
        return true;
    }

    if (symp.value.length > 1000) {
        symp.classList.add('is-invalid');
        symptomError.textContent = 'Triệu chứng không được vượt quá 1000 ký tự.';
        return false;
    }

    if (!symptomPattern.test(value)) {
        symp.classList.add('is-invalid');
        symptomError.textContent = 'Triệu chứng chỉ được nhập chữ và khoảng trắng, không nhập số hoặc ký tự đặc biệt.';
        return false;
    }

    symp.classList.add('is-valid');
    symptomError.textContent = '';
    return true;
}

symp.addEventListener('input', validateSymptoms);
symp.addEventListener('paste', () => setTimeout(validateSymptoms, 0));
validateSymptoms();

// Submit guard
document.getElementById('healthForm').addEventListener('submit', function(e) {
    let bad = false;
    Object.keys(RULES).forEach(name => {
        const input = document.getElementById(name);
        if (!input) return;
        const v = input.value.trim();
        if (!v) {
            input.classList.add('is-invalid');
            const fb = input.nextElementSibling;
            if (fb) { fb.textContent = `${RULES[name].label} là bắt buộc.`; fb.style.display='block'; }
            bad = true; return;
        }
        const r = validate(name, v);
        if (r && !r.ok) { applyState(input, r); bad = true; }
    });
    if (!validateSymptoms()) bad = true;
    if (bad) {
        e.preventDefault();
        this.querySelector('.is-invalid')?.scrollIntoView({behavior:'smooth',block:'center'});
    }
});
</script>
