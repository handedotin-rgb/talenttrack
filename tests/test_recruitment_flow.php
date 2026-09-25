<?php
// tests/test_recruitment_flow.php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/src/Helpers/auth.php';

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

echo "\n=== Running TalentTrack Platform Automated Tests ===\n";

$db = get_db();

it("Database connection is alive and SQLite tables exist", function() use ($db) {
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['users', 'categories', 'jobs', 'applications', 'application_history', 'interviews', 'audit_logs'];
    foreach ($required as $req) {
        if (!in_array($req, $tables, true)) {
            throw new Exception("Missing table: {$req}");
        }
    }
});

it("Pre-seeded users exist for all 3 roles (admin, recruiter, candidate)", function() use ($db) {
    $roles = ['admin', 'recruiter', 'candidate'];
    foreach ($roles as $role) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = ? AND status = 'active'");
        $stmt->execute([$role]);
        $count = (int)$stmt->fetchColumn();
        if ($count < 1) {
            throw new Exception("No active user found with role: {$role}");
        }
    }
});

it("Password hashing and verification works correctly", function() use ($db) {
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE email = 'admin@talenttrack.com'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    if (!password_verify('Admin@123', $hash)) {
        throw new Exception("Admin password verification failed!");
    }
});

it("Candidate application tracking stages and transitions work", function() use ($db) {
    // Check existing application
    $app = $db->query("SELECT id, current_stage FROM applications LIMIT 1")->fetch();
    if (!$app) {
        throw new Exception("No applications found to test!");
    }

    // Check history records exist
    $histCount = (int)$db->query("SELECT COUNT(*) FROM application_history WHERE application_id = {$app['id']}")->fetchColumn();
    if ($histCount < 1) {
        throw new Exception("Application #{$app['id']} has no tracking history records!");
    }
});

it("Interview scheduling records are present with valid relations", function() use ($db) {
    $interviews = $db->query("
        SELECT i.*, a.job_id, u.name as candidate_name 
        FROM interviews i
        JOIN applications a ON i.application_id = a.id
        JOIN users u ON i.candidate_id = u.id
    ")->fetchAll();

    if (empty($interviews)) {
        throw new Exception("No interviews found in database!");
    }
});

it("Admin KPI analytics queries execute without errors", function() use ($db) {
    $totalCandidates = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'")->fetchColumn();
    $totalRecruiters = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn();
    $totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $totalApps = (int)$db->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    if ($totalCandidates < 1 || $totalRecruiters < 1 || $totalJobs < 1 || $totalApps < 1) {
        throw new Exception("KPIs returned insufficient counts");
    }
});

echo "\n--- Summary ---\n";
echo "Total Passed: {$passes}\n";
echo "Total Failed: " . count($errors) . "\n";

if (!empty($errors)) {
    exit(1);
}
echo "ALL TESTS PASSED SUCCESSFULLY!\n\n";
