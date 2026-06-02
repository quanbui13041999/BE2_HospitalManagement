<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RehabExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isAdmin')
            ? $user->isAdmin()
            : (int) ($user->role_id ?? 0) === 1;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'duration_minutes' => $this->input('duration_minutes') === '' ? null : $this->input('duration_minutes'),
        ]);
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:120',
                'regex:/\A[\pL\pM]+(?: [\pL\pM]+)*\z/u',
            ],
            'content' => [
                'required',
                'string',
                'min:10',
                'max:5000',
                'regex:/\A[\pL\pM]+(?: [\pL\pM]+)*\z/u',
            ],
            'category' => [
                'required',
                Rule::in(['co-xuong-khop', 'than-kinh-dot-quy', 'chan-thuong-the-thao', 'ho-hap-tim-mach']),
            ],
            'phase' => ['required', Rule::in(['cap-tinh', 'phuc-hoi', 'duy-tri'])],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048',
            ],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:240'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'rehab_snapshot' => $isUpdate ? ['required', 'string', 'size:64'] : ['prohibited'],
            'id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'view_count' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề bài tập.',
            'title.min' => 'Tiêu đề phải có ít nhất 3 ký tự.',
            'title.max' => 'Tiêu đề tối đa 120 ký tự.',
            'title.regex' => 'Tiêu đề chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.',
            'content.required' => 'Vui lòng nhập nội dung hướng dẫn.',
            'content.min' => 'Nội dung hướng dẫn phải có ít nhất 10 ký tự.',
            'content.max' => 'Nội dung hướng dẫn tối đa 5000 ký tự.',
            'content.regex' => 'Nội dung hướng dẫn chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.',
            'category.required' => 'Vui lòng chọn nhóm bệnh lý.',
            'category.in' => 'Nhóm bệnh lý không hợp lệ.',
            'phase.required' => 'Vui lòng chọn giai đoạn điều trị.',
            'phase.in' => 'Giai đoạn điều trị không hợp lệ.',
            'thumbnail.image' => 'Tệp tải lên phải là ảnh.',
            'thumbnail.mimes' => 'Chỉ chấp nhận ảnh định dạng JPG, JPEG, PNG hoặc WEBP.',
            'thumbnail.mimetypes' => 'Tệp tải lên phải là ảnh hợp lệ.',
            'thumbnail.max' => 'Dung lượng ảnh không được vượt quá 2 MB.',
            'duration_minutes.integer' => 'Thời lượng tập phải là số nguyên dương.',
            'duration_minutes.min' => 'Thời lượng tập phải lớn hơn 0.',
            'duration_minutes.max' => 'Thời lượng tập tối đa 240 phút.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'rehab_snapshot.required' => 'Dữ liệu đã cũ, vui lòng tải lại trang trước khi lưu.',
            'rehab_snapshot.size' => 'Dữ liệu kiểm tra chỉnh sửa không hợp lệ, vui lòng tải lại trang.',
            'id.prohibited' => 'Không được tự ý gửi mã bài tập.',
            'created_by.prohibited' => 'Người tạo do hệ thống tự ghi nhận.',
            'view_count.prohibited' => 'Lượt xem do hệ thống tự ghi nhận.',
            'created_at.prohibited' => 'Ngày tạo do hệ thống tự ghi nhận.',
            'updated_at.prohibited' => 'Ngày cập nhật do hệ thống tự ghi nhận.',
        ];
    }

}
