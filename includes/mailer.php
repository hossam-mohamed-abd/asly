<?php
/**
 * ASLY | أصلي TECH STORE
 * Branded Bright Daylight HTML Email Notification Engine (Pure PHP TLS/SSL SMTP + mail() + Log)
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
        $stageInfo = $stages[$stageKey] ?? ['order' => 1, 'title' => $stageKey, 'title_ar' => $stageKey, 'title_en' => $stageKey, 'desc' => ''];

        $storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
        $storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
        $trackUrl = publicAbsoluteUrl(
            'track.php?code=' . urlencode((string)$ticket['ticket_code']) . '&t=' . urlencode((string)$ticket['qr_token']),
            $settings['public_base_url'] ?? null
        );

        $stageAr = $stageInfo['title_ar'] ?? $stageInfo['title'];
        $stageEn = $stageInfo['title_en'] ?? $stageInfo['title'];

        $subject = sprintf(
            '[%s | %s] %s (%s) — %s',
            $storeNameEn,
            $storeNameAr,
            $stageAr,
            $ticket['device_model'],
            $ticket['ticket_code']
        );

        $progressStepsHtml = '';
        $currentOrder = (int)$stageInfo['order'];
        foreach ($stages as $k => $st) {
            $order = (int)$st['order'];
            $bg = $order < $currentOrder ? '#14B4B8' : ($order === $currentOrder ? '#E4B453' : '#EEF6F7');
            $color = $order <= $currentOrder ? '#FFFFFF' : '#64748B';
            $progressStepsHtml .= sprintf(
                '<td align="center" style="padding:4px;"><div style="width:28px;height:28px;line-height:28px;border-radius:50%%;background:%s;color:%s;font-weight:bold;font-size:12px;">%d</div><div style="font-size:10px;color:#475569;margin-top:4px;">%s</div></td>',
                $bg,
                $color,
                $order,
                e($st['title_ar'] ?? $st['title'])
            );
        }

        $noteHtml = '';
        if (trim($latestNote) !== '') {
            $noteHtml = sprintf(
                '<div style="margin:18px 0;padding:15px 18px;background:#FFFBEB;border-right:4px solid #E4B453;border-radius:12px;">
                    <div style="font-size:12px;color:#B45309;font-weight:bold;margin-bottom:6px;">ملاحظة مهندس الصيانة • Engineer Note:</div>
                    <div style="font-size:14px;color:#334155;line-height:1.7;">%s</div>
                </div>',
                nl2br(e($latestNote))
            );
        }

        $actionBlock = $stageKey === 'delivered'
            ? '<div style="margin-top:20px;padding:14px;background:#ECFDF5;border:1px solid #A7F3D0;border-radius:12px;color:#059669;font-size:13px;text-align:center;">تم تسليم الجهاز بنجاح وإغلاق التذكرة • Device delivered & closed. Thank you for choosing ASLY TECH STORE!</div>'
            : sprintf(
                '<div style="text-align:center;margin:26px 0 10px;">
                    <a href="%s" style="display:inline-block;padding:13px 30px;background:linear-gradient(135deg,#14B4B8,#0E9599);color:#FFFFFF;text-decoration:none;border-radius:999px;font-weight:bold;font-size:15px;">متابعة حالة الجهاز • Track Repair Live</a>
                </div>',
                e($trackUrl)
            );

        $htmlBody = sprintf(
            '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:24px;background:#FAF9F6;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;color:#1E293B;">
  <div style="max-width:620px;margin:0 auto;background:#FFFFFF;border-radius:20px;overflow:hidden;border:1px solid #E2ECEE;box-shadow:0 12px 32px rgba(20,180,184,0.08);">
    <div style="background:linear-gradient(135deg,#EFFAFA 0%%,#FFFBF0 100%%);padding:26px 28px;border-bottom:3px solid #E4B453;text-align:center;">
      <div style="font-size:28px;font-weight:900;color:#14B4B8;letter-spacing:2px;">ASLY <span style="color:#E4B453;">|</span> أصلي</div>
      <div style="font-size:11px;color:#D6992A;letter-spacing:5px;margin-top:4px;font-weight:bold;">TECH STORE</div>
    </div>
    <div style="padding:28px;">
      <p style="margin:0 0 10px;font-size:16px;color:#1E293B;">مرحباً <strong>%s</strong>،</p>
      <p style="margin:0 0 18px;font-size:14px;color:#52637A;line-height:1.7;">تحديث جديد بخصوص جهازكم المسجل بمعمل الصيانة في <strong>%s (%s)</strong>:</p>

      <div style="background:#EFF9FA;border:1px solid #CBECEE;padding:18px;border-radius:14px;margin-bottom:18px;">
        <div style="font-size:12px;color:#0E8F94;font-weight:bold;margin-bottom:4px;">المرحلة الحالية • Current Stage</div>
        <div style="font-size:20px;font-weight:bold;color:#1E293B;">%s <span style="font-size:14px;color:#52637A;font-weight:normal;">(%s)</span></div>
        <div style="font-size:13px;color:#52637A;margin-top:6px;">%s</div>
      </div>

      <table width="100%%" cellpadding="0" cellspacing="0" style="margin:14px 0 18px;background:#FFFFFF;border:1px solid #E6F0F2;border-radius:12px;padding:10px;">
        <tr>%s</tr>
      </table>

      <table width="100%%" cellpadding="9" cellspacing="0" style="border-collapse:collapse;font-size:13px;color:#1E293B;border:1px solid #E6F0F2;border-radius:10px;">
        <tr style="background:#F8FAFC;"><td style="font-weight:bold;width:36%%;">رقم التذكرة • Ticket:</td><td>%s</td></tr>
        <tr><td style="font-weight:bold;">الجهاز • Device:</td><td>%s</td></tr>
        <tr style="background:#F8FAFC;"><td style="font-weight:bold;">العطل • Issue:</td><td>%s</td></tr>
        <tr><td style="font-weight:bold;">التكلفة المقدرة / العربون:</td><td>%s (%s)</td></tr>
      </table>

      %s
      %s
    </div>
    <div style="background:#FAF9F6;padding:16px 24px;text-align:center;font-size:12px;color:#64748B;border-top:1px solid #E8EEF0;">
      %s (%s) — 📞 %s | %s
    </div>
  </div>
</body>
</html>',
            e((string)$ticket['customer_name']),
            e($storeNameAr),
            e($storeNameEn),
            e($stageAr),
            e($stageEn),
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
            throw new RuntimeException("SMTP connection error ({$host}:{$port}): {$errstr}");
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
                throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
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
                throw new RuntimeException('Failed to enable STARTTLS encryption.');
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
