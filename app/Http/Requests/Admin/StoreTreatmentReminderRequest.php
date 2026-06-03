<?php

namespace App\Http\Requests\Admin;

use App\Models\MedicalRecord;
use App\Models\TreatmentReminder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTreatmentReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'record_id' => $this->record_id === '' ? null : $this->record_id,
        ]);
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $currentReminder = $this->currentReminder();

        return [
            'user_id' => $isCreate
                ? [
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('users', 'user_id')->where('role_id', 3)->where('status', 1),
                ]
                : [
                    'required',
                    'integer',
                    Rule::in([$currentReminder?->user_id ?? $this->integer('user_id')]),
                ],
            'record_id' => $isCreate
                ? ['nullable', 'integer', 'min:1', Rule::exists('medical_records', 'record_id')]
                : ['prohibited'],
            'reminder_type' => ['required', Rule::in(['medicine', 'instruction'])],
            'remind_at' => ['required', 'date', 'after:now'],
            'message' => [
                'required',
                'string',
                'min:5',
                'max:255',
                'regex:/\A(?! )(?!.* \z)[\pL\pM\pN .,:;()\/+\-%–—]+\z/u',
            ],
            'reminder_snapshot' => $isCreate ? ['prohibited'] : ['required', 'string', 'size:64'],
            'is_sent' => ['prohibited'],
            'created_at' => ['prohibited'],
            'reminder_id' => ['prohibited'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->isMethod('post') || ! $this->filled('record_id') || ! $this->filled('user_id')) {
                return;
            }

            $belongsToPatient = MedicalRecord::where('record_id', $this->integer('record_id'))
                ->where('patient_id', $this->integer('user_id'))
                ->exists();

            if (! $belongsToPatient) {
                $validator->errors()->add('record_id', 'Hồ sơ bệnh án không thuộc bệnh nhân đã chọn.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Vui lòng chọn bệnh nhân.',
            'user_id.exists' => 'Bệnh nhân không tồn tại hoặc đang bị khóa.',
            'user_id.in' => 'Không được đổi bệnh nhân của nhắc nhở bằng F12.',
            'record_id.exists' => 'Hồ sơ bệnh án không tồn tại.',
            'record_id.prohibited' => 'Không được tự thêm hồ sơ bệnh án ở màn hình sửa.',
            'reminder_type.required' => 'Vui lòng chọn loại nhắc nhở.',
            'reminder_type.in' => 'Loại nhắc nhở không hợp lệ.',
            'remind_at.required' => 'Vui lòng nhập thời gian nhắc.',
            'remind_at.date' => 'Thời gian nhắc không hợp lệ.',
            'remind_at.after' => 'Thời gian nhắc phải lớn hơn thời gian hiện tại.',
            'message.required' => 'Vui lòng nhập nội dung nhắc nhở.',
            'message.min' => 'Nội dung nhắc nhở phải có ít nhất 5 ký tự.',
            'message.max' => 'Nội dung nhắc nhở tối đa 255 ký tự.',
            'message.regex' => 'Nội dung nhắc nhở chỉ được nhập chữ tiếng Việt, số, khoảng trắng và các dấu . , ; : ( ) / + - % – —; không được có khoảng trắng đầu hoặc cuối.',
            'reminder_snapshot.required' => 'Dữ liệu đã cũ, vui lòng tải lại trang trước khi lưu.',
            'reminder_snapshot.size' => 'Dữ liệu kiểm tra chỉnh sửa không hợp lệ, vui lòng tải lại trang.',
            'is_sent.prohibited' => 'Không được tự ý gửi trạng thái nhắc nhở.',
            'created_at.prohibited' => 'Ngày tạo do hệ thống tự ghi nhận.',
            'reminder_id.prohibited' => 'Không được tự ý gửi mã nhắc nhở.',
        ];
    }

    private function currentReminder(): ?TreatmentReminder
    {
        $reminderId = $this->route('reminder');

        return is_numeric($reminderId)
            ? TreatmentReminder::find((int) $reminderId)
            : null;
    }
}
