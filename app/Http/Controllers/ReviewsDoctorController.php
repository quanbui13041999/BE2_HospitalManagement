<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewsDoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewsDoctorController extends Controller
{
    protected ReviewsDoctorService $reviewsDoctorService;

    public function __construct(ReviewsDoctorService $reviewsDoctorService)
    {
        $this->reviewsDoctorService = $reviewsDoctorService;
    }

    /**
     * kiem tra xem appointment co the review duoc khong
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCanRecview(Request $request) {
        if (!$this->reviewsDoctorService->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
        }

        $appointmentId = (int)$request->query('appointment_id');
        $result = $this->reviewsDoctorService->canReviews($appointmentId, Auth::id());
        return response()->json([
            'success' => $result['can'],
            'message' => $result['message'] ?? null
        ]);
    }
}
?>