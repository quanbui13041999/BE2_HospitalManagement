<?php
namespace App\Http\Controllers;

use App\Services\ReviewsDoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewsDoctorController extends Controller
{
    protected ReviewsDoctorService $service;

    public function __construct(ReviewsDoctorService $service)
    {
        $this->service = $service;
    }

    // ─────────────────────────────────────────────
    // Kiểm tra có thể đánh giá không
    // GET /reviews/check?appointment_id=xxx
    // ─────────────────────────────────────────────
    public function checkCanReview(Request $request): JsonResponse
    {
        if (!$this->service->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        $appointmentId = (int) $request->query('appointment_id');
        $result = $this->service->canReview($appointmentId, Auth::id());

        return response()->json([
            'success' => $result['can'],
            'message' => $result['message'] ?? null,
        ]);
    }

    // ─────────────────────────────────────────────
    // Tạo đánh giá mới
    // POST /reviews
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        if (!$this->service->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        try {
            $validated = $request->validate(
                $this->service->rules(),
                $this->service->messages()
            );

            $review = $this->service->store($validated, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã đánh giá!',
                'review'  => $review,
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

    // ─────────────────────────────────────────────
    // Chỉnh sửa đánh giá
    // PUT /reviews/{review}
    // ─────────────────────────────────────────────
    public function update(Request $request, int $review): JsonResponse
    {
        if (!$this->service->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        try {
            $validated = $request->validate([
                'rating'  => ['required', 'integer', 'between:1,5'],
                'comment' => ['nullable', 'string', 'max:1000'],
            ], [
                'rating.required' => 'Vui lòng chọn số sao.',
                'rating.between'  => 'Đánh giá phải từ 1 đến 5 sao.',
                'comment.max'     => 'Nhận xét tối đa 1000 ký tự.',
            ]);

            $isAdmin = Auth::user()->hasRole('admin') ?? false;  // tuỳ hệ thống
            $updated = $this->service->update($review, $validated, Auth::id(), $isAdmin);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được cập nhật.',
                'review'  => $updated,
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

    // ─────────────────────────────────────────────
    // Xóa đánh giá
    // DELETE /reviews/{review}
    // ─────────────────────────────────────────────
    public function destroy(int $review): JsonResponse
    {
        if (!$this->service->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        try {
            $isAdmin = Auth::user()->hasRole('admin') ?? false;
            $this->service->delete($review, Auth::id(), $isAdmin);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được xóa.',
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

    // ─────────────────────────────────────────────
    // Trả lời bình luận (bác sĩ / admin)
    // POST /reviews/{review}/reply
    // ─────────────────────────────────────────────
    public function reply(Request $request, int $review): JsonResponse
    {
        if (!$this->service->auth()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        try {
            $validated = $request->validate([
                'doctor_reply' => ['nullable', 'string', 'max:1000'],
            ], [
                'doctor_reply.max' => 'Phản hồi tối đa 1000 ký tự.',
            ]);

            $user    = Auth::user();
            $isAdmin = $user->hasRole('admin') ?? false;

            // Nếu bác sĩ đăng nhập, truyền doctor_id của họ
            // Giả sử Doctor model có trường user_id liên kết với users
            $doctorUserId = null;
            if ($user->doctor) {
                $doctorUserId = $user->doctor->doctor_id;
            }

            $updated = $this->service->reply(
                $review,
                $validated['doctor_reply'] ?? null,
                Auth::id(),
                $isAdmin,
                $doctorUserId
            );

            return response()->json([
                'success'      => true,
                'message'      => $validated['doctor_reply']
                    ? 'Phản hồi đã được lưu.'
                    : 'Phản hồi đã được xóa.',
                'doctor_reply' => $updated->doctor_reply,
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