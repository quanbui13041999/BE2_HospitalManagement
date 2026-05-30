<?php

namespace App\Services\Admin;

use App\Events\QueueUpdated;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\QueueTicket;
use App\Repositories\PaymentRepository;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;

class PaymentService
{
    // Các phương thức thanh toán được hỗ trợ
    const PAYMENT_METHODS = ['QR', 'ATM', 'MoMo', 'ZaloPay', 'Counter'];
    const PAYMENT_STATUSES = ['Chờ xử lý', 'Thành công', 'Thất bại', 'Hoàn tiền', 'Đã thanh toán', 'Chưa thanh toán'];

    public function __construct(
        protected PaymentRepository $repo,
        protected NotificationService $notifications
    ) {}

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
        
        // Debug log
        Log::info('buildListData called', ['filters' => $filters]);
        
        $payments = $this->repo->paginatedPayments($filters);
        
        // Debug log kết quả
        Log::info('Payments result', [
            'total' => $payments->total(),
            'count' => $payments->count()
        ]);

        return [
            'payments'       => $payments,
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
        $invoice = Invoice::findOrFail($data['invoice_id']);
        
        $ref     = strtoupper(Str::random(12));
        
        // Map invoice data to what the repository/model expects
        $paymentData = [
            'appointment_id'  => $invoice->appointment_id,
            'payment_method'  => $data['payment_method'],
            'amount'          => $data['amount'],
            'transaction_ref' => $ref,
        ];

        $payment = $this->repo->createPayment($paymentData);

        if ($payment->appointment?->user_id) {
            $this->notifications->createForUser(
                $payment->appointment->user_id,
                'Có hóa đơn cần thanh toán',
                'Hóa đơn cho lịch khám #' . $payment->appointment_id . ' có số tiền ' . number_format((float) $payment->total_amount, 0, ',', '.') . 'đ.',
                'payment_created',
                'payment',
                $payment->payment_id
            );
        }

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
        Log::info('confirmPayment called', ['payment_id' => $paymentId, 'ref' => $ref]);
        
        $updated = $this->repo->confirmPayment($paymentId, $ref);

        if ($updated) {
            // Đánh dấu hóa đơn và lịch hẹn đã thanh toán
            $payment = Payment::with(['appointment'])->find($paymentId);
            if ($payment) {
                if ($payment->appointment) {
                    $payment->appointment->update(['status' => 'Đã thanh toán']);
                    QueueTicket::where('appointment_id', $payment->appointment_id)
                        ->whereDate('queue_date', today())
                        ->where('status', 'waiting')
                        ->get()
                        ->each(function (QueueTicket $ticket) {
                            broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
                        });

                    $this->notifications->createForUser(
                        $payment->appointment->user_id,
                        'Thanh toán thành công',
                        'Giao dịch #' . $payment->payment_id . ' đã được xác nhận thanh toán.',
                        'payment_paid',
                        'payment',
                        $payment->payment_id
                    );
                }

                ActivityLogService::log(
                    'Thanh toán lịch khám',
                    'Admin đã xác nhận thanh toán giao dịch #' . $payment->payment_id . ' cho lịch khám #' . $payment->appointment_id . '.',
                    'payment',
                    $payment->payment_id,
                    [
                        'appointment_id' => $payment->appointment_id,
                        'method' => $payment->method,
                        'amount' => $payment->total_amount,
                        'transaction_ref' => $payment->transaction_ref,
                    ]
                );
            }
        }

        return $updated;
    }

    /**
     * Hủy / thất bại giao dịch.
     */
    public function failPayment(int $paymentId): bool
    {
        Log::info('failPayment called', ['payment_id' => $paymentId]);
        
        return $this->repo->failPayment($paymentId);
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
            (int) ($payment->total_amount ?? 0),
            'Thanh toan lich kham ' . ($payment->appointment_id ?? '')
        );
    }
}
