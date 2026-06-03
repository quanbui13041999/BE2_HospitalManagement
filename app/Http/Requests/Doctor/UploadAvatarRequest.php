<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Vui lòng chọn tệp ảnh.',
            'avatar.image'    => 'Tệp phải là hình ảnh.',
            'avatar.mimes'    => 'Tệp phải là định dạng JPEG, PNG hoặc WebP.',
            'avatar.max'      => 'Kích thước tệp không được vượt quá 5MB.',
        ];
    }
}
