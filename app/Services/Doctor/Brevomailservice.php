<?php

namespace App\Services\Doctor;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Part\TextPart;

/**
 * BrevoMailService
 *
 * Gửi mail qua SMTP bằng Laravel Mailer.
 * Cấu hình SMTP trong .env và config/mail.php.
 */
class BrevoMailService
{
    private string $senderEmail;
    private string $senderName;

    public function __construct()
    {
        $this->senderEmail = (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', 'no-reply@clinic.com'));
        $this->senderName = (string) config('mail.from.name', env('MAIL_FROM_NAME', 'Phòng Khám'));
    }

    public function sendReminder1Day(string $toEmail, string $toName, array $params): bool
    {
        $subject = '📅 Nhắc lịch khám ngày mai – ' . $params['appointment_date'];
        $html = $this->buildReminder1DayHtml($params);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function sendReminder1Hour(string $toEmail, string $toName, array $params): bool
    {
        $subject = '⏰ Lịch khám của bạn bắt đầu sau 1 giờ – ' . $params['appointment_time'];
        $html = $this->buildReminder1HourHtml($params);
        return $this->send($toEmail, $toName, $subject, $html);
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlContent,
        string $textContent = ''
    ): bool {
        if (empty($this->senderEmail)) {
            Log::error('[Brevo] BREVO_SENDER_EMAIL chưa được cấu hình.');
            return false;
        }

        try {
            $mailer = config('mail.default', 'smtp');

            Mail::mailer($mailer)->send([], [], function (Message $message) use ($toEmail, $toName, $subject, $htmlContent, $textContent) {
                $message->from($this->senderEmail, $this->senderName)
                    ->to($toEmail, $toName)
                    ->subject($subject)
                    ->setBody(new TextPart($htmlContent, 'utf-8', 'html'));

                if (!empty($textContent)) {
                    $message->addPart(new TextPart($textContent, 'utf-8', 'plain'));
                }
            });

            Log::info('[Brevo SMTP] Email gửi thành công.', [
                'to' => $toEmail,
                'subject' => $subject,
                'mailer' => $mailer,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('[Brevo SMTP] Exception khi gửi email.', [
                'to' => $toEmail,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function buildReminder1DayHtml(array $p): string
    {
        $cancelBtn = !empty($p['cancel_url'])
            ? '<a href="' . e($p['cancel_url']) . '" style="display:inline-block;padding:10px 22px;background:#dc3545;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;">Hủy lịch</a>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:32px 16px;">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">
        <tr><td style="background:linear-gradient(135deg,#0d6efd,#0a58ca);padding:32px 40px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:24px;">📅 Nhắc Lịch Khám Ngày Mai</h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Đừng quên lịch hẹn của bạn!</p>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <p style="margin:0 0 16px;font-size:16px;color:#333;">Xin chào <strong>{$p['patient_name']}</strong>,</p>
          <p style="margin:0 0 24px;font-size:15px;color:#555;line-height:1.6;">
            Bạn có lịch khám vào <strong>ngày mai</strong>. Vui lòng xem lại thông tin bên dưới và chuẩn bị đầy đủ.
          </p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f7ff;border-radius:10px;border-left:4px solid #0d6efd;">
            <tr><td style="padding:24px 28px;">
              <table width="100%" cellpadding="6" cellspacing="0" style="font-size:15px;color:#333;">
                <tr>
                  <td width="40%" style="color:#666;">🗓 Ngày khám</td>
                  <td><strong>{$p['appointment_date']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">⏰ Giờ khám</td>
                  <td><strong>{$p['appointment_time']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">👨‍⚕️ Bác sĩ</td>
                  <td><strong>{$p['doctor_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">🏥 Chuyên khoa</td>
                  <td><strong>{$p['department_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">🩺 Dịch vụ</td>
                  <td><strong>{$p['service_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">📍 Địa chỉ</td>
                  <td><strong>{$p['clinic_address']}</strong></td>
                </tr>
              </table>
            </td></tr>
          </table>
          <div style="margin:28px 0;padding:20px;background:#fff8e1;border-radius:8px;border-left:4px solid #ffc107;">
            <p style="margin:0 0 8px;font-weight:bold;color:#856404;">💡 Lưu ý trước khi đến khám:</p>
            <ul style="margin:0;padding-left:18px;color:#555;font-size:14px;line-height:1.8;">
              <li>Mang theo CMND/CCCD và sổ khám bệnh (nếu có).</li>
              <li>Đến trước giờ hẹn <strong>15 phút</strong> để làm thủ tục.</li>
              <li>Mang theo đơn thuốc cũ hoặc kết quả xét nghiệm liên quan.</li>
            </ul>
          </div>
          <div style="text-align:center;margin-top:28px;">
            {$cancelBtn}
          </div>
        </td></tr>
        <tr><td style="background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #e9ecef;">
          <p style="margin:0;font-size:13px;color:#999;">Email này được gửi tự động từ hệ thống. Vui lòng không reply.</p>
          <p style="margin:6px 0 0;font-size:13px;color:#999;">{$p['clinic_address']}</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    private function buildReminder1HourHtml(array $p): string
    {
        $queueInfo = !empty($p['queue_number'])
            ? "<tr><td style=\"color:#666;\">🔢 Số thứ tự</td><td><strong>#{$p['queue_number']}</strong></td></tr>"
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:32px 16px;">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">
        <tr><td style="background:linear-gradient(135deg,#fd7e14,#e55a00);padding:32px 40px;text-align:center;">
          <h1 style="margin:0;color:#fff;font-size:24px;">⏰ Lịch Khám Bắt Đầu Sau 1 Giờ!</h1>
          <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Hãy chuẩn bị và di chuyển ngay nhé!</p>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <p style="margin:0 0 16px;font-size:16px;color:#333;">Xin chào <strong>{$p['patient_name']}</strong>,</p>
          <p style="margin:0 0 24px;font-size:15px;color:#555;line-height:1.6;">
            Lịch khám của bạn sẽ bắt đầu <strong>sau khoảng 1 giờ nữa</strong>. Hãy di chuyển sớm để tránh trễ!
          </p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff4f0;border-radius:10px;border-left:4px solid #fd7e14;">
            <tr><td style="padding:24px 28px;">
              <table width="100%" cellpadding="6" cellspacing="0" style="font-size:15px;color:#333;">
                <tr>
                  <td width="40%" style="color:#666;">⏰ Giờ khám</td>
                  <td><strong>{$p['appointment_time']} – {$p['appointment_date']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">👨‍⚕️ Bác sĩ</td>
                  <td><strong>{$p['doctor_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">🏥 Chuyên khoa</td>
                  <td><strong>{$p['department_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">🩺 Dịch vụ</td>
                  <td><strong>{$p['service_name']}</strong></td>
                </tr>
                <tr>
                  <td style="color:#666;">📍 Địa chỉ</td>
                  <td><strong>{$p['clinic_address']}</strong></td>
                </tr>
                {$queueInfo}
              </table>
            </td></tr>
          </table>
          <div style="margin:28px 0;padding:20px;background:#fff8e1;border-radius:8px;border-left:4px solid #ffc107;">
            <p style="margin:0 0 8px;font-weight:bold;color:#856404;">💡 Lưu ý trước khi đến khám:</p>
            <ul style="margin:0;padding-left:18px;color:#555;font-size:14px;line-height:1.8;">
              <li>Đến trước giờ hẹn ít nhất 30 phút.</li>
              <li>Không quên giấy tờ tùy thân và sổ khám bệnh nếu có.</li>
            </ul>
          </div>
        </td></tr>
        <tr><td style="background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #e9ecef;">
          <p style="margin:0;font-size:13px;color:#999;">Email này được gửi tự động từ hệ thống. Vui lòng không reply.</p>
          <p style="margin:6px 0 0;font-size:13px;color:#999;">{$p['clinic_address']}</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }
}
