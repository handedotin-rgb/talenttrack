<?php
// src/Controllers/AuthController.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/Helpers/auth.php';
require_once dirname(__DIR__) . '/Helpers/csrf.php';
require_once dirname(__DIR__) . '/Helpers/flash.php';
require_once dirname(__DIR__) . '/Helpers/view.php';
require_once dirname(__DIR__) . '/Helpers/email.php';

class AuthController {
    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    public function showLogin(): void {
        if (is_authenticated()) {
            $user = auth_user();
            header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard');
            exit;
        }

        render_view('auth/login', [
            'pageTitle' => 'Sign In - ' . APP_NAME
        ], 'auth');
    }

    public function login(): void {
        verify_csrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            set_flash('error', 'Please provide both your registered email address and password.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            set_flash('error', 'Invalid email address or password. Please verify your credentials and try again.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Unverified account check - trigger Email OTP verification
        if ((int)($user['is_verified'] ?? 0) === 0) {
            $newOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $this->db->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?")->execute([$newOtp, $expiresAt, $user['id']]);

            $_SESSION['pending_verification_user_id'] = (int)$user['id'];
            $_SESSION['pending_verification_email']   = $user['email'];
            $_SESSION['pending_verification_role']    = $user['role'];

            send_email_otp($user['email'], $user['name'] ?? '', $newOtp);

            set_flash('warning', "Your account has not been verified yet. A 6-digit verification code has been dispatched to {$user['email']}. Please enter it below.");
            header('Location: ' . BASE_URL . '/verify-otp');
            exit;
        }

        // Recruiter approval check
        if ($user['role'] === 'recruiter') {
            if ($user['status'] === 'pending') {
                set_flash('warning', 'Your recruiter account is currently pending administrator verification and approval. You will be able to log in once an admin activates your account.');
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            if ($user['status'] === 'rejected') {
                set_flash('error', 'Your recruiter registration request was declined by an administrator. Please contact support.');
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }

        // Deactivated or suspended account check
        if ($user['status'] === 'inactive' || $user['status'] === 'suspended') {
            set_flash('error', 'Your account has been deactivated or suspended. Please contact platform support.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        login_user($user);
        audit_log($this->db, $user['id'], 'USER_LOGIN', 'users', $user['id'], "User logged in with credentials (role: {$user['role']}).");
        set_flash('success', "Welcome back, {$user['name']}!");

        $redirect = $_SESSION['intended_url'] ?? (BASE_URL . '/' . $user['role'] . '/dashboard');
        unset($_SESSION['intended_url']);
        header('Location: ' . $redirect);
        exit;
    }

    public function demoLogin(): void {
        // Demo 1-click bypass is removed as per requirements
        set_flash('info', 'Demo logins are disabled. Please sign in with your email and password.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function showRegister(): void {
        if (is_authenticated()) {
            $user = auth_user();
            header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard');
            exit;
        }

        render_view('auth/register', [
            'pageTitle' => 'Create Account - ' . APP_NAME
        ], 'auth');
    }

    public function register(): void {
        verify_csrf();

        $role = $_POST['role'] ?? 'candidate';
        if (!in_array($role, ['candidate', 'recruiter'], true)) {
            $role = 'candidate';
        }

        // Common Fields
        $name = trim($_POST['name'] ?? '');
        $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation - Common Fields
        if (strlen($name) < 2) {
            set_flash('error', 'Full Name is required and must be at least 2 characters.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            set_flash('error', 'Please enter a valid email address (e.g. name@company.com).');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Indian phone number validation: clean and check for 10 digits starting with 6, 7, 8, or 9
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
            $cleanPhone = substr($cleanPhone, 2);
        } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = substr($cleanPhone, 1);
        }

        if (!preg_match('/^[6-9][0-9]{9}$/', $cleanPhone)) {
            set_flash('error', 'Please provide a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $formattedPhone = '+91' . $cleanPhone;

        // Check for existing user with this phone
        $phoneCheck = $this->db->prepare("SELECT id FROM users WHERE phone = ? OR phone = ? LIMIT 1");
        $phoneCheck->execute([$formattedPhone, $cleanPhone]);
        if ($phoneCheck->fetch()) {
            set_flash('error', 'An account with this mobile number already exists. Please sign in instead.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Location
        if (strlen($location) < 2) {
            set_flash('error', 'Location (City, State/Country) is a required field.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Password
        if (strlen($password) < 6) {
            set_flash('error', 'Password must be at least 6 characters long.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        if ($password !== $passwordConfirm) {
            set_flash('error', 'Password and Confirm Password do not match.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Check for existing user with this email
        $checkStmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            set_flash('error', 'An account with this email address already exists. Please sign in instead.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Generate 6-Digit OTP
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if ($role === 'candidate') {
            // Candidate specific fields
            $dob = trim($_POST['dob'] ?? '');
            $degree = trim($_POST['degree'] ?? '');
            $institution = trim($_POST['institution'] ?? '');
            $cgpaInput = trim($_POST['cgpa'] ?? '');
            $marksInput = trim($_POST['marks'] ?? '');
            $headline = trim($_POST['headline'] ?? '');

            if (empty($dob)) {
                set_flash('error', 'Date of Birth is a required field.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // DOB age validation: must be at least 16 years old
            $birthTimestamp = strtotime($dob);
            if (!$birthTimestamp || $birthTimestamp > strtotime('-16 years')) {
                set_flash('error', 'You must be at least 16 years of age to register.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            if (empty($degree)) {
                set_flash('error', 'Please select your educational qualification / degree.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            if (strlen($institution) < 2) {
                set_flash('error', 'College / University name is a required field.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            if ($cgpaInput === '' || !is_numeric($cgpaInput)) {
                set_flash('error', 'CGPA is required and must be a numeric value.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            $cgpa = round((float)$cgpaInput, 2);
            if ($cgpa < 0 || $cgpa > 10) {
                set_flash('error', 'CGPA must be a valid number between 0.00 and 10.00.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            if ($marksInput === '' || !is_numeric($marksInput)) {
                set_flash('error', 'Marks / Percentage is required and must be numeric.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            $marks = round((float)$marksInput, 2);
            if ($marks < 0 || $marks > 100) {
                set_flash('error', 'Marks / Percentage must be between 0.0% and 100.0%.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // DO NOT INSERT INTO DB YET - WITHHOLD UNTIL OTP VERIFICATION!
            $_SESSION['pending_registration'] = [
                'role'            => 'candidate',
                'name'            => $name,
                'email'           => $email,
                'phone'           => $formattedPhone,
                'clean_phone'     => $cleanPhone,
                'location'        => $location,
                'password_hash'   => $passwordHash,
                'dob'             => $dob,
                'degree'          => $degree,
                'institution'     => $institution,
                'cgpa'            => $cgpa,
                'marks'           => $marks,
                'headline'        => $headline,
                'company_name'    => null,
                'company_website' => null,
                'otp_code'        => $otp,
                'otp_expires_at'  => $otpExpiresAt,
            ];

        } else {
            // Recruiter specific fields
            $companyName = trim($_POST['company_name'] ?? '');
            $companyWebsite = trim($_POST['company_website'] ?? '');
            $headline = trim($_POST['recruiter_headline'] ?? '');

            if (strlen($companyName) < 2) {
                set_flash('error', 'Company / Organization Name is a required field for recruiter registration.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            if (strlen($headline) < 2) {
                set_flash('error', 'Official Job Designation / Title is required for recruiter registration.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // DO NOT INSERT INTO DB YET - WITHHOLD UNTIL OTP VERIFICATION!
            $_SESSION['pending_registration'] = [
                'role'            => 'recruiter',
                'name'            => $name,
                'email'           => $email,
                'phone'           => $formattedPhone,
                'clean_phone'     => $cleanPhone,
                'location'        => $location,
                'password_hash'   => $passwordHash,
                'dob'             => null,
                'degree'          => null,
                'institution'     => null,
                'cgpa'            => null,
                'marks'           => null,
                'headline'        => $headline,
                'company_name'    => $companyName,
                'company_website' => $companyWebsite,
                'otp_code'        => $otp,
                'otp_expires_at'  => $otpExpiresAt,
            ];
        }

        // Dispatch OTP directly to user email
        send_email_otp($email, $name, $otp);

        set_flash('info', "A 6-digit verification code has been dispatched to {$email}. Please check your inbox and enter it below to complete registration.");
        header('Location: ' . BASE_URL . '/verify-otp');
        exit;
    }

    public function showVerifyOtp(): void {
        if (is_authenticated()) {
            $user = auth_user();
            header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard');
            exit;
        }

        $email = '';
        $otp   = '';

        if (isset($_SESSION['pending_registration'])) {
            $email = $_SESSION['pending_registration']['email'] ?? '';
            $otp   = $_SESSION['pending_registration']['otp_code'] ?? '';
        } elseif (isset($_SESSION['pending_verification_user_id'])) {
            $userId = (int)$_SESSION['pending_verification_user_id'];
            $stmt = $this->db->prepare("SELECT email, otp_code FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();
            $email = $_SESSION['pending_verification_email'] ?? ($u['email'] ?? '');
            $otp   = $u['otp_code'] ?? '';
        } else {
            set_flash('error', 'No pending registration found to verify. Please fill out the registration form.');
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        render_view('auth/verify_otp', [
            'pageTitle' => 'Verify Email - ' . APP_NAME,
            'email'     => $email,
            'otp'       => $otp,
        ], 'auth');
    }

    public function verifyOtp(): void {
        verify_csrf();

        $enteredOtp = trim($_POST['otp'] ?? '');
        if (strlen($enteredOtp) !== 6 || !ctype_digit($enteredOtp)) {
            set_flash('error', 'Please enter a valid 6-digit numeric OTP code.');
            header('Location: ' . BASE_URL . '/verify-otp');
            exit;
        }

        // Case A: New Registration Verification (User not yet created in DB!)
        if (isset($_SESSION['pending_registration'])) {
            $pending = $_SESSION['pending_registration'];

            // Validate expiry
            if (!empty($pending['otp_expires_at']) && strtotime($pending['otp_expires_at']) < time()) {
                set_flash('error', 'The verification code has expired. Please click "Resend OTP" to generate a fresh code.');
                header('Location: ' . BASE_URL . '/verify-otp');
                exit;
            }

            // Validate code
            if (empty($pending['otp_code']) || $pending['otp_code'] !== $enteredOtp) {
                set_flash('error', 'Invalid verification code. Please check the 6-digit OTP and try again.');
                header('Location: ' . BASE_URL . '/verify-otp');
                exit;
            }

            // Ensure email wasn't taken during the verification window
            $checkEmail = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkEmail->execute([$pending['email']]);
            if ($checkEmail->fetch()) {
                unset($_SESSION['pending_registration']);
                set_flash('error', 'An account with this email address already exists. Please sign in.');
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            // --- USER IS NOW VERIFIED! INSERT INTO DATABASE ONLY NOW ---
            if ($pending['role'] === 'candidate') {
                $insertStmt = $this->db->prepare("
                    INSERT INTO users (
                        name, email, password_hash, role, phone, location,
                        dob, degree, institution, cgpa, marks, headline,
                        status, is_verified, otp_code, otp_expires_at
                    ) VALUES (?, ?, ?, 'candidate', ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, NULL, NULL)
                ");
                $insertStmt->execute([
                    $pending['name'],
                    $pending['email'],
                    $pending['password_hash'],
                    $pending['phone'],
                    $pending['location'],
                    $pending['dob'],
                    $pending['degree'],
                    $pending['institution'],
                    $pending['cgpa'],
                    $pending['marks'],
                    $pending['headline'],
                ]);

                $userId = (int)$this->db->lastInsertId();
                audit_log($this->db, $userId, 'CANDIDATE_EMAIL_VERIFIED', 'users', $userId, "Candidate verified email OTP and completed registration.");

                unset($_SESSION['pending_registration']);

                // Fetch new user and log in
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $verifiedUser = $stmt->fetch();

                login_user($verifiedUser);
                set_flash('success', "Email verified successfully! Welcome to " . APP_NAME . ", {$verifiedUser['name']}!");
                header('Location: ' . BASE_URL . '/candidate/dashboard');
                exit;

            } else {
                // Recruiter
                $insertStmt = $this->db->prepare("
                    INSERT INTO users (
                        name, email, password_hash, role, company_name, company_website,
                        headline, phone, location, status, is_verified, otp_code, otp_expires_at
                    ) VALUES (?, ?, ?, 'recruiter', ?, ?, ?, ?, ?, 'pending', 1, NULL, NULL)
                ");
                $insertStmt->execute([
                    $pending['name'],
                    $pending['email'],
                    $pending['password_hash'],
                    $pending['company_name'],
                    $pending['company_website'],
                    $pending['headline'],
                    $pending['phone'],
                    $pending['location'],
                ]);

                $userId = (int)$this->db->lastInsertId();
                audit_log($this->db, $userId, 'RECRUITER_EMAIL_VERIFIED', 'users', $userId, "Recruiter verified email OTP for {$pending['company_name']}. Pending admin approval.");

                unset($_SESSION['pending_registration']);

                set_flash('success', "Email verified successfully! Your recruiter registration for '{$pending['company_name']}' has been submitted and is awaiting administrator approval.");
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }

        // Case B: Existing unverified account (from login flow)
        $userId = (int)($_SESSION['pending_verification_user_id'] ?? 0);
        if ($userId) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                set_flash('error', 'User account not found.');
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            if (empty($user['otp_code']) || $user['otp_code'] !== $enteredOtp) {
                set_flash('error', 'Invalid verification code. Please check your email and try again.');
                header('Location: ' . BASE_URL . '/verify-otp');
                exit;
            }

            if (!empty($user['otp_expires_at']) && strtotime($user['otp_expires_at']) < time()) {
                set_flash('error', 'The OTP code has expired. Please click "Resend OTP" to generate a fresh code.');
                header('Location: ' . BASE_URL . '/verify-otp');
                exit;
            }

            $this->db->prepare("
                UPDATE users 
                SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL 
                WHERE id = ?
            ")->execute([$userId]);

            audit_log($this->db, $userId, 'USER_EMAIL_OTP_VERIFIED', 'users', $userId, "User successfully verified email via OTP.");

            unset($_SESSION['pending_verification_user_id']);
            unset($_SESSION['pending_verification_email']);
            unset($_SESSION['pending_verification_phone']);
            unset($_SESSION['pending_verification_role']);

            if ($user['role'] === 'candidate') {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $verifiedUser = $stmt->fetch();

                login_user($verifiedUser);
                set_flash('success', "Email verified! Welcome to " . APP_NAME . ", {$verifiedUser['name']}!");
                header('Location: ' . BASE_URL . '/candidate/dashboard');
                exit;
            } else {
                set_flash('success', "Email verified! Your recruiter account for '{$user['company_name']}' is awaiting administrator approval.");
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }

        set_flash('error', 'Verification session expired. Please sign in or register again.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function resendOtp(): void {
        verify_csrf();

        // Case A: Pending registration
        if (isset($_SESSION['pending_registration'])) {
            $newOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $_SESSION['pending_registration']['otp_code'] = $newOtp;
            $_SESSION['pending_registration']['otp_expires_at'] = $expiresAt;

            $email = $_SESSION['pending_registration']['email'] ?? '';
            $name  = $_SESSION['pending_registration']['name'] ?? '';

            if (!empty($email)) {
                send_email_otp($email, $name, $newOtp);
            }

            set_flash('info', "A fresh 6-digit verification code has been dispatched to {$email}. Please check your inbox.");
            header('Location: ' . BASE_URL . '/verify-otp');
            exit;
        }

        // Case B: Existing user unverified
        $userId = (int)($_SESSION['pending_verification_user_id'] ?? 0);
        if ($userId) {
            $newOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->db->prepare("
                UPDATE users 
                SET otp_code = ?, otp_expires_at = ? 
                WHERE id = ?
            ")->execute([$newOtp, $expiresAt, $userId]);

            $stmt = $this->db->prepare("SELECT email, name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();

            $email = $u['email'] ?? ($_SESSION['pending_verification_email'] ?? '');
            $name  = $u['name'] ?? '';

            if (!empty($email)) {
                send_email_otp($email, $name, $newOtp);
            }

            audit_log($this->db, $userId, 'OTP_RESENT_EMAIL', 'users', $userId, "New verification OTP generated and dispatched via email.");

            set_flash('info', "A fresh 6-digit verification code has been sent to {$email}. Please check your inbox.");
            header('Location: ' . BASE_URL . '/verify-otp');
            exit;
        }

        set_flash('error', 'Session expired. Please register or sign in again.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function logout(): void {
        if (is_authenticated()) {
            audit_log($this->db, $_SESSION['user_id'] ?? null, 'USER_LOGOUT', 'users', $_SESSION['user_id'] ?? null, "User logged out.");
        }
        logout_user();
        set_flash('info', 'You have been successfully logged out.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
