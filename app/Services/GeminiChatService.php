<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiChatService
{
    protected ?string $apiKey = null;
    protected ?string $caBundle = null;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->caBundle = config('services.gemini.ca_bundle');
    }

    public function generateReply(int $roomId, string $userMessage): string
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key not configured');
            return 'Xin lỗi, hiện tại hệ thống AI tạm thời gián đoạn. Vui lòng liên hệ nhân viên CSKH để được hỗ trợ.';
        }

        $room = ChatRoom::find($roomId);
        if (!$room) {
            Log::error('Chat room not found: ' . $roomId);
            return 'Xin lỗi, không tìm thấy phòng chat.';
        }

        // Lấy lịch sử 10 tin nhắn gần nhất
        $history = ChatMessage::where('room_id', $roomId)
            ->orderBy('sent_at', 'asc')
            ->take(10)
            ->get()
            ->map(function ($msg) use ($room) {
                $role = $msg->is_ai ? 'model' : 'user';
                return [
                    'role'  => $role,
                    'parts' => [['text' => $msg->message_text]]
                ];
            })
            ->values()
            ->toArray();

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $this->buildSystemPrompt()]]
            ],
            'contents' => array_merge($history, [
                ['role' => 'user', 'parts' => [['text' => $userMessage]]]
            ]),
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 500,
            ]
        ];

        try {
            $request = Http::withQueryParameters(['key' => $this->apiKey]) /* fixed: bat verify TLS khi goi API ngoai */
                ->timeout(15)
                ->withOptions($this->tlsOptions());

            $response = $request->post($this->apiUrl, $payload);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('Gemini API Error Response: ' . $errorBody);

                if (str_contains($errorBody, 'API_KEY_INVALID') || str_contains($errorBody, 'INVALID_ARGUMENT')) {
                    Log::error('Gemini API Key appears to be invalid');
                    return 'Xin lỗi, hệ thống AI không hoạt động. Vui lòng liên hệ nhân viên CSKH để được hỗ trợ ngay.';
                }

                return 'Xin lỗi, hiện tại hệ thống AI tạm thời gián đoạn. Vui lòng thử lại sau hoặc chờ nhân viên CSKH hỗ trợ bạn.';
            }

            $data = $response->json();

            if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Unexpected Gemini API response structure', ['response' => $data]);
                return 'Xin lỗi, không nhận được phản hồi từ hệ thống AI. Vui lòng thử lại.';
            }

            return $data['candidates'][0]['content']['parts'][0]['text'];
        } catch (Throwable $e) {
            Log::error('Gemini API Exception: ' . $this->redactApiKey($e->getMessage())); /* fixed: khong ghi lo API key vao log */
            return 'Xin lỗi, hiện tại hệ thống AI tạm thời gián đoạn. Vui lòng thử lại sau hoặc chờ nhân viên CSKH hỗ trợ bạn.';
        }
    }

    private function tlsOptions(): array
    {
        if ($this->caBundle && is_file($this->caBundle)) {
            return ['verify' => $this->caBundle]; /* fixed: dung CA bundle cuc bo thay vi tat SSL verify */
        }

        return [];
    }

    private function redactApiKey(string $message): string
    {
        return preg_replace('/([?&]key=)[^\s&)]+/i', '$1[redacted]', $message) ?? $message;
    }

    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
Bạn là trợ lý CSKH (Chăm Sóc Khách Hàng) của Bệnh Viện Đa Khoa Trung Tâm.
Nhiệm vụ của bạn là hỗ trợ bệnh nhân về các vấn đề liên quan đến bệnh viện.

PHẠM VI TRẢ LỜI (chỉ trả lời các chủ đề sau):
1. Đặt lịch khám: hướng dẫn đặt lịch, hủy lịch, dời lịch, kiểm tra lịch hẹn
2. Thông tin bác sĩ: chuyên khoa, lịch làm việc, cách chọn bác sĩ
3. Thông tin khoa/phòng: Tim mạch, Phụ sản, Nội tổng quát, Ngoại tổng quát, Nhi khoa, Da liễu, Mắt, Tai Mũi Họng, Răng Hàm Mặt, Thần kinh, Ung bướu, Cơ xương khớp
4. Dịch vụ y tế: danh sách dịch vụ, giá cả, mô tả
5. Vaccine: danh sách vaccine, lịch tiêm
6. Bảo hiểm y tế (BHYT): thông tin thẻ BHYT, tỷ lệ thanh toán
7. Checkin / Check-out: hướng dẫn check-in khi đến viện
8. Giờ làm việc, địa chỉ, liên hệ bệnh viện
9. Bản tin bệnh viện: các thông báo mới nhất, tin tức y tế, kinh nghiệm sức khỏe
10. Hồ sơ bệnh án: hướng dẫn xem kết quả khám

TUYỆT ĐỐI KHÔNG trả lời về:
- Quản lý hệ thống, dashboard admin
- Báo cáo doanh thu, thống kê nội bộ
- Thao tác phân quyền, tài khoản nhân viên

Nếu câu hỏi ngoài phạm vi, hãy trả lời: "Xin lỗi, câu hỏi này nằm ngoài phạm vi hỗ trợ của tôi. Vui lòng liên hệ trực tiếp bệnh viện để được tư vấn."

Trả lời bằng tiếng Việt, thân thiện, ngắn gọn (tối đa 3-4 câu mỗi lần), xưng "tôi" gọi khách là "bạn".
PROMPT;
    }
}
