<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayOsService;
use App\Services\User\PaymentService;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PayOsService $payOsService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Tiếp nhận và tự động xử lý webhook chuyển khoản ngân hàng từ PayOS
     */
    public function handlePayOsWebhook(Request $request)
    {
        $payload = $request->all();
        
        Log::info('Nhận Webhook từ PayOS:', $payload);

        // 1. Xác thực chữ ký số webhook tránh giả mạo dữ liệu nạp tiền
        $verifiedData = $this->payOsService->verifyWebhook($payload);
        
        if (!$verifiedData) {
            return response()->json([
                'success' => false,
                'message' => 'Chữ ký webhook không hợp lệ hoặc dữ liệu bị sửa đổi!'
            ], 400);
        }

        try {
            // Lấy orderCode (chính là payment_id trong hệ thống của chúng ta)
            $paymentId = (int) $verifiedData->orderCode;
            $reference = $verifiedData->reference ?? 'PAYOS_REF';
            $amount = (float) $verifiedData->amount;

            // 2. Tìm bản ghi thanh toán trong database
            $payment = Payment::find($paymentId);
            
            if (!$payment) {
                Log::warning("PayOS Webhook: Không tìm thấy Payment ID #{$paymentId} trong hệ thống!");
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy giao dịch #{$paymentId}"
                ], 404);
            }

            // 3. Cơ chế Idempotency: Kiểm tra nếu giao dịch đã xử lý thành công trước đó
            if (in_array($payment->status, ['Thành công', 'Đã thanh toán'])) {
                Log::info("PayOS Webhook: Giao dịch #{$paymentId} đã hoàn tất thanh toán từ trước. Bỏ qua.");
                return response()->json([
                    'success' => true,
                    'message' => 'Giao dịch đã được xác nhận thành công trước đó.'
                ]);
            }

            // 4. Đối soát số tiền thực nhận (Có thể ghi nhận nhưng log cảnh báo nếu chuyển khoản thiếu tiền)
            if ($amount < (float) $payment->total_amount) {
                Log::warning("PayOS Webhook: Số tiền thực nhận ({$amount}đ) ít hơn số tiền cần thanh toán ({$payment->total_amount}đ) cho giao dịch #{$paymentId}!");
            }

            // 5. Gọi Service thực thi cập nhật trạng thái lịch hẹn khám, hóa đơn và bắn notification cho bệnh nhân
            $success = $this->paymentService->confirmPayment($paymentId, $reference);

            if ($success) {
                Log::info("PayOS Webhook: Xác nhận thanh toán thành công hoàn toàn cho Payment #{$paymentId} (ACB Bank).");
                return response()->json([
                    'success' => true,
                    'message' => 'Đã tự động xác nhận thanh toán thành công!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật trạng thái trong hệ thống!'
            ], 500);

        } catch (Exception $e) {
            Log::error('Lỗi khi xử lý PayOS Webhook: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý nội bộ: ' . $e->getMessage()
            ], 500);
        }
    }
}
