<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RehabExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Chỉ admin mới được tạo/sửa bài tập
        $user = auth()->user();

        return $user && method_exists($user, 'isAdmin')
            ? $user->isAdmin()
            : (int) ($user->role_id ?? 0) === 1;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['required', 'string'],
            'category'         => ['required', 'in:co-xuong-khop,than-kinh-dot-quy,chan-thuong-the-thao,ho-hap-tim-mach'],
            'phase'            => ['required', 'in:cap-tinh,phuc-hoi,duy-tri'],
            'thumbnail'        => [
                $isUpdate ? 'nullable' : 'nullable', // ảnh không bắt buộc
                'file',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120', // 5 MB
            ],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:240'],
            'status'           => ['required', 'in:published,draft'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'Vui lòng nhập tiêu đề bài tập.',
            'content.required'  => 'Vui lòng nhập nội dung hướng dẫn.',
            'category.required' => 'Vui lòng chọn nhóm bệnh lý.',
            'category.in'       => 'Nhóm bệnh lý không hợp lệ.',
            'phase.required'    => 'Vui lòng chọn giai đoạn điều trị.',
            'phase.in'          => 'Giai đoạn điều trị không hợp lệ.',
            'thumbnail.mimes'   => 'Chỉ chấp nhận ảnh định dạng JPG, PNG, GIF hoặc WEBP.',
            'thumbnail.max'     => 'Dung lượng ảnh không được vượt quá 5 MB.',
            'status.required'   => 'Vui lòng chọn trạng thái.',
            'status.in'         => 'Trạng thái không hợp lệ.',
        ];
    }
}
