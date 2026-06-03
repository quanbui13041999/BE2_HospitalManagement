<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:255'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
                'different:current_password',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.max' => 'Mật khẩu hiện tại không hợp lệ.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.max' => 'Mật khẩu mới không được vượt quá 72 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'new_password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'new_password.regex' => 'Mật khẩu mới phải có chữ thường, chữ hoa và số.',
        ];
    }
}
