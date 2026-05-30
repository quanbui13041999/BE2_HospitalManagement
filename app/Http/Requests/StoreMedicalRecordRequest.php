<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'      => 'required|integer|exists:users,user_id',
            'patient_name'    => 'required|string|max:100',
            'patient_code'    => 'nullable|string|max:30',
            'doctor_id'       => 'required|integer|exists:users,user_id',
            'doctor_name'     => 'required|string|max:100',
            'appointment_id'  => 'nullable|integer',
            'exam_date'       => 'required|date',
            'exam_time'       => 'nullable|date_format:H:i',
            'visit_type'      => 'required|in:Tái khám,Khám mới,Cấp cứu',
            'chief_complaint' => 'required|string|max:1000',

            // Chỉ số sinh tồn
            'vitals'                        => 'required|array',
            'vitals.blood_pressure'         => 'required|string|max:20',
            'vitals.bp_status'              => 'nullable|in:normal,high,low',
            'vitals.heart_rate'             => 'required|numeric|min:0|max:300',
            'vitals.hr_status'              => 'nullable|in:normal,high,low',
            'vitals.temperature'            => 'required|numeric|min:30|max:42',
            'vitals.temp_status'            => 'nullable|in:normal,high,low',
            'vitals.spo2'                   => 'required|numeric|min:0|max:100',
            'vitals.spo2_status'            => 'nullable|in:normal,high,low',
            'vitals.weight'                 => 'required|numeric|min:0|max:500',
            'vitals.blood_sugar'            => 'nullable|numeric|min:0',
            'vitals.sugar_status'           => 'nullable|in:normal,high,low',

            // Dị ứng
            'allergies'                  => 'nullable|array',
            'allergies.*.allergen'       => 'nullable|string|max:200',
            'allergies.*.severity'       => 'nullable|string|max:50',
            'allergies.*.reaction'       => 'nullable|string|max:200',

            // Chẩn đoán
            'diagnoses'                         => 'required|array|min:1',
            'diagnoses.*.diagnosis_name'        => 'required|string|max:300',
            'diagnoses.*.icd_code'              => 'nullable|string|max:20',
            'diagnoses.*.diagnosis_type'        => 'required|in:primary,secondary,complication',
            'diagnoses.*.note'                  => 'nullable|string',

            // Đơn thuốc
            'prescriptions'                     => 'nullable|array',
            'prescriptions.*.drug_name'         => 'nullable|string|max:200',
            'prescriptions.*.dosage'            => 'nullable|string|max:100',
            'prescriptions.*.instructions'      => 'nullable|string|max:500',
            'prescriptions.*.duration_days'     => 'nullable|integer|min:1',
            'prescriptions.*.quantity'          => 'nullable|integer|min:1',

            // Chỉ định xét nghiệm
            'orders'                    => 'nullable|array',
            'orders.*.order_type'       => 'nullable|in:lab,imaging,other',
            'orders.*.order_name'       => 'nullable|string|max:300',
            'orders.*.description'      => 'nullable|string',

            // Tập đính kèm
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
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
            'vitals.heart_rate.required'   => 'Vui lòng nhập nhịp tim.',
            'vitals.temperature.required'  => 'Vui lòng nhập nhiệt độ.',
            'vitals.spo2.required'         => 'Vui lòng nhập chỉ số SpO2.',
            'vitals.weight.required'       => 'Vui lòng nhập cân nặng.',
            'diagnoses.required'           => 'Vui lòng thêm ít nhất 1 chẩn đoán.',
            'diagnoses.min'                => 'Vui lòng thêm ít nhất 1 chẩn đoán.',
        ];
    }

    protected function prepareForValidation(): void
    {
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
}