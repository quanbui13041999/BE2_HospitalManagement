<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Trang thanh toán cho 1 appointment (người dùng chọn phương thức)
     */
    public function show(int $appointmentId)
    {
        // Kiểm tra xem đã có payment thành công chưa
        $existingPayment = Payment::where('appointment_id', $appointmentId)
            ->whereIn('status', ['Thành công', 'Đã thanh toán'])
            ->first();
        
        if ($existingPayment) {
            return redirect()->route('user.payments.success', $existingPayment->payment_id)
                ->with('info', 'Lịch hẹn này đã được thanh toán.');
        }
        
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

        // Kiểm tra lại trước khi tạo giao dịch mới
        $existingPayment = Payment::where('appointment_id', $request->appointment_id)
            ->whereIn('status', ['Thành công', 'Đã thanh toán'])
            ->first();
        
        if ($existingPayment) {
            return redirect()->route('user.payments.success', $existingPayment->payment_id)
                ->with('info', 'Lịch hẹn này đã được thanh toán.');
        }

        $result = $this->paymentService->initiatePayment(
            $request->appointment_id,
            $request->method,
            Auth::id()
        );

        if ($request->method === 'QR') {
            return redirect()->route('user.payments.qr', $result['payment']->payment_id)
                ->with([
                    'qr_content'   => $result['qr_content'],
                    'total_amount' => $result['payment']->total_amount,
                    'checkout_url' => $result['checkout_url'] ?? null,
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
        $payment = $this->paymentService->getPayment($paymentId);
        
        // Kiểm tra nếu đã thanh toán thành công
        if ($payment->status === 'Thành công' || $payment->status === 'Đã thanh toán') {
            return redirect()->route('user.payments.success', $paymentId)
                ->with('info', 'Giao dịch này đã được thanh toán trước đó.');
        }
        
        // Nếu thất bại, chuyển hướng về trang chọn phương thức
        if ($payment->status === 'Thất bại') {
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch đã thất bại. Vui lòng tạo giao dịch mới.');
        }
        
        // Nếu đã quá thời gian (24h), đánh dấu thất bại
        if ($payment->payment_date && $payment->payment_date->diffInHours(now()) > 24) {
            $this->paymentService->failPayment($paymentId);
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch đã hết hạn. Vui lòng tạo giao dịch mới.');
        }
        
        $qrContent   = session('qr_content', 'HOSPITAL|' . $payment->transaction_ref . '|' . (int)$payment->total_amount . '|Thanh toan lich kham');
        $totalAmount = session('total_amount', $payment->total_amount);
        $checkoutUrl = session('checkout_url');
        $isRealMode  = $this->paymentService->isPayOsConfigured();
        
        return view('user.payments.qr', compact('payment', 'qrContent', 'totalAmount', 'checkoutUrl', 'isRealMode'));
    }

    /**
     * Trang giả lập cổng thanh toán (ATM/MoMo/ZaloPay)
     */
    public function gateway(int $paymentId)
    {
        $payment = $this->paymentService->getPayment($paymentId);
        
        // Kiểm tra nếu đã thanh toán thành công
        if ($payment->status === 'Thành công' || $payment->status === 'Đã thanh toán') {
            return redirect()->route('user.payments.success', $paymentId)
                ->with('info', 'Giao dịch này đã được thanh toán trước đó.');
        }
        
        // Nếu thất bại, chuyển hướng về trang chọn phương thức
        if ($payment->status === 'Thất bại') {
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch đã thất bại. Vui lòng tạo giao dịch mới.');
        }
        
        // Nếu đã quá thời gian (24h), đánh dấu thất bại
        if ($payment->payment_date && $payment->payment_date->diffInHours(now()) > 24) {
            $this->paymentService->failPayment($paymentId);
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch đã hết hạn. Vui lòng tạo giao dịch mới.');
        }
        
        $ref        = session('ref', $payment->transaction_ref);
        $method     = session('method', $payment->method);
        $totalAmount = session('total_amount', $payment->total_amount);
        
        return view('user.payments.gateway', compact('payment', 'ref', 'method', 'totalAmount'));
    }

    /**
     * Kiểm tra trạng thái thanh toán (API)
     */
    public function check(int $paymentId)
    {
        $payment = $this->paymentService->getPayment($paymentId);
        
        $isPaid = in_array($payment->status, ['Thành công', 'Đã thanh toán']);
        
        return response()->json([
            'status' => $payment->status,
            'is_paid' => $isPaid,
            'redirect_url' => $isPaid ? route('user.payments.success', $paymentId) : null
        ]);
    }

    /**
     * Callback xác nhận thanh toán thành công (từ cổng / polling QR)
     */
    public function confirm(Request $request, int $paymentId)
    {
        $payment = $this->paymentService->getPayment($paymentId);
        
        // Nếu đã thành công rồi, không xử lý nữa
        if ($payment->status === 'Thành công' || $payment->status === 'Đã thanh toán') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Giao dịch đã được thanh toán trước đó.',
                    'redirect' => route('user.payments.success', $paymentId),
                ]);
            }
            return redirect()->route('user.payments.success', $paymentId)
                ->with('info', 'Giao dịch đã được thanh toán trước đó.');
        }
        
        // Nếu đã thất bại, không xử lý
        if ($payment->status === 'Thất bại') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giao dịch đã thất bại.',
                ]);
            }
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch đã thất bại. Vui lòng tạo giao dịch mới.');
        }
        
        $success = $this->paymentService->confirmPayment($paymentId, $request->input('ref', ''));

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
        $payment = $this->paymentService->getPayment($paymentId);
        
        // Nếu đã thành công rồi, không cho thất bại
        if ($payment->status === 'Thành công' || $payment->status === 'Đã thanh toán') {
            return redirect()->route('user.payments.success', $paymentId)
                ->with('info', 'Giao dịch đã được thanh toán thành công.');
        }
        
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
        
        // Nếu chưa thanh toán, chuyển về trang chọn phương thức
        if (!in_array($payment->status, ['Thành công', 'Đã thanh toán'])) {
            return redirect()->route('user.payments.show', $payment->appointment_id)
                ->with('error', 'Giao dịch chưa được thanh toán. Vui lòng thực hiện thanh toán.');
        }
        
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