<?php
/**
 * ASLY | أصلي TECH STORE
 * Branded HTML Email Notification Engine (Pure PHP TLS/SSL SMTP + mail() + Log)
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

class AslyMailer
{
    /**
     * Send branded maintenance stage update email when customer has registered an email.
     */
    public static function sendMaintenanceStageEmail(
        PDO $pdo,
        array $ticket,
        string $stageKey,
        string $latestNote = '',
        bool $recordInLog = true
    ): array {
        $email = trim((string)($ticket['customer_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'reason' => 'no_valid_email'];
        }

        $settings = getAllSettings($pdo);
        $stages = getMaintenanceStages();
        $stageInfo = $stages[$stageKey] ?? ['order' => 1, 'title' => $stageKey, 'desc' => ''];

        $storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
        $storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
        $trackUrl = publicAbsoluteUrl(
            'track.php?code=' . urlencode((string)$ticket['ticket_code']) . '&t=' . urlencode((string)$ticket['qr_token']),
            $settings['public_base_url'] ?? null
        );

        $subject = sprintf(
            '[%s] تحديث حالة جهازك (%s): %s - تذكرة %s',
            $storeNameAr,
            $ticket['device_model'],
            $stageInfo['title'],
            $ticket['ticket_code']
        );

        $progressStepsHtml = '';
        $currentOrder = (int)$stageInfo['order'];
        foreach ($stages as $k => $st) {
            $order = (int)$st['order'];
            $bg = $order < $currentOrder ? '#085A5E' : ($order === $currentOrder ? '#D49B27' : '#E5ECEC');
            $color = $order <= $currentOrder ? '#FFFFFF' : '#6D8082';
            $progressStepsHtml .= sprintf(
                '<td align="center" style="padding:4px;"><div style="width:28px;height:28px;line-height:28px;border-radius:50%%;background:%s;color:%s;font-weight:bold;font-size:12px;">%d</div><div style="font-size:10px;color:#334E50;margin-top:4px;">%s</div></td>',
                $bg,
                $color,
                $order,
                e($st['title'])
            );
        }

        $noteHtml = '';
        if (trim($latestNote) !== '') {
            $noteHtml = sprintf(
                '<div style="margin:18px 0;padding:15px 18px;background:#F4F9F9;border-right:4px solid #D49B27;border-radius:8px;">
                    <div style="font-size:12px;color:#085A5E;font-weight:bold;margin-bottom:6px;">ملاحظة مهندس الصيانة بالمرحلة الحالية:</div>
                    <div style="font-size:14px;color:#162D2F;line-height:1.7;">%s</div>
                </div>',
                nl2br(e($latestNote))
            );
        }

        $actionBlock = $stageKey === 'delivered'
            ? '<div style="margin-top:20px;padding:14px;background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;color:#065F46;font-size:13px;text-align:center;">تم تسليم الجهاز بنجاح وإغلاق التذكرة. نشكركم لثقتكم الغالية في مركز أصلي ASLY TECH STORE!</div>'
            : sprintf(
                '<div style="text-align:center;margin:26px 0 10px;">
                    <a href="%s" style="display:inline-block;padding:13px 28px;background:linear-gradient(135deg,#074F53,#043538);color:#FFE082;text-decoration:none;border-radius:10px;font-weight:bold;font-size:15px;border:1px solid #D49B27;">متابعة حالة الجهاز وإضافة تعليق</a>
                </div>',
                e($trackUrl)
            );

        $htmlBody = sprintf(
            '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:24px;background:#EFF5F5;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;">
  <div style="max-width:620px;margin:0 auto;background:#FFFFFF;border-radius:16px;overflow:hidden;border:1px solid #D7E5E5;box-shadow:0 8px 24px rgba(4,53,56,0.08);">
    <div style="background:linear-gradient(135deg,#043538 0%%,#085A5E 100%%);padding:26px 28px;border-bottom:4px solid #D49B27;text-align:center;">
      <div style="font-size:26px;font-weight:900;color:#FFFFFF;letter-spacing:1px;">ASLY <span style="color:#F2C75C;">|</span> أصلي</div>
      <div style="font-size:12px;color:#F2C75C;letter-spacing:4px;margin-top:4px;">TECH STORE</div>
    </div>
    <div style="padding:28px;">
      <p style="margin:0 0 12px;font-size:16px;color:#102A2C;">أهلاً بك أستاذ/ <strong>%s</strong>،</p>
      <p style="margin:0 0 18px;font-size:14px;color:#425B5D;line-height:1.7;">نود إعلامكم بتحديث جديد بخصوص جهازكم المسجل بقسم الصيانة لدى <strong>%s</strong>:</p>

      <div style="background:#063F42;color:#FFFFFF;padding:18px;border-radius:12px;margin-bottom:18px;">
        <div style="font-size:12px;color:#F2C75C;margin-bottom:4px;">المرحلة الحالية للجهاز</div>
        <div style="font-size:20px;font-weight:bold;">%s</div>
        <div style="font-size:13px;color:#D6ECEC;margin-top:6px;">%s</div>
      </div>

      <table width="100%%" cellpadding="0" cellspacing="0" style="margin:14px 0 18px;background:#FAFDFD;border:1px solid #E3EEEE;border-radius:10px;padding:10px;">
        <tr>%s</tr>
      </table>

      <table width="100%%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:13px;color:#1D383A;border:1px solid #E5EFEF;border-radius:8px;">
        <tr style="background:#F7FAFA;"><td style="font-weight:bold;width:36%%;">رقم تذكرة الصيانة:</td><td>%s</td></tr>
        <tr><td style="font-weight:bold;">الجهاز والموديل:</td><td>%s</td></tr>
        <tr style="background:#F7FAFA;"><td style="font-weight:bold;">وصف العطل:</td><td>%s</td></tr>
        <tr><td style="font-weight:bold;">التكلفة المقدرة / العربون:</td><td>%s (المدفوع مقدماً: %s)</td></tr>
      </table>

      %s
      %s
    </div>
    <div style="background:#F6F9F9;padding:16px 24px;text-align:center;font-size:12px;color:#5B7476;border-top:1px solid #E4EEEE;">
      %s (%s) — للاستفسار: %s | %s
    </div>
  </div>
</body>
</html>',
            e((string)$ticket['customer_name']),
            e($storeNameAr),
            e($stageInfo['title']),
            e($stageInfo['desc']),
            $progressStepsHtml,
            e((string)$ticket['ticket_code']),
            e((string)$ticket['device_model']),
            e((string)$ticket['problem_description']),
            formatMoney((float)($ticket['estimated_cost'] ?? 0)),
            formatMoney((float)($ticket['deposit_paid'] ?? 0)),
            $noteHtml,
            $actionBlock,
            e($storeNameAr),
            e($storeNameEn),
            e($settings['primary_phone'] ?: '01015981677'),
            e($settings['secondary_phone'] ?: '01154639718')
        );

        $result = self::deliverEmail($settings, $email, $subject, $htmlBody);

        if ($recordInLog) {
            $stmt = $pdo->prepare("
                INSERT INTO email_logs (ticket_id, recipient_email, subject, stage_key, status, body_preview, error_message, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                isset($ticket['id']) ? (int)$ticket['id'] : null,
                $email,
                $subject,
                $stageKey,
                $result['status'],
                $htmlBody,
                $result['error'] ?? null,
                date('Y-m-d H:i:s'),
            ]);
        }

        return $result;
    }

    public static function deliverEmail(array $settings, string $to, string $subject, string $htmlBody): array
    {
        $fromEmail = trim($settings['smtp_from_email'] ?? '') ?: 'noreply@aslystore.com';
        $fromName  = trim($settings['smtp_from_name'] ?? '') ?: 'ASLY | أصلي TECH STORE';
        $smtpEnabled = ($settings['smtp_enabled'] ?? '0') === '1';

        if ($smtpEnabled && trim($settings['smtp_host'] ?? '') !== '') {
            try {
                self::sendViaSmtp(
                    trim($settings['smtp_host']),
                    (int)($settings['smtp_port'] ?: 587),
                    strtolower(trim($settings['smtp_encryption'] ?: 'tls')),
                    trim($settings['smtp_username'] ?? ''),
                    (string)($settings['smtp_password'] ?? ''),
                    $fromEmail,
                    $fromName,
                    $to,
                    $subject,
                    $htmlBody
                );
                return ['sent' => true, 'status' => 'sent_smtp', 'error' => null];
            } catch (Throwable $e) {
                return ['sent' => false, 'status' => 'smtp_error', 'error' => $e->getMessage()];
            }
        }

        // Try PHP mail() if configured on server, otherwise store cleanly in local outbox log for XAMPP testing
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "From: {$encodedFromName} <{$fromEmail}>",
            "Reply-To: {$fromEmail}",
        ]);

        $sentMail = false;
        if (function_exists('mail') && ini_get('sendmail_path')) {
            $sentMail = @mail($to, $encodedSubject, chunk_split(base64_encode($htmlBody)), $headers);
        }

        return [
            'sent'   => $sentMail,
            'status' => $sentMail ? 'sent_mail' : 'queued_outbox',
            'error'  => null,
        ];
    }

    /**
     * Lightweight pure-PHP RFC-compliant SMTP client (SSL & STARTTLS) — Zero external libraries needed on XAMPP!
     */
    private static function sendViaSmtp(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $htmlBody
    ): void {
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote, $errno, $errstr, 12, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            throw new RuntimeException("تعذر الاتصال بخادم SMTP ({$host}:{$port}): {$errstr}");
        }
        stream_set_timeout($socket, 12);

        $expect = static function ($fp, array $codes): string {
            $response = '';
            while (($line = fgets($fp, 515)) !== false) {
                $response .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            $code = (int)substr($response, 0, 3);
            if (!in_array($code, $codes, true)) {
                throw new RuntimeException('رد غير متوقع من خادم البريد: ' . trim($response));
            }
            return $response;
        };

        $cmd = static function ($fp, string $command, array $codes) use ($expect): string {
            fwrite($fp, $command . "\r\n");
            return $expect($fp, $codes);
        };

        $expect($socket, [220]);
        $cmd($socket, 'EHLO aslystore.local', [250]);

        if ($encryption === 'tls') {
            $cmd($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('تعذر تفعيل تشفير TLS مع خادم البريد.');
            }
            $cmd($socket, 'EHLO aslystore.local', [250]);
        }

        if ($username !== '') {
            $cmd($socket, 'AUTH LOGIN', [334]);
            $cmd($socket, base64_encode($username), [334]);
            $cmd($socket, base64_encode($password), [235]);
        }

        $cmd($socket, "MAIL FROM:<{$fromEmail}>", [250]);
        $cmd($socket, "RCPT TO:<{$toEmail}>", [250, 251]);
        $cmd($socket, 'DATA', [354]);

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFrom = '=?UTF-8?B?' . base64_encode($fromName) . "?= <{$fromEmail}>";
        $msg = implode("\r\n", [
            "Date: " . date('r'),
            "To: <{$toEmail}>",
            "From: {$encodedFrom}",
            "Subject: {$encodedSubject}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "",
            rtrim(chunk_split(base64_encode($htmlBody))),
            ".",
        ]);

        $cmd($socket, $msg, [250]);
        @fwrite($socket, "QUIT\r\n");
        fclose($socket);
    }
}
