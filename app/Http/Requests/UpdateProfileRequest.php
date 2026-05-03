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
        return [
            'full_name'     => ['required', 'string', 'max:100'],
            'email'         => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore(Auth::id(), 'user_id')
            ],
            'phone'         => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]+$/'],
            'address'       => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'        => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'     => 'Họ và tên không được để trống.',
            'full_name.max'          => 'Họ và tên không vượt quá 100 ký tự.',
            'email.required'         => 'Email không được để trống.',
            'email.email'            => 'Email không hợp lệ.',
            'email.unique'           => 'Email đã được sử dụng bởi tài khoản khác.',
            'phone.regex'            => 'Số điện thoại chỉ chứa số và ký tự +, -.',
            'date_of_birth.date'     => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before'   => 'Ngày sinh phải trước ngày hôm nay.',
            'gender.in'              => 'Giới tính không hợp lệ.',
            'avatar.image'           => 'File phải là hình ảnh.',
            'avatar.mimes'           => 'Chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'avatar.max'             => 'Ảnh không được vượt quá 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $this->merge(['phone' => preg_replace('/\s+/', '', $this->phone)]);
        }
    }
}
