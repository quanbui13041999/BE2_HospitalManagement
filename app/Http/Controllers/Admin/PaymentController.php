<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentRequest;
use App\Services\Admin\PaymentService;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    // ----------------------------------------------------------------
    // Trang thanh toán hóa đơn (checkout)
    // ----------------------------------------------------------------
    public function checkout(int $invoiceId)
    {
        return view(
            'admin.payments.checkout',
            $this->paymentService->buildIndexData($invoiceId)
        );
    }

    // ----------------------------------------------------------------
    // Trang chi tiết giao dịch (dùng paymentId)
    // ----------------------------------------------------------------
    public function show(int $paymentId)
    {
        // Lấy payment chi tiết từ database
        $payment = Payment::with([
            'appointment.user',
            'appointment.schedule.doctor',
            'items',
            'insurance',
            'membership'
        ])->findOrFail($paymentId);
        
        return view('admin.payments.show', compact('payment'));
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
        return redirect()->route('admin.payments.show', $result['payment']->payment_id)
            ->with('success', "Đã tạo giao dịch #{$result['ref']}. Vui lòng hoàn tất thanh toán.");
    }

    // ----------------------------------------------------------------
    // Trang chờ quét QR (polling)
    // ----------------------------------------------------------------
    public function qr(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        return view('admin.payments.qr', compact('paymentId', 'payment'));
    }

    // ----------------------------------------------------------------
    // Xác nhận thanh toán thành công
    // ----------------------------------------------------------------
    public function confirm(Request $request, int $paymentId)
    {
        $success = $this->paymentService->confirmPayment(
            $paymentId,
            $request->input('ref', '')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Thanh toán thành công.' : 'Xác nhận thất bại.',
            ]);
        }

        if ($success) {
            return redirect()->route('admin.payments.show', $paymentId)
                ->with('success', 'Thanh toán thành công!');
        }

        return redirect()->route('admin.payments.show', $paymentId)
            ->with('error', 'Xác nhận thất bại!');
    }

    // ----------------------------------------------------------------
    // Đánh dấu thất bại
    // ----------------------------------------------------------------
    public function fail(int $paymentId)
    {
        $this->paymentService->failPayment($paymentId);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái thất bại.']);
        }

        return redirect()->route('admin.payments.show', $paymentId)
            ->with('error', 'Đã cập nhật trạng thái thất bại.');
    }
}