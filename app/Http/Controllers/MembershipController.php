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
            
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
            }

            
            $membership = MembershipCard::where('user_id', $user->user_id)->first();

            $progressPercent = 0;

            if ($membership) {
                $points = (int)$membership->points;

                // Logic phân hạng tự động
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
                // Trường hợp user chưa có thẻ trong DB
                $membership = (object)[
                    'points' => 0,
                    'tier' => 'Đồng',
                    'card_number' => 'Chưa có thẻ',
                    'card_id' => null
                ];
            }

       
            $remaining = max(0, 25000000 - (int)($membership->points ?? 0));
            $extraData = [
                'visit_count' => 48,
                'pending_points' => 200,
                'voucher_count' => 3,
                'saved_money' => '890k'
            ];


            return view('Membership.thethanhvien', compact('user', 'membership', 'remaining', 'progressPercent', 'extraData'));

        } catch (\Exception $e) {
            return "Lỗi rồi bạn ơi: " . $e->getMessage();
        }
    }
}