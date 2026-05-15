<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MembershipCard;

class MembershipController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        // 1. Lấy hoặc tạo mới membership
        $membership = MembershipCard::firstOrCreate(
            ['user_id' => $user->user_id],
            [
                'points' => 0,
                'total_spent' => 0,
                'tier' => 'Đồng',
                'card_number' => 'MB-' . now()->format('Ymd') . '-' . str_pad((string) $user->user_id, 6, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addYear()->toDateString(),
            ]
        );

        // 2. ĐOẠN ĐỒNG BỘ: Kiểm tra nếu hạng thực tế (qua accessor) khác với hạng lưu trong DB thô
        // $membership->tier là chữ được tính toán từ thuộc tính Accessor (chữ "Vàng")
        if ($membership->getRawOriginal('tier') !== $membership->tier) {
            $membership->tier = $membership->tier; // Gán lại hạng đúng
            $membership->save(); // Lưu lại vào Database
        }

        $extraData = [
            'visit_count' => 48,
            'pending_points' => 200,
            'voucher_count' => 3,
            'saved_money' => '890k'
        ];

        return view('Membership.membershipcards', compact('user', 'membership', 'extraData'));
    }
}
