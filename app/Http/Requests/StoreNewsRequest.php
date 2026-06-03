<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->normalizeText($this->input('title')),
        ]); /* fixed: trim ca khoang trang full-width truoc khi validate */
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255', 'not_regex:/\A[\s\x{3000}]*\z/u'],
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
            'title.not_regex'   => 'Tiêu đề không được chỉ chứa khoảng trắng.',
            'content.required'  => 'Vui lòng nhập nội dung bài viết.',
            'content.max'       => 'Nội dung bài viết tối đa 10000 ký tự.',
            'category.required' => 'Vui lòng chọn danh mục.',
            'thumbnail.image'   => 'File thumbnail phải là hình ảnh.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $plainContent = $this->normalizeText(strip_tags(html_entity_decode((string) $this->input('content'), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($plainContent === '') {
                $validator->errors()->add('content', 'Nội dung không được chỉ chứa khoảng trắng.');
            }
        }); /* fixed: TinyMCE co the gui HTML rong nhu <p>&nbsp;</p> */
    }

    private function normalizeText(mixed $value): string
    {
        $value = str_replace("\xC2\xA0", ' ', (string) $value);
        $value = preg_replace('/^[\s\x{3000}]+|[\s\x{3000}]+$/u', '', $value) ?? '';

        return $value;
    }
}
