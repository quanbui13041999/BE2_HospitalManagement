<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return self::rulesFor(Auth::id());
    }

    public static function rulesFor(?int $userId): array
    {
        return [
            'profile_snapshot' => ['nullable', 'string', 'size:64'],
            'full_name'     => ['required', 'string', 'max:100', 'regex:/^(?=.*\pL)[\pL\s.\'-]+$/u'],
            'email'         => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($userId, 'user_id')
            ],
            'phone'         => ['nullable', 'string', 'size:10', 'regex:/^0\d{9}$/'],
            'address'       => ['nullable', 'string', 'max:255', 'regex:/^(?=.*\pL)[\pL\pN\s,.\-\/]+$/u'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'        => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'avatar'        => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return self::messagesFor();
    }

    public static function messagesFor(): array
    {
        return [
            'full_name.required'     => 'Họ và tên không được để trống.',
            'full_name.max'          => 'Họ và tên không vượt quá 100 ký tự.',
            'full_name.regex'        => 'Họ và tên chỉ được nhập chữ cái, khoảng trắng, dấu chấm, dấu gạch nối hoặc dấu nháy.',
            'email.required'         => 'Email không được để trống.',
            'email.email'            => 'Email không hợp lệ.',
            'email.max'              => 'Email không vượt quá 100 ký tự.',
            'email.unique'           => 'Email đã được sử dụng bởi tài khoản khác.',
            'phone.size'             => 'Số điện thoại phải đúng 10 chữ số.',
            'phone.regex'            => 'Số điện thoại phải đúng 10 chữ số, bắt đầu bằng số 0 và không chứa chữ hoặc ký tự khác.',
            'address.max'            => 'Địa chỉ không vượt quá 255 ký tự.',
            'address.regex'          => 'Địa chỉ chỉ được nhập chữ, số, khoảng trắng và các dấu phân cách thông dụng.',
            'date_of_birth.date'     => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before'   => 'Ngày sinh phải trước ngày hôm nay.',
            'gender.in'              => 'Giới tính không hợp lệ.',
            'avatar.file'            => 'Ảnh đại diện phải là một file hợp lệ.',
            'avatar.image'           => 'File phải là hình ảnh.',
            'avatar.mimes'           => 'Chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'avatar.max'             => 'Ảnh không được vượt quá 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $this->merge(['phone' => trim((string) $this->phone)]);
        }
    }
}
