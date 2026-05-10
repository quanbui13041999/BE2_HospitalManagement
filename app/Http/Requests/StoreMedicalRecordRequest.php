<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'      => 'sometimes|nullable|integer|exists:users,user_id',
            'patient_name'    => 'sometimes|required|string|max:100',
            'patient_code'    => 'nullable|string|max:30',
            'doctor_id'       => 'sometimes|nullable|integer|exists:users,user_id',
            'doctor_name'     => 'sometimes|required|string|max:100',
            'appointment_id'  => 'nullable|integer',
            'exam_date'       => 'sometimes|required|date',
            'exam_time'       => 'nullable',
            'visit_type'      => 'sometimes|required|in:Tái khám,Khám mới,Cấp cứu',
            'chief_complaint' => 'sometimes|required|string|max:1000',

            // Vitals
            'vitals' => 'sometimes|required|array',
            'vitals.blood_pressure' => 'sometimes|required|string|max:20',
            'vitals.bp_status' => 'nullable|in:normal,high,low',
            'vitals.heart_rate' => 'sometimes|required|numeric|min:0|max:300',
            'vitals.hr_status' => 'nullable|in:normal,high,low',
            'vitals.temperature' => 'sometimes|required|numeric|min:30|max:42',
            'vitals.temp_status' => 'nullable|in:normal,high,low',
            'vitals.spo2' => 'sometimes|required|numeric|min:0|max:100',
            'vitals.spo2_status' => 'nullable|in:normal,high,low',
            'vitals.weight' => 'sometimes|required|numeric|min:0|max:500',
            'vitals.blood_sugar' => 'nullable|numeric|min:0',
            'vitals.sugar_status' => 'nullable|in:normal,high,low',

            // Allergies
            'allergies' => 'nullable|array',
            'allergies.*.allergen' => 'nullable|string|max:200',
            'allergies.*.severity' => 'nullable|string|max:50',
            'allergies.*.reaction' => 'nullable|string|max:200',

            // Diagnoses
            'diagnoses' => 'sometimes|required|array|min:1',
            'diagnoses.*.diagnosis_name' => 'sometimes|required|string|max:300',
            'diagnoses.*.icd_code' => 'nullable|string|max:20',
            'diagnoses.*.diagnosis_type' => 'sometimes|required|in:primary,secondary,complication',
            'diagnoses.*.note' => 'nullable|string',

            // Prescriptions
            'prescriptions' => 'nullable|array',
            'prescriptions.*.drug_name' => 'nullable|string|max:200',
            'prescriptions.*.dosage' => 'nullable|string|max:100',
            'prescriptions.*.instructions' => 'nullable|string|max:500',
            'prescriptions.*.duration_days' => 'nullable|integer|min:1',
            'prescriptions.*.quantity' => 'nullable|integer|min:1',

            // Orders
            'orders' => 'nullable|array',
            'orders.*.order_type' => 'nullable|in:lab,imaging,other',
            'orders.*.order_name' => 'nullable|string|max:300',
            'orders.*.description' => 'nullable|string',

            // Attachments
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_name.required' => 'Vui lòng nhập tên bệnh nhân.',
            'exam_date.required' => 'Vui lòng chọn ngày khám.',
            'doctor_name.required' => 'Vui lòng nhập tên bác sĩ.',
            'visit_type.required' => 'Vui lòng chọn loại khám.',
            'chief_complaint.required' => 'Vui lòng nhập lý do đến khám.',
            'diagnoses.required' => 'Vui lòng thêm ít nhất 1 chẩn đoán.',
            'vitals.required' => 'Vui lòng nhập đầy đủ chỉ số sinh tồn.',
            'vitals.blood_pressure.required' => 'Vui lòng nhập huyết áp.',
            'vitals.heart_rate.required' => 'Vui lòng nhập nhịp tim.',
            'vitals.temperature.required' => 'Vui lòng nhập nhiệt độ.',
            'vitals.spo2.required' => 'Vui lòng nhập chỉ số SpO2.',
            'vitals.weight.required' => 'Vui lòng nhập cân nặng.',
        ];
    }
}