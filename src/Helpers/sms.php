<?php
// src/Helpers/sms.php
//
// send_sms($to, $message, $otp) — Sends an SMS directly to an Indian mobile number
//
// In India:
//   - Fast2SMS OTP route delivers 6-digit OTPs directly without DLT registration
//   - 2Factor.in API delivers OTPs directly
//   - Twilio sends international SMS to +91 numbers
//   - If SMS_MOCK is true (or SMS_API_KEY is empty), logs to logs/otp_sms.log for inspection

if (!function_exists('send_sms')) {

    /**
     * Send an SMS message / OTP to an Indian mobile number.
     *
     * @param string $to      Recipient phone number (e.g. +919876543210, 9876543210)
     * @param string $message The message text to send
     * @param string $otp     The 6-digit OTP code (used for dedicated OTP routes)
     * @return bool           true on success / mock, false on delivery failure
     */
    function send_sms(string $to, string $message, string $otp = ''): bool
    {
        // Extract digits
        $digits = preg_replace('/[^0-9]/', '', $to);

        // Strip leading 91 or 0 if present to get 10-digit Indian mobile number
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // If raw OTP was not explicitly passed, try extracting 6 consecutive digits from message
        if (empty($otp) && preg_match('/\b([0-9]{6})\b/', $message, $matches)) {
            $otp = $matches[1];
        }

        // ── MOCK / SIMULATION MODE ───────────────────────────────────────────
        // Active when SMS_MOCK is true or no SMS_API_KEY is provided
        if (!defined('SMS_MOCK') || SMS_MOCK || empty(defined('SMS_API_KEY') ? SMS_API_KEY : '')) {
            return _sms_log('+91' . $digits, $message, 'MOCK_LOGGED', $otp);
        }

        // ── LIVE SMS DELIVERY ────────────────────────────────────────────────
        $provider = defined('SMS_PROVIDER') ? strtolower(SMS_PROVIDER) : 'fast2sms';

        switch ($provider) {
            case 'fast2sms':
                return _sms_fast2sms($digits, $message, $otp);
            case '2factor':
                return _sms_2factor($digits, $otp);
            case 'twilio':
                return _sms_twilio('+91' . $digits, $message);
            case 'textlocal':
                return _sms_textlocal($digits, $message);
            default:
                _sms_log('+91' . $digits, $message, 'UNKNOWN_PROVIDER:' . $provider, $otp);
                return false;
        }
    }

    // ── Provider Implementations ─────────────────────────────────────────────

    /**
     * Send via Fast2SMS (India)
     * Fast2SMS 'otp' route delivers 6-digit OTPs directly to Indian mobile numbers.
     */
    function _sms_fast2sms(string $tenDigitNumber, string $message, string $otp): bool
    {
        $apiKey = defined('SMS_API_KEY') ? SMS_API_KEY : '';
        if (empty($apiKey)) {
            _sms_log('+91' . $tenDigitNumber, $message, 'FAIL:fast2sms:no_api_key', $otp);
            return false;
        }

        // Use the Fast2SMS dedicated OTP route if 6-digit OTP is present
        if (!empty($otp)) {
            $url = 'https://www.fast2sms.com/dev/bulkV2';
            $params = [
                'authorization'    => $apiKey,
                'variables_values' => $otp,
                'route'            => 'otp',
                'numbers'          => $tenDigitNumber,
            ];
            $endpoint = $url . '?' . http_build_query($params);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['cache-control: no-cache'],
                CURLOPT_TIMEOUT        => 10,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $body = json_decode($response, true);
            $ok = ($httpCode === 200 && isset($body['return']) && $body['return'] === true);
            _sms_log('+91' . $tenDigitNumber, $message, $ok ? 'SENT:fast2sms:otp' : "FAIL:fast2sms:http{$httpCode}:" . ($body['message'][0] ?? 'error'), $otp);
            return $ok;
        }

        // Fallback to quick SMS route (q)
        $url = 'https://www.fast2sms.com/dev/bulkV2';
        $params = [
            'authorization' => $apiKey,
            'message'       => $message,
            'language'      => 'english',
            'route'         => 'q',
            'numbers'       => $tenDigitNumber,
        ];
        $endpoint = $url . '?' . http_build_query($params);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['cache-control: no-cache'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode($response, true);
        $ok = ($httpCode === 200 && isset($body['return']) && $body['return'] === true);
        _sms_log('+91' . $tenDigitNumber, $message, $ok ? 'SENT:fast2sms:q' : "FAIL:fast2sms:http{$httpCode}", $otp);
        return $ok;
    }

    /**
     * Send via 2Factor.in (India)
     * Direct OTP delivery route for Indian mobile numbers
     */
    function _sms_2factor(string $tenDigitNumber, string $otp): bool
    {
        $apiKey = defined('SMS_API_KEY') ? SMS_API_KEY : '';
        if (empty($apiKey) || empty($otp)) {
            return false;
        }

        $url = "https://2factor.in/API/V1/{$apiKey}/SMS/{$tenDigitNumber}/{$otp}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode($response, true);
        $ok = ($httpCode === 200 && isset($body['Status']) && strtolower($body['Status']) === 'success');
        _sms_log('+91' . $tenDigitNumber, "OTP: {$otp}", $ok ? 'SENT:2factor' : "FAIL:2factor:http{$httpCode}", $otp);
        return $ok;
    }

    /**
     * Send via Twilio REST API
     */
    function _sms_twilio(string $toE164, string $message): bool
    {
        $sid   = defined('SMS_ACCOUNT_SID') ? SMS_ACCOUNT_SID : '';
        $token = defined('SMS_API_KEY')     ? SMS_API_KEY     : '';
        $from  = defined('SMS_FROM')        ? SMS_FROM        : '';

        if (empty($sid) || empty($token) || empty($from)) {
            _sms_log($toE164, $message, 'FAIL:twilio:missing_credentials');
            return false;
        }

        $fromE164 = preg_replace('/[^0-9+]/', '', $from);
        $url  = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $data = http_build_query(['To' => $toE164, 'From' => $fromE164, 'Body' => $message]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_USERPWD        => "{$sid}:{$token}",
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $ok = ($httpCode >= 200 && $httpCode < 300);
        _sms_log($toE164, $message, $ok ? 'SENT:twilio' : "FAIL:twilio:http{$httpCode}");
        return $ok;
    }

    /**
     * Send via TextLocal (India)
     */
    function _sms_textlocal(string $tenDigitNumber, string $message): bool
    {
        $apiKey = defined('SMS_API_KEY') ? SMS_API_KEY : '';
        $sender = defined('SMS_FROM')    ? SMS_FROM    : 'TXTLCL';

        if (empty($apiKey)) {
            return false;
        }

        $url  = 'https://api.textlocal.in/send/';
        $data = http_build_query([
            'apikey'  => $apiKey,
            'numbers' => '91' . $tenDigitNumber,
            'message' => $message,
            'sender'  => $sender,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode($response, true);
        $ok   = ($httpCode === 200 && isset($body['status']) && $body['status'] === 'success');
        _sms_log('+91' . $tenDigitNumber, $message, $ok ? 'SENT:textlocal' : "FAIL:textlocal:http{$httpCode}");
        return $ok;
    }

    // ── Logger Helper ────────────────────────────────────────────────────────

    function _sms_log(string $to, string $message, string $status, string $otp = ''): bool
    {
        $logDir  = defined('ROOT_PATH') ? ROOT_PATH . DIRECTORY_SEPARATOR . 'logs' : dirname(__DIR__, 2) . '/logs';
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'otp_sms.log';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $otpNote = $otp ? " [CODE: {$otp}]" : "";
        $line = sprintf(
            "[%s] [%s] TO:%s%s | %s\n",
            date('Y-m-d H:i:s'),
            $status,
            $to,
            $otpNote,
            $message
        );

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        return true;
    }
}
