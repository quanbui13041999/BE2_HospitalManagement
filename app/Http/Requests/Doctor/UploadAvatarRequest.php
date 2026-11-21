<?php

namespace App\Http\Requests\Doctor;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = Auth::id();
        $user = $userId ? User::find($userId) : null;

        return $user?->isAdmin() ?? false;
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
            'avatar.image' => 'Tệp phải là hình ảnh.',
            'avatar.mimes' => 'Tệp phải là định dạng JPEG, PNG hoặc WebP.',
            'avatar.max' => 'Kích thước tệp không được vượt quá 5MB.',
        ];
    }
}
