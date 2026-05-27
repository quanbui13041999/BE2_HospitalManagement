<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HealthTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPatient();
    }

    public function rules(): array
    {
        $rules = [
            'systolic'    => ['required', 'integer', 'min:50',  'max:250'],
            'diastolic'   => ['required', 'integer', 'min:30',  'max:150'],
            'heart_rate'  => ['required', 'integer', 'min:30',  'max:220', 'not_in:0'],
            'spo2'        => ['required', 'integer', 'min:50',  'max:100'],
            'weight'      => ['required', 'numeric', 'min:1',   'max:500'],
            'blood_sugar' => ['required', 'integer', 'min:20',  'max:1000'],
            'symptoms'    => ['nullable', 'string',  'max:1000'],
        ];

        // Thêm version khi update (optimistic locking)
        if ($this->isMethod('PUT')) {
            $rules['version'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'systolic.required'    => 'Vui lòng nhập huyết áp tâm thu.',
            'systolic.integer'     => 'Huyết áp tâm thu chỉ được nhập số nguyên.',
            'systolic.min'         => 'Huyết áp tâm thu tối thiểu là :min mmHg.',
            'systolic.max'         => 'Huyết áp tâm thu tối đa là :max mmHg.',
            'diastolic.required'   => 'Vui lòng nhập huyết áp tâm trương.',
            'diastolic.integer'    => 'Huyết áp tâm trương chỉ được nhập số nguyên.',
            'diastolic.min'        => 'Huyết áp tâm trương tối thiểu là :min mmHg.',
            'diastolic.max'        => 'Huyết áp tâm trương tối đa là :max mmHg.',
            'heart_rate.required'  => 'Vui lòng nhập nhịp tim.',
            'heart_rate.integer'   => 'Nhịp tim chỉ được nhập số nguyên.',
            'heart_rate.min'       => 'Nhịp tim quá thấp (tối thiểu :min bpm).',
            'heart_rate.max'       => 'Nhịp tim quá cao (tối đa :max bpm).',
            'heart_rate.not_in'    => 'Nhịp tim không được bằng 0.',
            'spo2.required'        => 'Vui lòng nhập chỉ số SpO2.',
            'spo2.integer'         => 'SpO2 chỉ được nhập số nguyên.',
            'spo2.min'             => 'SpO2 tối thiểu là :min%.',
            'spo2.max'             => 'SpO2 không thể vượt quá :max%.',
            'weight.required'      => 'Vui lòng nhập cân nặng.',
            'weight.numeric'       => 'Cân nặng chỉ được nhập số.',
            'weight.min'           => 'Cân nặng tối thiểu là :min kg.',
            'weight.max'           => 'Cân nặng tối đa là :max kg.',
            'blood_sugar.required' => 'Vui lòng nhập đường huyết.',
            'blood_sugar.integer'  => 'Đường huyết chỉ được nhập số nguyên.',
            'blood_sugar.min'      => 'Đường huyết tối thiểu là :min mg/dL.',
            'blood_sugar.max'      => 'Đường huyết tối đa là :max mg/dL.',
            'symptoms.max'         => 'Triệu chứng không được vượt quá :max ký tự.',
            'version.required'     => 'Thiếu thông tin phiên bản bản ghi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->symptoms) {
            $this->merge(['symptoms' => trim(strip_tags($this->symptoms))]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors(),
            ], 422));
        }
        parent::failedValidation($validator);
    }
}
