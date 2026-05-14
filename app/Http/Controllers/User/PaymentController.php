<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Trang thanh toán cho 1 appointment (người dùng chọn phương thức)
     */
    public function show(int $appointmentId)
    {
        $data = $this->paymentService->buildPaymentPage($appointmentId, Auth::id());
        return view('user.payments.show', $data);
    }

    /**
     * Xử lý thanh toán: người dùng submit chọn phương thức
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'method'         => 'required|in:QR,ATM,MoMo,ZaloPay,Counter',
        ]);

        $result = $this->paymentService->initiatePayment(
            $request->appointment_id,
            $request->method,
            Auth::id()
        );

        if ($request->method === 'QR') {
            return redirect()->route('user.payments.qr', $result['payment']->payment_id)
                ->with([
                    'qr_content'  => $result['qr_content'],
                    'total_amount' => $result['payment']->total_amount,
                ]);
        }

        // Counter (thu ngân): xác nhận ngay
        if ($request->method === 'Counter') {
            $this->paymentService->confirmPayment($result['payment']->payment_id, $result['ref']);
            return redirect()->route('user.payments.success', $result['payment']->payment_id);
        }

        // ATM / MoMo / ZaloPay: giả lập redirect tới cổng
        return redirect()->route('user.payments.gateway', $result['payment']->payment_id)
            ->with([
                'ref'          => $result['ref'],
                'method'       => $request->method,
                'total_amount' => $result['payment']->total_amount,
            ]);
    }

    /**
     * Trang chờ quét QR
     */
    public function qr(int $paymentId)
    {
        $payment    = $this->paymentService->getPayment($paymentId);
        $qrContent  = session('qr_content', 'HOSPITAL|' . $payment->transaction_ref . '|' . (int)$payment->total_amount . '|Thanh toan lich kham');
        $totalAmount = session('total_amount', $payment->total_amount);
        return view('user.payments.qr', compact('payment', 'qrContent', 'totalAmount'));
    }

    /**
     * Trang giả lập cổng thanh toán (ATM/MoMo/ZaloPay)
     */
    public function gateway(int $paymentId)
    {
        $payment    = $this->paymentService->getPayment($paymentId);
        $ref        = session('ref', $payment->transaction_ref);
        $method     = session('method', $payment->method);
        $totalAmount = session('total_amount', $payment->total_amount);
        return view('user.payments.gateway', compact('payment', 'ref', 'method', 'totalAmount'));
    }

    /**
     * Callback xác nhận thanh toán thành công (từ cổng / polling QR)
     */
    public function confirm(Request $request, int $paymentId)
    {
        $success = $this->paymentService->confirmPayment(
            $paymentId,
            $request->input('ref', '')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'redirect' => route('user.payments.success', $paymentId),
            ]);
        }

        return $success
            ? redirect()->route('user.payments.success', $paymentId)
            : back()->with('error', 'Xác nhận thất bại. Vui lòng thử lại.');
    }

    /**
     * Callback thất bại
     */
    public function fail(int $paymentId)
    {
        $this->paymentService->failPayment($paymentId);
        $payment = $this->paymentService->getPayment($paymentId);
        return view('user.payments.result', [
            'payment' => $payment,
            'success' => false,
        ]);
    }

    /**
     * Trang kết quả thanh toán thành công
     */
    public function success(int $paymentId)
    {
        $payment = $this->paymentService->getPayment($paymentId);
        return view('user.payments.result', [
            'payment' => $payment,
            'success' => true,
        ]);
    }

    /**
     * Lịch sử thanh toán của người dùng
     */
    public function history()
    {
        $payments = $this->paymentService->getUserPayments(Auth::id());
        return view('user.payments.history', compact('payments'));
    }
}
