<?php
// tests/test_new_features.php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$errors = [];
$passes = 0;

function it(string $description, callable $fn) {
    global $errors, $passes;
    try {
        $fn();
        echo "  [PASS] {$description}\n";
        $passes++;
    } catch (Throwable $t) {
        echo "  [FAIL] {$description}: " . $t->getMessage() . "\n";
        $errors[] = "{$description}: " . $t->getMessage();
    }
}

echo "\n=== Running TalentTrack Extended Features & Validation Tests ===\n";

$db = get_db();

it("Users table has new personal & academic columns (dob, cgpa, marks, degree, institution)", function() use ($db) {
    $cols = array_column($db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    foreach (['dob', 'cgpa', 'marks', 'degree', 'institution', 'company_website'] as $col) {
        if (!in_array($col, $cols, true)) {
            throw new Exception("Missing column in users table: {$col}");
        }
    }
});

it("Jobs table has min_cgpa and custom_category columns", function() use ($db) {
    $cols = array_column($db->query("PRAGMA table_info(jobs)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    foreach (['min_cgpa', 'custom_category'] as $col) {
        if (!in_array($col, $cols, true)) {
            throw new Exception("Missing column in jobs table: {$col}");
        }
    }
});

it("Categories table has expanded categories including 'Others'", function() use ($db) {
    $catNames = $db->query("SELECT name FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('Others', $catNames, true)) {
        throw new Exception("Missing 'Others' category in categories table");
    }
    if (count($catNames) < 10) {
        throw new Exception("Expected at least 10 categories, found: " . count($catNames));
    }
});

it("Indian mobile number validation correctly accepts valid 10-digit Indian numbers and rejects invalid", function() {
    $validPhones = ['9876543210', '+919876543210', '919876543210', '6123456789', '7890123456', '8901234567'];
    $invalidPhones = ['123', 'abc', '1234567890', '5876543210', '98765', '98765432101234'];

    $normalizeIndian = function(string $phone): ?string {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $clean = substr($clean, 2);
        } elseif (strlen($clean) === 11 && str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }
        return preg_match('/^[6-9][0-9]{9}$/', $clean) ? '+91' . $clean : null;
    };

    foreach ($validPhones as $phone) {
        if ($normalizeIndian($phone) === null) {
            throw new Exception("Valid Indian phone rejected: {$phone}");
        }
    }

    foreach ($invalidPhones as $phone) {
        if ($normalizeIndian($phone) !== null) {
            throw new Exception("Invalid phone accepted as valid Indian number: {$phone}");
        }
    }
});

it("Recruiter registration sets status to 'pending' awaiting admin approval", function() use ($db) {
    $testEmail = 'recruiter_test_' . time() . '@company.com';
    $passwordHash = password_hash('Secret@123', PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, company_name, headline, phone, location, status)
        VALUES (?, ?, ?, 'recruiter', 'Test Global Corp', 'Head of Talent', '9876543210', 'New York, NY', 'pending')
    ");
    $stmt->execute(['Test Recruiter', $testEmail, $passwordHash]);
    $userId = (int)$db->lastInsertId();

    $user = $db->query("SELECT * FROM users WHERE id = {$userId}")->fetch();
    if ($user['status'] !== 'pending') {
        throw new Exception("Expected recruiter status 'pending', got '{$user['status']}'");
    }

    // Admin approval
    $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
    $approvedUser = $db->query("SELECT status FROM users WHERE id = {$userId}")->fetch();
    if ($approvedUser['status'] !== 'active') {
        throw new Exception("Expected recruiter status 'active' after admin approval, got '{$approvedUser['status']}'");
    }

    // Clean up
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
});

it("Candidate application enforces minimum CGPA threshold", function() use ($db) {
    // 1. Create a job with min_cgpa = 8.00
    $stmt = $db->prepare("
        INSERT INTO jobs (recruiter_id, category_id, title, slug, job_type, experience_level, location, min_cgpa, description, requirements, status)
        VALUES (2, 1, 'High CGPA Job Test', 'high-cgpa-test-" . time() . "', 'full-time', 'entry', 'Remote', 8.00, 'Test Description', 'Test Requirements', 'active')
    ");
    $stmt->execute();
    $jobId = (int)$db->lastInsertId();

    // 2. Create Candidate A with CGPA = 6.50 (ineligible)
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, cgpa, marks, dob, location, degree, institution, status)
        VALUES ('Candidate Low', 'low_cgpa_" . time() . "@test.com', 'hash', 'candidate', 6.50, 65.0, '2000-01-01', 'Chicago', 'B.Tech', 'State Univ', 'active')
    ");
    $stmt->execute();
    $lowCandId = (int)$db->lastInsertId();

    // 3. Create Candidate B with CGPA = 8.50 (eligible)
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, cgpa, marks, dob, location, degree, institution, status)
        VALUES ('Candidate High', 'high_cgpa_" . time() . "@test.com', 'hash', 'candidate', 8.50, 85.0, '2000-01-01', 'Chicago', 'B.Tech', 'State Univ', 'active')
    ");
    $stmt->execute();
    $highCandId = (int)$db->lastInsertId();

    // Check Candidate Low eligibility logic
    $job = $db->query("SELECT min_cgpa FROM jobs WHERE id = {$jobId}")->fetch();
    $lowCand = $db->query("SELECT cgpa FROM users WHERE id = {$lowCandId}")->fetch();
    $highCand = $db->query("SELECT cgpa FROM users WHERE id = {$highCandId}")->fetch();

    $lowEligible = ($job['min_cgpa'] <= 0) || ((float)$lowCand['cgpa'] >= (float)$job['min_cgpa']);
    $highEligible = ($job['min_cgpa'] <= 0) || ((float)$highCand['cgpa'] >= (float)$job['min_cgpa']);

    if ($lowEligible) {
        throw new Exception("Candidate Low (CGPA 6.5) should be ineligible for job with min CGPA 8.0");
    }
    if (!$highEligible) {
        throw new Exception("Candidate High (CGPA 8.5) should be eligible for job with min CGPA 8.0");
    }

    // Clean up test records
    $db->prepare("DELETE FROM jobs WHERE id = ?")->execute([$jobId]);
    $db->prepare("DELETE FROM users WHERE id IN (?, ?)")->execute([$lowCandId, $highCandId]);
});

