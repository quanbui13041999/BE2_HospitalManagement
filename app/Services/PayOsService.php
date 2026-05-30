<?php

namespace App\Services;

use PayOS\PayOS;
use PayOS\PayOSOptions;
use GuzzleHttp\Client as GuzzleClient;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class PayOsService
{
    protected ?PayOS $payOS = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $clientId = config('services.payos.client_id');
        $apiKey = config('services.payos.api_key');
        $checksumKey = config('services.payos.checksum_key');

        // Kiểm tra xem đã điền credentials thật chưa
        if (
            $clientId && $apiKey && $checksumKey &&
            !str_contains($clientId, 'your_') &&
            !str_contains($apiKey, 'your_') &&
            !str_contains($checksumKey, 'your_')
        ) {
            try {
                // Tạo Guzzle HTTP Client tùy chỉnh, vô hiệu hóa xác minh SSL để chạy ổn định trên môi trường Localhost Wamp64
                $httpClient = new GuzzleClient([
                    'verify' => false,
                    'timeout' => 30,
                ]);

                // Khởi chạy kích hoạt autoload lớp PayOS để định nghĩa hằng số PAYOS_BASE_URL
                class_exists(PayOS::class);

                $options = new PayOSOptions(
                    clientId: $clientId,
                    apiKey: $apiKey,
                    checksumKey: $checksumKey,
                    httpClient: $httpClient
                );

                $this->payOS = PayOS::options($options);
                $this->isConfigured = true;
            } catch (Exception $e) {
                Log::error('Lỗi khởi tạo PayOS SDK: ' . $e->getMessage());
            }
        } else {
            Log::warning('PayOS chưa được cấu hình credentials thật trong file .env. Hệ thống sẽ tự động sử dụng chế độ Giả lập (Sandbox/Mock).');
        }
    }

    /**
     * Kiểm tra hệ thống PayOS đã được cấu hình thật chưa
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Tạo Link thanh toán VietQR động của PayOS
     * 
     * @param int $paymentId Mã thanh toán (unique integer dùng làm orderCode)
     * @param int $amount Số tiền thanh toán (VND)
     * @param string $description Nội dung chuyển khoản chuyển tiền
     * @param string $returnUrl URL redirect khi thanh toán thành công
     * @param string $cancelUrl URL redirect khi bệnh nhân bấm hủy
     * @return array
     */
    public function createPaymentLink(int $paymentId, int $amount, string $description, string $returnUrl, string $cancelUrl): array
    {
        // Chế độ GIẢ LẬP nếu chưa điền credentials thật
        if (!$this->isConfigured || !$this->payOS) {
            Log::info('PayOS Mock: Tạo link thanh toán giả lập.', ['payment_id' => $paymentId, 'amount' => $amount]);
            
            // Trả về dữ liệu mock tương ứng cấu trúc thực của PayOS
            return [
                'success' => true,
                'mock' => true,
                'checkoutUrl' => route('user.payments.gateway', $paymentId), // Trỏ về gateway giả lập của bạn
                'qrContent' => 'HOSPITAL|MOCK_REF_' . $paymentId . '|' . $amount . '|' . $description,
            ];
        }

        try {
            // Chuẩn hóa mô tả chuyển khoản không dấu, độ dài tối đa 25 ký tự theo quy định Napas / PayOS
            $cleanDescription = $this->sanitizeDescription($description);

            // Khởi tạo model request theo SDK v2
            $requestData = new CreatePaymentLinkRequest(
                orderCode: $paymentId,
                amount: $amount,
                description: $cleanDescription,
                returnUrl: $returnUrl,
                cancelUrl: $cancelUrl
            );

            // Gọi API PayOS thực tế
            $response = $this->payOS->paymentRequests->create($requestData);

            return [
                'success' => true,
                'mock' => false,
                'checkoutUrl' => $response->checkoutUrl,
                'qrContent' => $response->qrCode ?? '',
                'paymentLinkId' => $response->paymentLinkId ?? '',
            ];
        } catch (Exception $e) {
            Log::error('Lỗi khi gọi API PayOS: ' . $e->getMessage());
            
            // Trả về fallback giả lập khi API ngân hàng bị lỗi kết nối
            return [
                'success' => false,
                'message' => 'Không thể kết nối cổng ngân hàng: ' . $e->getMessage(),
                'checkoutUrl' => route('user.payments.gateway', $paymentId),
                'qrContent' => 'HOSPITAL|FALLBACK_' . $paymentId . '|' . $amount . '|' . $description,
                'fallback' => true,
            ];
        }
    }

    /**
     * Xác thực chữ ký webhook từ PayOS gửi về
     * 
     * @param array $payload Dữ liệu thô POST từ PayOS
     * @return object|null Trả về dữ liệu đã verify hoặc null nếu giả mạo
     */
    public function verifyWebhook(array $payload)
    {
        if (!$this->isConfigured || !$this->payOS) {
            Log::warning('PayOS Mock: Nhận webhook giả lập không qua kiểm tra chữ ký.');
            // Trả về một object giả lập để Controller xử lý tiếp
            return (object) [
                'orderCode' => $payload['data']['orderCode'] ?? null,
                'amount' => $payload['data']['amount'] ?? 0,
                'description' => $payload['data']['description'] ?? '',
                'reference' => $payload['data']['reference'] ?? 'MOCK_REF',
            ];
        }

        try {
            // SDK v2 tự động kiểm tra chữ ký SHA256 dựa trên ChecksumKey và trả về object dữ liệu sạch
            return $this->payOS->webhooks->verify($payload);
        } catch (Exception $e) {
            Log::error('Xác thực chữ ký Webhook PayOS THẤT BẠI: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Dọn dẹp ký tự có dấu và ký tự đặc biệt để tuân thủ định dạng của Napas
     */
    private function sanitizeDescription(string $str): string
    {
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|YY',
        );
        foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        
        // Loại bỏ ký tự đặc biệt, chỉ giữ lại chữ, số và dấu cách
        $str = preg_replace('/[^A-Za-z0-9\s]/', '', $str);
        
        // Chuyển thành viết hoa và cắt chuỗi tối đa 25 ký tự (Quy định của PayOS)
        return substr(strtoupper(trim($str)), 0, 25);
    }
}
