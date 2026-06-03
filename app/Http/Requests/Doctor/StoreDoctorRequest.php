<?php

namespace App\Http\Requests\Doctor;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = Auth::id();
        $user = $userId ? User::find($userId) : null;

        return $user?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim($this->input('full_name', '')),
            'bio' => trim($this->input('bio', '')),
            'avatar_url' => trim($this->input('avatar_url', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:100|regex:/\S/',
            'user_id' => 'nullable|integer|exists:users,user_id|unique:doctors,user_id',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'department_id' => 'required|integer|exists:departments,department_id',
            'experience' => 'nullable|integer|min:0|max:60',
            'price' => 'nullable|numeric|min:0',
            'avatar_url' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000|regex:/^(?![\s]*$)/',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'full_name.regex' => 'Họ và tên không được chỉ chứa khoảng trắng.',
            'user_id.unique' => 'Tài khoản này đã được liên kết với một bác sĩ khác.',
            'user_id.exists' => 'Tài khoản không tồn tại.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'department_id.required' => 'Vui lòng chọn khoa.',
            'department_id.exists' => 'Khoa không tồn tại.',
            'experience.integer' => 'Kinh nghiệm phải là số nguyên.',
            'experience.min' => 'Kinh nghiệm không được âm.',
            'experience.max' => 'Kinh nghiệm không được vượt quá 60 năm.',
            'price.numeric' => 'Giá khám phải là số.',
            'price.min' => 'Giá khám không được âm.',
            'avatar_url.max' => 'URL ảnh không được vượt quá 255 ký tự.',
            'bio.max' => 'Giới thiệu không được vượt quá 2000 ký tự.',
            'bio.regex' => 'Giới thiệu không được chỉ chứa khoảng trắng.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
