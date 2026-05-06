<?php

namespace App\Services\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentService
{
    // Các phương thức thanh toán được hỗ trợ
    const PAYMENT_METHODS = ['QR', 'ATM', 'MoMo', 'ZaloPay', 'Counter'];
    const PAYMENT_STATUSES = ['Chờ xử lý', 'Thành công', 'Thất bại', 'Hoàn tiền'];

    public function __construct(protected PaymentRepository $repo) {}

    // ----------------------------------------------------------------
    // Data builders cho view
    // ----------------------------------------------------------------

    public function buildIndexData(int $invoiceId): array
    {
        $invoice = $this->repo->getInvoiceWithDetails($invoiceId);

        abort_if(!$invoice, 404, 'Hóa đơn không tồn tại.');

        return [
            'invoice'            => $invoice,
            'recentTransactions' => $this->repo->recentTransactions(),
            'todayStats'         => $this->repo->todayStats(),
            'paymentMethods'     => self::PAYMENT_METHODS,
        ];
    }

    public function buildListData(Request $request): array
    {
        $filters = $request->only(['from_date', 'to_date', 'status', 'method']);

        return [
            'payments'       => $this->repo->paginatedPayments($filters),
            'statuses'       => self::PAYMENT_STATUSES,
            'methods'        => self::PAYMENT_METHODS,
            'todayStats'     => $this->repo->todayStats(),
        ];
    }

    // ----------------------------------------------------------------
    // Xử lý thanh toán
    // ----------------------------------------------------------------

    /**
     * Khởi tạo giao dịch, trả về Payment + QR data (nếu cần).
     */
    public function initiatePayment(array $data): array
    {
        $ref     = strtoupper(Str::random(12));
        $payment = $this->repo->createPayment(array_merge($data, ['transaction_ref' => $ref]));

        $result = ['payment' => $payment, 'ref' => $ref];

        // Với QR: tạo nội dung QR để frontend render
        if ($data['payment_method'] === 'QR') {
            $result['qr_content'] = $this->buildVietQrContent($payment);
        }

        return $result;
    }

    /**
     * Xác nhận thanh toán thành công (webhook từ cổng thanh toán).
     */
    public function confirmPayment(int $paymentId, string $ref): bool
    {
        $updated = $this->repo->updateStatus($paymentId, 'Thành công', $ref);

        if ($updated) {
            // Đánh dấu hóa đơn đã thanh toán
            Payment::find($paymentId)?->invoice?->update(['status' => 'Đã thanh toán']);
        }

        return $updated;
    }

    /**
     * Hủy / thất bại giao dịch.
     */
    public function failPayment(int $paymentId): bool
    {
        return $this->repo->updateStatus($paymentId, 'Thất bại');
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    /**
     * Tạo nội dung VietQR (chuỗi EMV).
     * Trong thực tế, gọi API VietQR; ở đây trả về chuỗi mẫu.
     */
    private function buildVietQrContent(Payment $payment): string
    {
        return sprintf(
            'HOSPITAL|%s|%d|%s',
            $payment->transaction_ref,
            (int) $payment->amount,
            'Thanh toan hoa don ' . $payment->invoice_id
        );
    }
}
