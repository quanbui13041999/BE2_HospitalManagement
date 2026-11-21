<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ToggleInstructionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instruction_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('treatment_home_instructions', 'id')
                    ->where('user_id', Auth::id())
                    ->where('is_active', 1),
            ],
            'expected_state' => ['required', 'boolean'],
            'instruction_text' => ['prohibited'],
            'detail' => ['prohibited'],
            'checked_at' => ['prohibited'],
            'checked_date' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'instruction_id.required' => 'Thiếu mã hướng dẫn điều trị.',
            'instruction_id.integer' => 'Mã hướng dẫn điều trị không hợp lệ.',
            'instruction_id.min' => 'Mã hướng dẫn điều trị không hợp lệ.',
            'instruction_id.exists' => 'Hướng dẫn không tồn tại hoặc không thuộc tài khoản của bạn.',
            'expected_state.required' => 'Trạng thái hiện tại của checkbox không hợp lệ, vui lòng tải lại trang.',
            'expected_state.boolean' => 'Trạng thái hiện tại của checkbox không hợp lệ, vui lòng tải lại trang.',
            'instruction_text.prohibited' => 'Không được sửa nội dung hướng dẫn từ màn hình bệnh nhân.',
            'detail.prohibited' => 'Không được sửa chi tiết hướng dẫn từ màn hình bệnh nhân.',
            'checked_at.prohibited' => 'Thời gian hoàn thành do hệ thống tự ghi nhận.',
            'checked_date.prohibited' => 'Ngày hoàn thành do hệ thống tự ghi nhận.',
        ];
    }
}
