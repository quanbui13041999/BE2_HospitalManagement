<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpsertEmergencyContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Thay bằng Gate/Policy nếu cần
    }

     public function rules(): array
    {
        return [
            'contacts'                     => ['required', 'array', 'max:3'],
 
            'contacts.*.name'              => ['nullable', 'string', 'max:100'],
            'contacts.*.relationship'      => ['nullable', 'string', 'in:Vợ/Chồng,Mẹ,Cha,Con,Anh/Chị em,Người giám hộ,Khác'],
            'contacts.*.email'             => ['nullable', 'email', 'max:100'],
            'contacts.*.lab_notifications' => ['nullable', 'boolean'],
            'contacts.*.recovery_updates'  => ['nullable', 'boolean'],
 
            // Gộp regex + custom closure vào cùng 1 key — tránh duplicate array key
            'contacts.*.phone'             => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\s\+\-\(\)]+$/',
                function (string $attribute, mixed $value, Closure $fail) {
                    $index = explode('.', $attribute)[1];
                    $name  = $this->input("contacts.{$index}.name");
 
                    if (filled($name) && empty($value)) {
                        $fail('Vui lòng nhập số điện thoại cho liên hệ ưu tiên ' . ($index + 1) . '.');
                    }
                },
            ],
        ];
    }
 
    public function messages(): array
    {
        return [
            'contacts.max'                 => 'Chỉ được thêm tối đa 3 người thân.',
            'contacts.*.name.max'          => 'Họ tên không được vượt quá 100 ký tự.',
            'contacts.*.relationship.in'   => 'Mối quan hệ không hợp lệ.',
            'contacts.*.phone.regex'       => 'Số điện thoại chỉ được chứa chữ số và ký tự +, -, (, ).',
            'contacts.*.phone.max'         => 'Số điện thoại không được vượt quá 20 ký tự.',
            'contacts.*.email.email'       => 'Email không đúng định dạng.',
            'contacts.*.email.max'         => 'Email không được vượt quá 100 ký tự.',
        ];
    }
 
    public function attributes(): array
    {
        $attrs = [];
 
        foreach (range(0, 2) as $i) {
            $priority = $i + 1;
            $attrs["contacts.{$i}.name"]         = "Họ tên (ưu tiên {$priority})";
            $attrs["contacts.{$i}.relationship"] = "Mối quan hệ (ưu tiên {$priority})";
            $attrs["contacts.{$i}.phone"]        = "Số điện thoại (ưu tiên {$priority})";
            $attrs["contacts.{$i}.email"]        = "Email (ưu tiên {$priority})";
        }
 
        return $attrs;
    }
 
    /**
     * Chuẩn hoá dữ liệu trước khi validate.
     * Checkbox không được gửi khi không tick → ép về false.
     */
    protected function prepareForValidation(): void
    {
        $contacts = $this->input('contacts', []);
 
        foreach ($contacts as $i => &$contact) {
            $contact['lab_notifications'] = isset($contact['lab_notifications']) ? (bool) $contact['lab_notifications'] : false;
            $contact['recovery_updates']  = isset($contact['recovery_updates'])  ? (bool) $contact['recovery_updates']  : false;
        }
        unset($contact);
 
        $this->merge(['contacts' => $contacts]);
    }
}