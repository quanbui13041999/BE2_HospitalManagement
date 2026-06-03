<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'content'      => 'required|string|max:10000',
            'category'     => 'required|in:Thông báo,Sức khỏe,Chương trình,Hướng dẫn,Khẩn cấp',
            'thumbnail'    => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'version'      => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'Vui lòng nhập tiêu đề bài viết.',
            'content.required'  => 'Vui lòng nhập nội dung bài viết.',
            'content.max'       => 'Nội dung bài viết tối đa 10000 ký tự.',
            'category.required' => 'Vui lòng chọn danh mục.',
            'thumbnail.image'   => 'File thumbnail phải là hình ảnh.',
        ];
    }
}
