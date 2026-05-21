<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id'       => 'required|exists:Invoices,invoice_id',
            'payment_method'   => 'required|in:QR,ATM,MoMo,ZaloPay,Counter',
            'amount'           => 'required|numeric|min:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.exists'      => 'Hóa đơn không tồn tại.',
            'payment_method.in'      => 'Phương thức thanh toán không hợp lệ.',
            'amount.min'             => 'Số tiền thanh toán phải ít nhất 1.000đ.',
        ];
    }
}
