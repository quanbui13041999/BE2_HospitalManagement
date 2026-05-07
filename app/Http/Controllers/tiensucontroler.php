<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Tiensu; // Đừng quên use Model này

class tiensucontroler extends Controller
{
    // Hàm hiển thị giao diện (Đã có)
    public function tiensusuckhoe()
    {

        $userId = Auth::id();
        $tiensu = Tiensu::where('user_id', $userId)->first();
       return view('tiensu', compact('tiensu'));
    }

    public function luutiensu(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->back()->with('error', 'Bạn cần đăng nhập!');
        }

        // Tính toán lại BMI để đảm bảo dữ liệu chuẩn xác nhất
        $bmi = 0;
        if ($request->height > 0 && $request->weight > 0) {
            // Công thức: Cân nặng / (Chiều cao/100)^2
            $heightInMeters = $request->height / 100;
            $bmi = round($request->weight / ($heightInMeters * $heightInMeters), 2);
        }

        // Sử dụng updateOrCreate: Nếu đã có user_id này thì UPDATE, chưa có thì CREATE
        Tiensu::updateOrCreate(
            ['user_id' => $userId], // Điều kiện tìm kiếm
            [
                'blood_group'            => $request->nhommau,
                'yeuto_rh'               => $request->yeuto_rh,
                'height'                 => $request->height,
                'weight'                 => $request->weight,
                'bmi'                    => $bmi, // Dùng giá trị vừa tính toán
                'food_allergies'         => $request->food_allergies,
                'drug_allergies'         => $request->drug_allergies,
                'chronic_diseases'       => $request->chronic_diseases ?? [], // Tránh lỗi null nếu không chọn gì
                'other_chronic_diseases' => $request->other_chronic_diseases,
            ]
        );

        return redirect()->back()->with('success', 'Hồ sơ sức khỏe đã được cập nhật!');
    }
}