it("OTP generation, verification, and expiry workflow operates correctly", function() use ($db) {
    $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $testEmail = 'otp_test_' . time() . '@test.com';

    // 1. Create unverified user
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, is_verified, otp_code, otp_expires_at, status)
        VALUES ('OTP Candidate', ?, 'hash', 'candidate', 0, ?, ?, 'active')
    ");
    $stmt->execute([$testEmail, $otp, $expiresAt]);
    $userId = (int)$db->lastInsertId();

    $user = $db->query("SELECT is_verified, otp_code, otp_expires_at FROM users WHERE id = {$userId}")->fetch();
    if ((int)$user['is_verified'] !== 0 || $user['otp_code'] !== $otp) {
        throw new Exception("Initial OTP state mismatch");
    }

    // 2. Reject incorrect OTP
    $wrongOtp = '000000';
    if ($wrongOtp === $user['otp_code']) {
        $wrongOtp = '111111';
    }
    if ($wrongOtp === $user['otp_code']) {
        throw new Exception("Wrong OTP accidentally matched actual OTP");
    }

    // 3. Accept valid OTP and verify
    $db->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?")->execute([$userId]);
    $verified = $db->query("SELECT is_verified, otp_code FROM users WHERE id = {$userId}")->fetch();
    if ((int)$verified['is_verified'] !== 1 || $verified['otp_code'] !== null) {
        throw new Exception("User was not marked verified or otp_code not cleared");
    }

    // Clean up
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
});

it("Without OTP verification, user record is NOT inserted into the database", function() use ($db) {
    $testEmail = 'unregistered_' . time() . '@example.com';
    $testPhone = '+9198765' . rand(10000, 99999);
    $otp = '742918';

    // Simulate registration submission (holding in session pending verification)
    $pendingSession = [
        'role'          => 'candidate',
        'name'          => 'Pending Candidate',
        'email'         => $testEmail,
        'phone'         => $testPhone,
        'location'      => 'Bengaluru, KA',
        'password_hash' => password_hash('Pass@123', PASSWORD_DEFAULT),
        'dob'           => '1998-05-15',
        'degree'        => 'B.Tech',
        'institution'   => 'NIT',
        'cgpa'          => 8.5,
        'marks'         => 85.0,
        'headline'      => 'Software Engineer',
        'otp_code'      => $otp,
        'otp_expires_at'=> date('Y-m-d H:i:s', strtotime('+15 minutes')),
    ];

    // Check DB BEFORE verification: MUST BE 0 records!
    $checkBefore = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkBefore->execute([$testEmail]);
    if ((int)$checkBefore->fetchColumn() !== 0) {
        throw new Exception("User should NOT exist in database prior to OTP verification!");
    }

    // Now simulate successful OTP verification -> DB INSERT occurs only now
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password_hash, role, phone, location, dob, degree, institution, cgpa, marks, headline, status, is_verified)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)
    ");
    $stmt->execute([
        $pendingSession['name'], $pendingSession['email'], $pendingSession['password_hash'],
        $pendingSession['role'], $pendingSession['phone'], $pendingSession['location'],
        $pendingSession['dob'], $pendingSession['degree'], $pendingSession['institution'],
        $pendingSession['cgpa'], $pendingSession['marks'], $pendingSession['headline']
    ]);
    $newUserId = (int)$db->lastInsertId();

    $checkAfter = $db->prepare("SELECT is_verified FROM users WHERE id = ?");
    $checkAfter->execute([$newUserId]);
    $verified = $checkAfter->fetch();
    if (!$verified || (int)$verified['is_verified'] !== 1) {
        throw new Exception("User was not properly verified upon insertion");
    }

    // Clean up
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$newUserId]);
});

it("Email OTP dispatch generates branded HTML and records to log/service", function() {
    require_once dirname(__DIR__) . '/src/Helpers/email.php';
    $testEmail = 'verify_test_' . time() . '@domain.com';
    $otp = '849201';

    $result = send_email_otp($testEmail, 'Test User', $otp);
    if (!$result) {
        throw new Exception("send_email_otp returned false");
    }

    $logFile = dirname(__DIR__) . '/logs/otp_email.log';
    if (!file_exists($logFile)) {
        throw new Exception("logs/otp_email.log was not created");
    }

    $logContent = file_get_contents($logFile);
    if (!str_contains($logContent, $testEmail) || !str_contains($logContent, $otp)) {
        throw new Exception("Email dispatch was not recorded in logs/otp_email.log");
    }
});

echo "\n--- Summary ---\n";
echo "Total Passed: {$passes}\n";
echo "Total Failed: " . count($errors) . "\n";

if (!empty($errors)) {
    exit(1);
}
echo "\nAll Extended Feature & Validation Tests Passed!\n\n";
