<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewsDoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\throwException;

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

    /**
     * luu danh gia
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        if (!$this->reviewsDoctorService->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
        }

        try {
            $vallidate = $request->validate(
                $this->reviewsDoctorService->rules(),
                $this->reviewsDoctorService->message()
            );
            $review = $this->reviewsDoctorService->store($vallidate, Auth::id());

            return response()->json([
                'sucess' => true,
                'message' => 'Cảm ơn bạn đã đánh giá',
                'review' => $review
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
            ], 500);
        }
    }
}
?>