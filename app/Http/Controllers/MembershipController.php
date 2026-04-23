<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MembershipCard;

class MembershipController extends Controller
{
    public function show()
    {
        try {
            // Bước 1: Kiểm tra xem đã đăng nhập chưa
            if (!Auth::check()) {
                // Nếu chưa đăng nhập, bắt quay về trang login ngay
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem thẻ!');
            }

            // Bước 2: Lấy thông tin user
            $user = Auth::user();

            // TEST: Sau khi bạn đăng nhập xong, hãy bỏ comment dòng dưới để xem số ID của mình
            // dd($user->id); 

            // Bước 3: Tìm thẻ thành viên theo ID của user đang đăng nhập
          $membership = MembershipCard::where('user_id', $user->user_id)->first();

            $progressPercent = 0;

            if ($membership) {
                $points = (int)$membership->points;

                // Logic phân hạng
                if ($points >= 10000000) {
                    $membership->tier = 'Vàng';
                    $progressPercent = 75;
                } elseif ($points >= 5000000) {
                    $membership->tier = 'Bạc';
                    $progressPercent = 50;
                } else {
                    $membership->tier = 'Đồng';
                    $progressPercent = 25;
                }
            } else {
                // Nếu user đã đăng nhập nhưng chưa có dữ liệu trong bảng membershipcards
                $membership = (object)[
                    'points' => 0,
                    'tier' => 'Đồng',
                    'card_number' => 'Chưa có thẻ'
                ];
            }

            $remaining = max(0, 25000000 - (int)($membership->points ?? 0));

            $extraData = [
                'visit_count' => 48,
                'pending_points' => 200,
                'voucher_count' => 3,
                'saved_money' => '890k'
            ];

            return view('thethanhvien', compact('user', 'membership', 'remaining', 'progressPercent', 'extraData'));

        } catch (\Exception $e) {
            return "Lỗi rồi Đạt ơi: " . $e->getMessage();
        }
    }
}