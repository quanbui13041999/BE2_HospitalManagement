<?php

namespace App\Http\Controllers;

use App\Models\HealthBackground;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthBackgroundController extends Controller
{
    public function index()
    {
        return view('health_background.index', $this->buildViewData(
            HealthBackground::where('user_id', Auth::id())->first()
        ));
    }

    public function store(Request $request)
    {
        $this->normalizeTextInputs($request);

        $validated = $request->validate(
            HealthBackground::rules(),
            HealthBackground::messages()
        );

        $result = HealthBackground::saveForUser((int) Auth::id(), $validated);

        return redirect()
            ->route('health.index')
            ->with($result['saved'] ? 'success' : 'warning', $result['message']);
    }

    public function showPatient(int $patientId)
    {
        $user = Auth::user();

        if (! in_array($user->role_id ?? 0, [1, 2], true)) {
            abort(403, 'Không có quyền xem hồ sơ này');
        }

        $patient = User::findOrFail($patientId);
        $healthData = HealthBackground::where('user_id', $patientId)->first();

        return view('health_background.index', $this->buildViewData($healthData, $patient, true));
    }

    private function buildViewData(?HealthBackground $healthData, ?User $patient = null, bool $readonly = false): array
    {
        return array_merge(
            compact('healthData', 'patient', 'readonly'),
            HealthBackground::viewOptions()
        );
    }

    private function normalizeTextInputs(Request $request): void
    {
        foreach (['food_allergies', 'drug_allergies', 'other_chronic_diseases'] as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));
            $request->merge([
                $field => $value === '' ? null : preg_replace('/\s+/u', ' ', $value),
            ]);
        }
    }
}
