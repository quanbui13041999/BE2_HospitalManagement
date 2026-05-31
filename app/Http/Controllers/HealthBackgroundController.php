<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HealthBackground;

class HealthBackgroundController extends Controller
{
    // 👉 View
    public function index()
    {
        $healthData = HealthBackground::where('user_id', Auth::id())->first();

        return view('health_background.index', compact('healthData'));
    }

    // 👉 Store / Update
    public function store(Request $request)
    {
        $request->validate([
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['bmi'] = $this->calculateBMI($request->height, $request->weight);

        HealthBackground::updateOrCreate(
            ['user_id' => Auth::id()],
            $this->mapData($data)
        );

        return back()->with('success', 'Cập nhật thành công!');
    }

    // 👉 Business logic (không để trong view)
    private function calculateBMI($height, $weight)
    {
        if ($height > 0 && $weight > 0) {
            $h = $height / 100;
            return round($weight / ($h * $h), 2);
        }
        return 0;
    }

    
    private function mapData($data)
    {
        return [
            'user_id'                 => $data['user_id'],
            'blood_group'            => $data['nhommau'],
            'yeuto_rh'               => $data['yeuto_rh'],
            'height'                 => $data['height'],
            'weight'                 => $data['weight'],
            'bmi'                    => $data['bmi'],
            'food_allergies'         => $data['food_allergies'],
            'drug_allergies'         => $data['drug_allergies'],
            'chronic_diseases'       => $data['chronic_diseases'] ?? [],
            'other_chronic_diseases' => $data['other_chronic_diseases'],
        ];
    }
    // 👉 Bác sĩ/Admin xem tiền sử của bệnh nhân cụ thể
    public function showPatient(int $patientId)
    {
        $user = Auth::user();
        $isDoctor = in_array($user->role_id ?? 0, [1, 2]);

        if (!$isDoctor) {
            abort(403, 'Không có quyền xem hồ sơ này');
        }

        $healthData = HealthBackground::where('user_id', $patientId)->first();
        $patient    = \App\Models\User::findOrFail($patientId);

        return view('health_background.index', compact('healthData', 'patient'));
    }
}
