<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Service;
use App\Models\User;
use App\Repositories\ServiceRepository;
use App\Services\NotificationService;
use App\Services\PayOsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function __construct(protected ServiceRepository $repo) {}

    /**
     * Hiển thị danh sách dịch vụ công khai (trang chủ).
     */
    public function index(Request $request)
    {
        $data = [
            'services' => $this->repo->filteredPublicServices($request),
            'departments' => $this->repo->activeDepartments(),
        ];

        return view('services.index', $data);
    }

    /**
     * JSON endpoint cho realtime polling từ trang công khai /dich-vu.
     */
    public function publicServicesData(Request $request)
    {
        $services = $this->repo->filteredPublicServices($request);

        $list = $services->getCollection()->map(function ($s) {
            $priceNormal = $s->activePrices->first(fn ($p) => str_contains(strtolower($p->price_type), 'thường') || str_contains(strtolower($p->price_type), 'normal')) ?? $s->activePrices->first();
            $priceBhyt = $s->activePrices->first(fn ($p) => str_contains(strtolower($p->price_type), 'bhyt') || str_contains(strtolower($p->price_type), 'bảo hiểm'));
            $priceVip = $s->activePrices->first(fn ($p) => str_contains(strtolower($p->price_type), 'vip') || str_contains(strtolower($p->price_type), 'cao cấp'));
            $lowestPrice = $s->activePrices->min('price');

            return [
                'service_id' => $s->service_id,
                'service_code' => $s->service_code,
                'service_name' => $s->service_name,
                'description' => $s->description,
                'duration_minutes' => $s->duration_minutes,
                'department' => $s->department?->department_name,
                'price_normal' => $priceNormal?->price,
                'price_bhyt' => $priceBhyt?->price,
                'price_vip' => $priceVip?->price,
                'lowest_price' => $lowestPrice,
                'show_url' => route('user.services.show', $s->service_id),
                'book_url' => route('user.services.show', $s->service_id),
            ];
        });

        return response()->json([
            'total' => $services->total(),
            'services' => $list,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Hiển thị chi tiết dịch vụ.
     */
    public function show(Request $request, int $id)
    {
        $service = Service::with(['department', 'activePrices'])->find($id);

        abort_if(! $service || ! $service->status, 404, 'Dịch vụ không tồn tại hoặc không khả dụng.');

        $related = $this->repo->relatedServices($service);

        return view('services.show', [
            'service' => $service,
            'related' => $related,
        ]);
    }

    /**
     * Lấy giá dịch vụ theo loại (API).
     */
    public function getPrice(int $id, string $priceType)
    {
        $service = Service::find($id);

        abort_if(! $service, 404, 'Dịch vụ không tồn tại.');

        $price = $service->activePrices()
            ->where('price_type', $priceType)
            ->first();

        if (! $price) {
            return response()->json(['error' => 'Giá không khả dụng.'], 404);
        }

        return response()->json([
            'price_id' => $price->price_id,
            'service_id' => $service->service_id,
            'price_type' => $priceType,
            'price' => $price->price,
            'effective' => $this->formatDateValue($price->effective_date),
        ]);
    }

    private function formatDateValue(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    /**
     * Đặt và thanh toán trực tiếp dịch vụ y tế độc lập (không cần bác sĩ)
     */
    public function bookService(Request $request, int $id)
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để đặt và thanh toán dịch vụ.');
        }

        $request->validate([
            'price_type' => 'required|string',
            'work_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'note' => 'nullable|string|max:255',
            'payment_option' => 'nullable|string|in:now,later',
        ], [
            'price_type.required' => 'Vui lòng chọn loại mức giá dịch vụ.',
            'work_date.required' => 'Vui lòng chọn ngày thực hiện.',
            'work_date.after_or_equal' => 'Ngày thực hiện phải từ hôm nay trở đi.',
            'appointment_time.required' => 'Vui lòng chọn giờ hẹn.',
        ]);

        $service = Service::find($id);
        if (! $service || ! $service->status) {
            return back()->with('error', 'Dịch vụ không tồn tại hoặc đã ngừng hoạt động.');
        }

        $priceRecord = $service->activePrices()
            ->where('price_type', $request->price_type)
            ->first();

        if (! $priceRecord) {
            return back()->with('error', 'Mức giá đã chọn hiện không khả dụng.');
        }

        $subtotal = (float) $priceRecord->price;

        // 1. Tạo bản ghi đặt lịch hẹn cho dịch vụ (schedule_id = null)
        $appointmentDatetime = $request->work_date.' '.$request->appointment_time.':00';
        $duration = $service->duration_minutes ?? 30;
        $appointmentEndtime = Carbon::parse($appointmentDatetime)->addMinutes($duration)->format('Y-m-d H:i:s');

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'service_id' => $id,
            'schedule_id' => null, // Dịch vụ độc lập không có ca trực bác sĩ
            'appointment_time' => $appointmentDatetime,
            'appointment_timeEnd' => $appointmentEndtime,
            'queue_number' => null,
            'status' => 'Chờ thanh toán',
            'is_priority' => false,
            'note' => $request->note ?? ('Đăng ký dịch vụ: '.$service->service_name),
        ]);

        // 2. Tính giảm giá BHYT & Thành viên nếu có
        $user = User::findOrFail(Auth::id());

        $insurance = null;
        $insuranceDiscount = 0;
        if ($request->price_type === 'BHYT') {
            $insurance = $user->insuranceCards()
                ->where('status', 'Còn hạn')
                ->first();
            $insuranceDiscount = $insurance
                ? round($subtotal * $insurance->discount_pct / 100, 2)
                : 0;
        }

        $membership = $user->membershipCard ?? null;
        $membershipDiscount = ($membership && $membership->status == 1)
            ? round($subtotal * $membership->discount_pct / 100, 2)
            : 0;

        $discountAmount = $insuranceDiscount + $membershipDiscount;
        $totalAmount = max(0, $subtotal - $discountAmount);

        $ref = 'PAY-'.strtoupper(Str::random(10));

        // 3. Tạo Hóa đơn thanh toán
        $payment = Payment::create([
            'appointment_id' => $appointment->appointment_id,
            'insurance_id' => $insurance?->insurance_id,
            'membership_id' => $membership?->card_id,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'method' => 'QR',
            'status' => 'Chờ thanh toán',
            'transaction_ref' => $ref,
            'payment_date' => now(),
        ]);

        // 4. Tạo Payment Item
        PaymentItem::create([
            'payment_id' => $payment->payment_id,
            'item_name' => $service->service_name.' ('.$request->price_type.')',
            'quantity' => 1,
            'unit_price' => $subtotal,
            'subtotal' => $subtotal,
        ]);

        // 5. Bắn notification cho bệnh nhân
        app(NotificationService::class)->createForUser(
            Auth::id(),
            'Đăng ký dịch vụ thành công',
            'Bạn đã đăng ký dịch vụ '.$service->service_name.' vào '.Carbon::parse($appointmentDatetime)->format('H:i d/m/Y').'. Số tiền cần thanh toán: '.number_format($totalAmount, 0, ',', '.').'đ.',
            'payment_created',
            'payment',
            $payment->payment_id
        );

        $paymentOption = $request->input('payment_option', 'now');

        // 6. Gọi PayOS sinh QR động real-time
        try {
            $payOsService = app(PayOsService::class);
            $payOsResult = $payOsService->createPaymentLink(
                $payment->payment_id,
                (int) $totalAmount,
                'Thanh toan DV '.$appointment->appointment_id,
                route('user.payments.success', $payment->payment_id),
                route('user.payments.fail', $payment->payment_id)
            );

            if ($payOsResult['success']) {
                $payment->update([
                    'checkout_url' => $payOsResult['checkoutUrl'] ?? null,
                    'qr_content' => $payOsResult['qrContent'] ?? null,
                    'transaction_ref' => $payOsResult['paymentLinkId'] ?? $payment->transaction_ref,
                ]);
            }

            if ($paymentOption === 'later') {
                return redirect()->route('user.payments.history')
                    ->with('success', 'Đăng ký dịch vụ thành công! Bạn có thể thanh toán hoá đơn này trong Lịch sử thanh toán.');
            }

            return redirect()->route('user.payments.qr', $payment->payment_id)
                ->with([
                    'qr_content' => $payOsResult['qrContent'],
                    'total_amount' => $totalAmount,
                    'checkout_url' => $payOsResult['checkoutUrl'] ?? null,
                ]);
        } catch (\Exception $e) {
            if ($paymentOption === 'later') {
                return redirect()->route('user.payments.history')
                    ->with('success', 'Đăng ký dịch vụ thành công! Bạn có thể thanh toán hoá đơn này trong Lịch sử thanh toán.');
            }

            // Fallback giả lập nếu gọi API PayOS lỗi
            return redirect()->route('user.payments.qr', $payment->payment_id)
                ->with([
                    'qr_content' => 'HOSPITAL|STANDALONE_'.$payment->payment_id.'|'.$totalAmount.'|Thanh toan dich vu',
                    'total_amount' => $totalAmount,
                ]);
        }
    }
}
