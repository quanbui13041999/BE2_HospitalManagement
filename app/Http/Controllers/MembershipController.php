<?php

namespace App\Http\Controllers;

use App\Services\MembershipCardSyncService;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{
    public function show(MembershipCardSyncService $membershipCards)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        $membership = $membershipCards->syncForUser((int) $user->user_id);
        $visitCount = $membershipCards->getVisitCountForUser((int) $user->user_id);
        $pointHistory = $membershipCards->getPointHistoryForUser((int) $user->user_id);
        $extraData = [];

        return view('Membership.membershipcards', compact('user', 'membership', 'visitCount', 'pointHistory', 'extraData'));
    }
}
