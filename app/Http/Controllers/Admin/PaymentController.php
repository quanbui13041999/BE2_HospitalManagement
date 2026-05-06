<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentRequest;
use App\Services\Admin\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    // ----------------------------------------------------------------
    // Trang thanh toán cho 1 hóa đơn cụ thể
    // ----------------------------------------------------------------
    public function show(int $invoiceId)
    {
        return view(
            'admin.payments.show',
            $this->paymentService->buildIndexData($invoiceId)
        );
    }

    // ----------------------------------------------------------------
    // Danh sách / lịch sử giao dịch
    // ----------------------------------------------------------------
    public function index(Request $request)
    {
        return view(
            'admin.payments.index',
            $this->paymentService->buildListData($request)
        );
    }

    // ----------------------------------------------------------------
    // Khởi tạo giao dịch (POST từ form chọn phương thức)
    // ----------------------------------------------------------------
    public function store(PaymentRequest $request)
    {
        $result = $this->paymentService->initiatePayment($request->validated());

        // Nếu là QR, redirect sang trang chờ quét QR
        if ($request->payment_method === 'QR') {
            return redirect()->route('admin.payments.qr', $result['payment']->payment_id)
                ->with([
                    'qr_content' => $result['qr_content'],
                    'ref'        => $result['ref'],
                ]);
        }

        // Các phương thức khác: redirect về trang chi tiết với thông báo
        return redirect()->route('admin.payments.show', $request->invoice_id)
            ->with('success', "Đã tạo giao dịch #{$result['ref']}. Vui lòng hoàn tất thanh toán.");
    }

    // ----------------------------------------------------------------
    // Trang chờ quét QR (polling)
    // ----------------------------------------------------------------
    public function qr(int $paymentId)
    {
        return view('admin.payments.qr', compact('paymentId'));
    }

    // ----------------------------------------------------------------
    // Webhook: cổng thanh toán callback xác nhận thành công
    // ----------------------------------------------------------------
    public function confirm(Request $request, int $paymentId)
    {
        $success = $this->paymentService->confirmPayment(
            $paymentId,
            $request->input('ref', '')
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Thanh toán thành công.' : 'Xác nhận thất bại.',
        ]);
    }

    // ----------------------------------------------------------------
    // Webhook: callback thất bại
    // ----------------------------------------------------------------
    public function fail(int $paymentId)
    {
        $this->paymentService->failPayment($paymentId);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái thất bại.']);
    }
}
