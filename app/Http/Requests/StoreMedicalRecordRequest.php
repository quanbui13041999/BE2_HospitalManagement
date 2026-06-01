<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array((int) (Auth::user()?->role_id ?? 0), [1, 2], true);
    }

    public function rules(): array
    {
        return [
            'patient_id'      => [
                'required',
                'integer',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->where('role_id', 3)),
            ],
            'patient_name'    => ['required', 'string', 'max:100', 'regex:/\A[\pL\s.\'-]+\z/u'],
            'patient_code'    => ['nullable', 'string', 'max:30', 'regex:/\ABN\d{1,10}\z/u'],
            'doctor_id'       => [
                'required',
                'integer',
                (int) (Auth::user()?->role_id ?? 0) === 2
                    ? Rule::in([(int) Auth::user()->user_id])
                    : Rule::exists('users', 'user_id')->where(fn ($query) => $query->where('role_id', 2)),
            ],
            'doctor_name'     => ['required', 'string', 'max:100', 'regex:/\A[\pL\s.\'-]+\z/u'],
            'appointment_id'  => ['nullable', 'integer', Rule::exists('appointments', 'appointment_id')],
            'exam_date'       => 'required|date',
            'exam_time'       => 'nullable|date_format:H:i',
            'visit_type'      => 'required|in:Tái khám,Khám mới,Cấp cứu',
            'chief_complaint' => ['required', 'string', 'max:1000', 'regex:/\A[\pL\s.,;:()\/+\-%]+\z/u'],
            'record_id'       => 'prohibited',
            'record_code'     => 'prohibited',
            'status'          => 'prohibited',
            'status_note'     => 'prohibited',
            'created_at'      => 'prohibited',
            'updated_at'      => 'prohibited',
            'record_snapshot' => 'prohibited',

            // Chỉ số sinh tồn
            'vitals'                        => 'required|array',
            'vitals.blood_pressure'         => ['required', 'string', 'max:20', 'regex:/\A\d{2,3}\/\d{2,3}\z/'],
            'vitals.bp_status'              => 'nullable|in:normal,high,low',
            'vitals.heart_rate'             => 'required|integer|min:1|max:300',
            'vitals.hr_status'              => 'nullable|in:normal,high,low',
            'vitals.temperature'            => ['required', 'numeric', 'min:36', 'max:40', 'regex:/\A\d+(\.\d{1,2})?\z/'],
            'vitals.temp_status'            => 'nullable|in:normal,high,low',
            'vitals.spo2'                   => 'required|integer|min:50|max:100',
            'vitals.spo2_status'            => 'nullable|in:normal,high,low',
            'vitals.weight'                 => ['required', 'numeric', 'min:1', 'max:500', 'regex:/\A\d+(\.\d{1,2})?\z/'],
            'vitals.blood_sugar'            => ['nullable', 'numeric', 'min:1', 'max:1000', 'regex:/\A\d+(\.\d{1,2})?\z/'],
            'vitals.sugar_status'           => 'nullable|in:normal,high,low',

            // Dị ứng
            'allergies'                  => 'nullable|array',
            'allergies.*.allergen'       => ['nullable', 'string', 'max:100', 'regex:/\A[\pL\s\/\-]+\z/u'],
            'allergies.*.allergen_custom' => ['nullable', 'string', 'max:100', 'regex:/\A[\pL\s\/\-]+\z/u'],
            'allergies.*.severity'       => 'nullable|in:Nhẹ,Vừa,Nặng',
            'allergies.*.reaction'       => ['nullable', 'string', 'max:200', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],

            // Chẩn đoán
            'diagnoses'                         => 'required|array|min:1',
            'diagnoses.*.diagnosis_name'        => ['required', 'string', 'max:150', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'diagnoses.*.diagnosis_name_custom' => ['nullable', 'string', 'max:150', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'diagnoses.*.icd_code'              => ['nullable', 'string', 'max:20', 'regex:/\A[A-Z][0-9]{1,2}(\.[0-9A-Z]{1,2})?\z/'],
            'diagnoses.*.diagnosis_type'        => 'required|in:primary,secondary,complication',
            'diagnoses.*.note'                  => ['nullable', 'string', 'max:500', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],

            // Đơn thuốc
            'prescriptions'                     => 'nullable|array',
            'prescriptions.*.drug_name'         => ['nullable', 'string', 'max:120', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'prescriptions.*.drug_name_custom'  => ['nullable', 'string', 'max:120', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'prescriptions.*.dosage'            => ['nullable', 'string', 'max:100', 'regex:/\A[\pL\pN\s.,;:()\/+\-%]+\z/u'],
            'prescriptions.*.instructions'      => ['nullable', 'string', 'max:300', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'prescriptions.*.duration_days'     => 'nullable|integer|min:1|max:365',
            'prescriptions.*.quantity'          => 'nullable|integer|min:1|max:10000',

            // Chỉ định xét nghiệm
            'orders'                    => 'nullable|array',
            'orders.*.order_type'       => 'nullable|in:lab,imaging,other',
            'orders.*.order_name'       => ['nullable', 'string', 'max:150', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'orders.*.order_name_custom' => ['nullable', 'string', 'max:150', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],
            'orders.*.description'      => ['nullable', 'string', 'max:500', 'regex:/\A[\pL\s.,;:()\/+\-]+\z/u'],

            // Tập đính kèm
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required'          => 'Vui lòng chọn bệnh nhân.',
            'patient_id.exists'            => 'Bệnh nhân không tồn tại.',
            'patient_name.required'        => 'Vui lòng nhập tên bệnh nhân.',
            'doctor_id.required'           => 'Vui lòng chọn bác sĩ.',
            'doctor_name.required'         => 'Vui lòng nhập tên bác sĩ.',
            'exam_date.required'           => 'Vui lòng chọn ngày khám.',
            'exam_date.date'               => 'Ngày khám không hợp lệ.',
            'visit_type.required'          => 'Vui lòng chọn loại khám.',
            'chief_complaint.required'     => 'Vui lòng nhập lý do đến khám.',
            'vitals.required'              => 'Vui lòng nhập đầy đủ chỉ số sinh tồn.',
            'vitals.blood_pressure.required' => 'Vui lòng nhập huyết áp.',
            'vitals.blood_pressure.regex'    => 'Huyết áp phải đúng dạng 120/80.',
            'vitals.heart_rate.required'   => 'Vui lòng nhập nhịp tim.',
            'vitals.heart_rate.integer'    => 'Nhịp tim phải là số nguyên.',
            'vitals.heart_rate.min'        => 'Nhịp tim phải lớn hơn 0.',
            'vitals.heart_rate.max'        => 'Nhịp tim không được vượt quá 300 bpm.',
            'vitals.temperature.required'  => 'Vui lòng nhập nhiệt độ.',
            'vitals.temperature.min'       => 'Nhiệt độ chỉ hợp lệ từ 36°C đến 40°C.',
            'vitals.temperature.max'       => 'Nhiệt độ chỉ hợp lệ từ 36°C đến 40°C.',
            'vitals.spo2.required'         => 'Vui lòng nhập chỉ số SpO2.',
            'vitals.spo2.integer'          => 'SpO2 phải là số nguyên.',
            'vitals.spo2.min'              => 'SpO2 tối thiểu là 50%.',
            'vitals.spo2.max'              => 'SpO2 không được vượt quá 100%.',
            'vitals.weight.required'       => 'Vui lòng nhập cân nặng.',
            'vitals.weight.min'            => 'Cân nặng phải lớn hơn 0 kg.',
            'vitals.weight.max'            => 'Cân nặng không được vượt quá 500 kg.',
            'vitals.blood_sugar.min'       => 'Đường huyết phải lớn hơn 0.',
            'vitals.blood_sugar.max'       => 'Đường huyết không được vượt quá 1000.',
            'diagnoses.required'           => 'Vui lòng thêm ít nhất 1 chẩn đoán.',
            'diagnoses.min'                => 'Vui lòng thêm ít nhất 1 chẩn đoán.',
            'regex'                        => 'Dữ liệu nhập sai định dạng hoặc có ký tự không hợp lệ.',
            'prohibited'                   => 'Không được gửi dữ liệu hệ thống từ trình duyệt.',
            'attachments.*.mimes'          => 'Tập đính kèm chỉ được nhận file PDF, JPG, JPEG hoặc PNG.',
            'attachments.*.max'            => 'Tập đính kèm tối đa 10MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $patient = $this->filled('patient_id')
                ? User::where('user_id', $this->integer('patient_id'))->where('role_id', 3)->first()
                : null;
            $doctor = $this->filled('doctor_id')
                ? User::where('user_id', $this->integer('doctor_id'))->where('role_id', 2)->first()
                : null;
            $doctorName = $doctor
                ? (Doctor::where('user_id', $doctor->user_id)->value('full_name') ?: $doctor->full_name)
                : null;

            if ($this->filled('patient_id') && ! $patient) {
                $validator->errors()->add('patient_id', 'Benh nhan khong hop le.');
            }

            if ($this->filled('doctor_id') && ! $doctor) {
                $validator->errors()->add('doctor_id', 'Bac si khong hop le.');
            }

            if ((int) (Auth::user()?->role_id ?? 0) === 2 && $this->integer('doctor_id') !== (int) Auth::user()->user_id) {
                $validator->errors()->add('doctor_id', 'Bac si chi duoc tao phieu kham cua chinh minh.');
            }

            if ($patient && $this->filled('patient_name') && $this->normalizeName($this->input('patient_name')) !== $this->normalizeName($patient->full_name)) {
                $validator->errors()->add('patient_name', 'Ten benh nhan khong khop voi ma benh nhan da chon.');
            }

            if ($doctorName && $this->filled('doctor_name') && $this->normalizeName($this->input('doctor_name')) !== $this->normalizeName($doctorName)) {
                $validator->errors()->add('doctor_name', 'Ten bac si khong khop voi ma bac si da chon.');
            }

            if (! $this->filled('appointment_id')) {
                return;
            }

            $appointment = Appointment::with('schedule.doctor')->find($this->integer('appointment_id'));

            if (! $appointment) {
                return;
            }

            if ((int) $appointment->user_id !== $this->integer('patient_id')) {
                $validator->errors()->add('appointment_id', 'Lich hen khong thuoc benh nhan da chon.');
            }

            $appointmentDoctorId = (int) ($appointment->schedule?->doctor?->user_id ?? 0);
            if ($appointmentDoctorId && $appointmentDoctorId !== $this->integer('doctor_id')) {
                $validator->errors()->add('doctor_id', 'Bac si khong khop voi lich hen da chon.');
            }

            if ($appointment->status !== 'Hoàn thành') {
                $validator->errors()->add('appointment_id', 'Lich hen chua hoan thanh nen chua the tao phieu kham.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeTextInputs();
        $this->applyCustomRows();

        // Chuyển đổi patient_id và doctor_id sang integer
        if ($this->has('patient_id') && !empty($this->patient_id)) {
            $this->merge(['patient_id' => (int) $this->patient_id]);
        }
        
        if ($this->has('doctor_id') && !empty($this->doctor_id)) {
            $this->merge(['doctor_id' => (int) $this->doctor_id]);
        }
        
        // Đảm bảo diagnoses là mảng
        if (!$this->has('diagnoses') || empty($this->diagnoses)) {
            $this->merge(['diagnoses' => []]);
        }
        
        // Lọc bỏ dòng rỗng trong diagnoses
        if ($this->has('diagnoses') && is_array($this->diagnoses)) {
            $filtered = array_values(array_filter($this->diagnoses, function ($item) {
                return !empty(trim($item['diagnosis_name'] ?? ''));
            }));
            $this->merge(['diagnoses' => $filtered]);
        }
        
        // Lọc bỏ dòng rỗng trong allergies
        if ($this->has('allergies') && is_array($this->allergies)) {
            $filtered = array_values(array_filter($this->allergies, function ($item) {
                return !empty(trim($item['allergen'] ?? ''));
            }));
            $this->merge(['allergies' => empty($filtered) ? null : $filtered]);
        }
        
        // Lọc bỏ dòng rỗng trong prescriptions
        if ($this->has('prescriptions') && is_array($this->prescriptions)) {
            $filtered = array_values(array_filter($this->prescriptions, function ($item) {
                return !empty(trim($item['drug_name'] ?? ''));
            }));
            $this->merge(['prescriptions' => empty($filtered) ? null : $filtered]);
        }
        
        // Lọc bỏ dòng rỗng trong orders
        if ($this->has('orders') && is_array($this->orders)) {
            $filtered = array_values(array_filter($this->orders, function ($item) {
                return !empty(trim($item['order_name'] ?? ''));
            }));
            $this->merge(['orders' => empty($filtered) ? null : $filtered]);
        }
    }

    private function normalizeName(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $value)), 'UTF-8');
    }

    private function normalizeTextInputs(): void
    {
        $data = $this->all();

        array_walk_recursive($data, function (&$value): void {
            if (is_string($value)) {
                $value = preg_replace('/\s+/u', ' ', trim(strip_tags($value)));
                if ($value === '') {
                    $value = null;
                }
            }
        });

        if (isset($data['diagnoses']) && is_array($data['diagnoses'])) {
            foreach ($data['diagnoses'] as &$diagnosis) {
                if (! empty($diagnosis['icd_code'])) {
                    $diagnosis['icd_code'] = strtoupper($diagnosis['icd_code']);
                }
            }
        }

        $this->replace($data);
    }

    private function applyCustomRows(): void
    {
        $data = $this->all();

        foreach (['allergies' => 'allergen', 'diagnoses' => 'diagnosis_name', 'prescriptions' => 'drug_name', 'orders' => 'order_name'] as $group => $field) {
            if (! isset($data[$group]) || ! is_array($data[$group])) {
                continue;
            }

            foreach ($data[$group] as &$row) {
                if (($row[$field] ?? null) === 'other' && ! empty($row[$field . '_custom'])) {
                    $row[$field] = $row[$field . '_custom'];
                }
            }
        }

        $this->replace($data);
    }
}
