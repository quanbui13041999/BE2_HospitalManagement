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
            'profile_snapshot' => ['required', 'string', 'size:64'],
            'full_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/\A(?![Bb][Ss][\pL\pM])(?:[Bb][Ss](?:\.\s?|\s))?[\pL\pM]+(?: [\pL\pM]+)*\z/u'],
            'email' => [
                'required',
                'email:rfc',
                'max:100',
                Rule::unique('users', 'email')->ignore(Auth::id(), 'user_id'),
            ],
            'phone' => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => ['nullable', 'string', 'max:150', 'regex:/\A[\pL\pM\pN,.\-\/]+(?: [\pL\pM\pN,.\-\/]+)*\z/u'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_snapshot.required' => 'Dữ liệu hồ sơ đã hết hiệu lực. Vui lòng tải lại trang.',
            'profile_snapshot.size' => 'Dữ liệu hồ sơ đã hết hiệu lực. Vui lòng tải lại trang.',
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'full_name.max' => 'Họ và tên không vượt quá 80 ký tự.',
            'full_name.regex' => 'Họ và tên chỉ được nhập chữ tiếng Việt, có thể bắt đầu bằng BS. nếu là bác sĩ, và đúng một khoảng trắng giữa các từ.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không vượt quá 100 ký tự.',
            'email.unique' => 'Email đã được sử dụng bởi tài khoản khác.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 hoặc +84 và chỉ chứa chữ số.',
            'address.max' => 'Địa chỉ không vượt quá 150 ký tự.',
            'address.regex' => 'Địa chỉ chỉ được nhập chữ, số và các dấu , . - /.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hôm nay.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Chỉ chấp nhận định dạng jpg, jpeg, png.',
            'avatar.max' => 'Ảnh không được vượt quá 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $address = $this->input('address');

        $this->merge([
            'full_name' => (string) $this->full_name,
            'email' => (string) $this->email,
            'phone' => $this->phone ?: null,
            'address' => ($address === null || $address === '') ? null : (string) $address,
        ]);
    }
}
