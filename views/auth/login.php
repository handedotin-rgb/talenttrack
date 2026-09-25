<?php
// views/auth/login.php
?>
<div class="card" style="max-width: 440px; width: 100%; padding: 2.25rem;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; background: var(--primary-light); color: var(--primary); border-radius: 12px; margin-bottom: 1rem;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
        </div>
        <h2 style="font-size: 1.5rem; margin-bottom: 0.35rem; color: var(--slate-900);">Sign In</h2>
        <p style="font-size: 0.88rem; color: var(--slate-500);">Enter your email address and password to access your account</p>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/login" id="loginForm">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label" for="email">
                Email Address <span style="color: #ef4444;">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@domain.com" required autofocus autocomplete="email">
        </div>

        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <label class="form-label" for="password" style="margin-bottom: 0;">
                    Password <span style="color: #ef4444;">*</span>
                </label>
            </div>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem; padding: 0.75rem 1rem; font-weight: 600; font-size: 0.95rem;">
            Sign In
        </button>
    </form>

    <div style="margin-top: 1.75rem; text-align: center; font-size: 0.88rem; color: var(--slate-500); border-top: 1px solid var(--slate-100); padding-top: 1.25rem;">
        Don't have an account yet? 
        <a href="<?= BASE_URL ?>/register" style="font-weight: 600; color: var(--primary);">Create an account</a>
    </div>
</div>
