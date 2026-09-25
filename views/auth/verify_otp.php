<?php
// views/auth/verify_otp.php
$targetEmail = $email ?? '';
$otpCode = $otp ?? '';
?>

<div class="card" style="max-width: 480px; width: 100%; padding: 2.5rem 2rem; text-align: center;">
    <!-- Email Verification Icon -->
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: #eff6ff; color: var(--primary); border-radius: 18px; margin-bottom: 1.25rem;">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
        </svg>
    </div>

    <h2 style="font-size: 1.55rem; font-weight: 800; margin-bottom: 0.4rem; color: var(--slate-900);">
        Verify Your Email Address
    </h2>
    <p style="font-size: 0.9rem; color: var(--slate-600); margin-bottom: 1.25rem; line-height: 1.55;">
        A 6-digit One-Time Password (OTP) has been dispatched to:
    </p>

    <!-- Email Display Badge -->
    <div style="background: #f8fafc; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.95rem; color: var(--slate-800); display: flex; align-items: center; justify-content: center; gap: 0.5rem; word-break: break-all;">
        <span style="font-size: 1.1rem;">✉️</span>
        <strong><?= e($targetEmail ?: 'your email address') ?></strong>
    </div>

    <!-- Quick Inbox Shortcut (if Gmail) -->
    <?php if (str_contains(strtolower($targetEmail), 'gmail.com')): ?>
        <div style="margin-bottom: 1.5rem;">
            <a href="https://mail.google.com/mail/u/0/#search/TalentTrack" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; font-weight: 600; color: #dc2626; text-decoration: none; background: #fef2f2; padding: 0.45rem 0.9rem; border-radius: 9999px; border: 1px solid #fecaca;">
                <span>🔴 Open Gmail Inbox</span> ↗
            </a>
        </div>
    <?php endif; ?>

    <!-- OTP Input Form -->
    <form method="POST" action="<?= BASE_URL ?>/verify-otp" id="otpForm">
        <?= csrf_field() ?>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" for="otp" style="font-weight: 700; font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">
                Enter 6-Digit Verification Code <span style="color: #ef4444;">*</span>
            </label>
            <input 
                type="text" 
                id="otp" 
                name="otp" 
                class="form-control" 
                maxlength="6" 
                pattern="[0-9]{6}" 
                inputmode="numeric" 
                placeholder="123456" 
                required 
                autofocus 
                autocomplete="one-time-code"
                style="font-size: 1.85rem; font-weight: 800; letter-spacing: 0.4em; text-align: center; max-width: 270px; margin: 0 auto; height: 56px; border: 2px solid var(--primary);"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,6);"
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem 1rem; font-size: 1rem; font-weight: 700;">
            Verify & Complete Registration &rarr;
        </button>
    </form>

    <!-- Resend OTP Action -->
    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--slate-100); font-size: 0.85rem; color: var(--slate-500);">
        Didn't receive the email? Check your spam folder or
        <form method="POST" action="<?= BASE_URL ?>/resend-otp" style="display: inline;">
            <?= csrf_field() ?>
            <button type="submit" style="background: none; border: none; color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                Resend OTP
            </button>
        </form>
    </div>

    <div style="margin-top: 1rem; font-size: 0.82rem;">
        <a href="<?= BASE_URL ?>/login" style="color: var(--slate-500); text-decoration: none;">&larr; Back to Sign In</a>
    </div>

    <!-- Developer / Local Environment Helper Notice -->
    <?php if (defined('EMAIL_MOCK') && EMAIL_MOCK): ?>
        <div style="margin-top: 1.5rem; padding: 0.85rem 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: var(--radius-md); font-size: 0.78rem; color: #64748b; text-align: left; line-height: 1.45;">
            <div style="font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                ℹ️ Email Simulation Active (Local Development)
            </div>
            <div>
                Live delivery sends to any inbox via free Gmail SMTP (set <code>SMTP_USER</code> and <code>SMTP_PASS</code> in <code>config/config.php</code>) or Resend API.
            </div>
            <div style="margin-top: 0.35rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    Dispatched code is recorded in: <code style="background: #e2e8f0; padding: 1px 5px; border-radius: 3px; font-weight: 600; color: #0f172a;">logs/otp_email.log</code>
                </div>
                <?php if (!empty($otpCode)): ?>
                    <button type="button" onclick="autoFillOtp('<?= e($otpCode) ?>')" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; border-radius: 6px; padding: 3px 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                        ⚡ Auto-Fill Code
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function autoFillOtp(code) {
    const input = document.getElementById('otp');
    if (input) {
        input.value = code;
        document.getElementById('otpForm').submit();
    }
}
</script>
