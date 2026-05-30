<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MembershipCard;

class MembershipController extends Controller
{
    /**
     * Hiển thị trang thông tin thẻ thành viên của người dùng
     */
    public function show()
    {
        // Lấy thông tin của người dùng đang đăng nhập trong phiên làm việc (session)
        $user = Auth::user();

        // Kiểm tra nếu người dùng chưa đăng nhập thì đá hướng về trang login kèm thông báo lỗi
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        // 1. LẤY HOẶC TẠO MỚI THẺ THÀNH VIÊN (MEMBERSHIP CARD)
        // firstOrCreate sẽ tìm trong DB xem có thẻ nào ứng với 'user_id' này chưa.
        // - Nếu CÓ: Nó sẽ lấy ra bản ghi đó.
        // - Nếu CHƯA CÓ: Nó sẽ tự động tạo mới một thẻ với các giá trị mặc định truyền vào phía dưới.
        $membership = MembershipCard::firstOrCreate(
            ['user_id' => $user->user_id], // Điều kiện tìm kiếm
            [
                'points' => 0,          // Mặc định ban đầu 0 điểm
                'total_spent' => 0,     // Mặc định tổng chi tiêu bằng 0
                'tier' => 'Đồng',       // Mặc định hạng thẻ ban đầu là hạng Đồng
                // Tự động tạo số thẻ định dạng: MB-YYYYMMDD-ID_USER (Ví dụ: MB-20260515-000025)
                'card_number' => 'MB-' . now()->format('Ymd') . '-' . str_pad((string) $user->user_id, 6, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addYear()->toDateString(), // Ngày hết hạn mặc định là 1 năm sau kể từ ngày tạo
            ]
        );

        // 2. ĐỒNG BỘ DỮ LIỆU: Cập nhật lại số điểm và hạng thẻ dựa trên Tổng chi tiêu thực tế
        // Lấy giá trị ĐIỂM gốc lưu trong database (bỏ qua các hàm biến đổi tự động của Model nếu có)
        $rawPoints      = (int)   $membership->getRawOriginal('points');
        // Lấy giá trị TỔNG CHI TIÊU gốc lưu trong database
        $rawSpent       = (float) $membership->getRawOriginal('total_spent');
        // Tính toán số điểm thực tế: Cứ tiêu 1,000 đ thì được 1 điểm (dùng hàm floor để làm tròn xuống số nguyên)
        $computedPoints = (int) floor($rawSpent / 1000);
        // Lấy giá trị HẠNG THẺ (tier) gốc lưu trong database (Ví dụ đang lưu: "Đồng")
        $rawTier        = (string) $membership->getRawOriginal('tier');

        // KIỂM TRA ĐIỀU KIỆN ĐỒNG BỘ:
        // Nếu số điểm cũ khác điểm vừa tính toán HOẶC hạng thẻ cũ trong DB khác với hạng thẻ thực tế (tính theo điểm mới qua Model Accessor)
        if ($rawPoints !== $computedPoints || $rawTier !== $membership->tier) {
            
            // Cập nhật lại điểm số mới cho Model
            $membership->points = $computedPoints;
            
            // Lưu lại các thay đổi vào Database. 
            // Lưu ý: Lúc này hàm booted/saving trong Model (nếu có) sẽ tự động bắt lấy số điểm mới để ép lại cột 'tier' chuẩn vào DB.
            $membership->save();
            
            // Làm mới lại dữ liệu của biến $membership từ database để đảm bảo mọi thông tin hiển thị ra giao diện là mới nhất
            $membership->refresh();
        }

        // 3. KHỞI TẠO DỮ LIỆU PHỤ (Dữ liệu giả lập, sau này có thể cấu hình lấy động từ DB)
        $extraData = [
            'visit_count' => 48,          // Số lần khách hàng đến khám
            'pending_points' => 200,      // Số điểm đang chờ hệ thống phê duyệt
            'voucher_count' => 3,         // Số lượng mã giảm giá (voucher) đang sở hữu
            'saved_money' => '890k'       // Số tiền tiết kiệm được nhờ dùng thẻ thành viên
        ];

        // Trả về file giao diện Blade và truyền các biến $user, $membership, $extraData sang bên HTML
        return view('Membership.membershipcards', compact('user', 'membership', 'extraData'));
    }
}