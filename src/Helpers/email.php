<?php
// src/Helpers/email.php
//
// Zero-dependency transactional email helper for TalentTrack.
// Supports:
//   1. Free Gmail / Custom SMTP (over SSL/TLS via pure PHP sockets — no Composer needed)
//   2. Resend API (3,000 free emails/month via simple cURL)
//   3. PHP built-in mail() function
//   4. Mock logger (logs outgoing emails to logs/otp_email.log when credentials are not set)

if (!function_exists('send_email_otp')) {

    /**
     * Send a branded OTP verification email to the user.
     *
     * @param string $toEmail       Recipient email address
     * @param string $recipientName Recipient full name
     * @param string $otp           6-digit One-Time Password
     * @return bool                 true if sent/mocked successfully
     */
    function send_email_otp(string $toEmail, string $recipientName, string $otp): bool
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'TalentTrack';
        $subject = "[{$appName}] Your Verification Code is: {$otp}";

        $cleanName = htmlspecialchars($recipientName ?: 'Valued User', ENT_QUOTES, 'UTF-8');
        $cleanOtp  = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 35px 15px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 520px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af, #2563eb); padding: 26px 30px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em;">
                                {$appName}
                            </div>
                            <div style="color: #bfdbfe; font-size: 13px; margin-top: 4px;">
                                Account Verification & Security
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <h2 style="margin: 0 0 12px; font-size: 19px; color: #0f172a; font-weight: 700;">
                                Hello, {$cleanName}!
                            </h2>
                            <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #475569;">
                                Thank you for creating an account with <strong>{$appName}</strong>. To complete your registration and guarantee data integrity, please use the 6-digit verification code below:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #eff6ff; border: 2px dashed #3b82f6; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 24px;">
                                <div style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">
                                    One-Time Password (OTP)
                                </div>
                                <div style="font-size: 34px; font-weight: 800; color: #1e3a8a; letter-spacing: 0.28em; font-family: 'Courier New', monospace; margin: 4px 0;">
                                    {$cleanOtp}
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 6px;">
                                    ⏱ Valid for <strong>15 minutes</strong>
                                </div>
                            </div>

                            <p style="margin: 0 0 8px; font-size: 13px; line-height: 1.5; color: #64748b;">
                                • If you did not initiate this request, you can safely ignore this email.<br>
                                • Never share this code with anyone.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 18px 30px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                            &copy; {$appName}. All rights reserved. Automated security email, do not reply.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        return send_email($toEmail, $subject, $html, $recipientName);
    }

    /**
     * Send an HTML email via configured provider (SMTP, Resend API, mail(), or Mock Logger)
     */
    function send_email(string $toEmail, string $subject, string $htmlBody, string $recipientName = ''): bool
    {
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            _email_log($toEmail, $subject, 'FAIL:invalid_recipient_email');
            return false;
        }

        // ── 1. MOCK / SIMULATION MODE ─────────────────────────────────────────
        // Active when EMAIL_MOCK is true or no SMTP/API credentials are set
        $hasSmtp   = defined('SMTP_USER') && !empty(SMTP_USER) && defined('SMTP_PASS') && !empty(SMTP_PASS);
        $hasResend = defined('RESEND_API_KEY') && !empty(RESEND_API_KEY);
        $isMock    = defined('EMAIL_MOCK') ? (bool)EMAIL_MOCK : (!$hasSmtp && !$hasResend);

        if ($isMock || (!$hasSmtp && !$hasResend)) {
            return _email_log($toEmail, $subject, 'MOCK_LOGGED', $htmlBody);
        }

        // ── 2. RESEND API (if configured) ────────────────────────────────────
        if ($hasResend) {
            $ok = _send_via_resend($toEmail, $subject, $htmlBody, $recipientName);
            if ($ok) return true;
        }

        // ── 3. DIRECT SMTP (Gmail or custom SMTP) ────────────────────────────
        if ($hasSmtp) {
            $ok = _send_via_smtp($toEmail, $subject, $htmlBody, $recipientName);
            if ($ok) return true;
        }

        // ── 4. PHP mail() FALLBACK ───────────────────────────────────────────
        $fromEmail = defined('SMTP_FROM') && !empty(SMTP_FROM) ? SMTP_FROM : 'no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $fromName  = defined('SMTP_FROM_NAME') && !empty(SMTP_FROM_NAME) ? SMTP_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'TalentTrack');

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";

        $mailOk = @mail($toEmail, $subject, $htmlBody, $headers);
        _email_log($toEmail, $subject, $mailOk ? 'SENT:php_mail' : 'FAIL:php_mail');
        return $mailOk;
    }

    // ── Resend API Implementation ────────────────────────────────────────────

    function _send_via_resend(string $toEmail, string $subject, string $htmlBody, string $recipientName): bool
    {
        $apiKey    = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
        $fromEmail = defined('SMTP_FROM') && !empty(SMTP_FROM) ? SMTP_FROM : 'onboarding@resend.dev';
        $fromName  = defined('SMTP_FROM_NAME') && !empty(SMTP_FROM_NAME) ? SMTP_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'TalentTrack');

        $payload = json_encode([
            'from'    => "{$fromName} <{$fromEmail}>",
            'to'      => [$toEmail],
            'subject' => $subject,
            'html'    => $htmlBody,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $ok = ($httpCode >= 200 && $httpCode < 300);
        _email_log($toEmail, $subject, $ok ? 'SENT:resend' : "FAIL:resend:http{$httpCode}");
        return $ok;
    }

    // ── Pure PHP SSL/TLS SMTP Implementation (No Composer Needed) ────────────

    function _send_via_smtp(string $toEmail, string $subject, string $htmlBody, string $recipientName): bool
    {
        $host     = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $port     = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $username = defined('SMTP_USER') ? SMTP_USER : '';
        $password = defined('SMTP_PASS') ? SMTP_PASS : '';
        $fromEmail= defined('SMTP_FROM') && !empty(SMTP_FROM) ? SMTP_FROM : $username;
        $fromName = defined('SMTP_FROM_NAME') && !empty(SMTP_FROM_NAME) ? SMTP_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'TalentTrack');

        $transport = ($port === 465) ? 'ssl://' . $host : $host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ]);

        $socket = @stream_socket_client("{$transport}:{$port}", $errno, $errstr, 12, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            _email_log($toEmail, $subject, "FAIL:smtp_connect:{$errstr}");
            return false;
        }

        $read = function() use ($socket) {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $data;
        };

        $write = function(string $cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $read(); // Initial greeting

        $write("EHLO " . gethostname());
        $read();

        // STARTTLS if port 587
        if ($port === 587) {
            $write("STARTTLS");
            $resp = $read();
            if (strpos($resp, '220') !== 0) {
                fclose($socket);
                return false;
            }
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write("EHLO " . gethostname());
            $read();
        }

        // AUTH LOGIN
        $write("AUTH LOGIN");
        $read();
        $write(base64_encode($username));
        $read();
        $write(base64_encode($password));
        $authResp = $read();

        if (strpos($authResp, '235') === false) {
            fclose($socket);
            _email_log($toEmail, $subject, "FAIL:smtp_auth_failed");
            return false;
        }

        // MAIL FROM & RCPT TO
        $write("MAIL FROM: <{$fromEmail}>");
        $read();
        $write("RCPT TO: <{$toEmail}>");
        $read();

        // DATA
        $write("DATA");
        $read();

        $headers  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
        $headers .= "To: <{$toEmail}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        $write($headers . "\r\n" . $htmlBody . "\r\n.");
        $read();

        $write("QUIT");
        $read();
        fclose($socket);

        _email_log($toEmail, $subject, "SENT:smtp:{$host}");
        return true;
    }

    // ── Log File Helper ──────────────────────────────────────────────────────

    function _email_log(string $toEmail, string $subject, string $status, string $content = ''): bool
    {
        $logDir  = defined('ROOT_PATH') ? ROOT_PATH . DIRECTORY_SEPARATOR . 'logs' : dirname(__DIR__, 2) . '/logs';
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'otp_email.log';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // Extract 6-digit OTP code if present for quick readability in log
        $otp = '';
        if (preg_match('/\b([0-9]{6})\b/', $subject . ' ' . $content, $m)) {
            $otp = $m[1];
        }

        $otpNote = $otp ? " [OTP: {$otp}]" : "";
        $line = sprintf(
            "[%s] [%s] TO:%s%s | %s\n",
            date('Y-m-d H:i:s'),
            $status,
            $toEmail,
            $otpNote,
            $subject
        );

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        return true;
    }
}
