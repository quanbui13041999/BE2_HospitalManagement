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
        return self::rulesFor();
    }

    public static function rulesFor(): array
    {
        return [
            'profile_snapshot' => ['nullable', 'string', 'size:64'],
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return self::messagesFor();
    }

    public static function messagesFor(): array
    {
        return [
            'current_password.required'  => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'      => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'           => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.max'           => 'Mật khẩu mới không được vượt quá 255 ký tự.',
            'new_password.confirmed'     => 'Xác nhận mật khẩu không khớp.',
            'new_password.different'     => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
