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
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập!');
        }

        // 👉 luôn đảm bảo có membership
        $membership = MembershipCard::firstOrCreate(
            ['user_id' => $user->user_id],
            [
                'points' => 0,
                'card_number' => 'Chưa có thẻ'
            ]
        );

        // 👉 data phụ (có thể sau này lấy từ DB)
        $extraData = [
            'visit_count' => 48,
            'pending_points' => 200,
            'voucher_count' => 3,
            'saved_money' => '890k'
        ];

        return view('Membership.membershipcards', compact(
            'user',
            'membership',
            'extraData'
        ));
    }
}
