<?php
// tests/test_http_modules.php

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/test_cookies.txt';

function http_request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response,
        'url'  => $effectiveUrl,
        'err'  => $error
    ];
}

echo "=== Starting HTTP Integration & Role Access Tests ===\n";

// 1. Candidate Test
if (file_exists($cookieFile)) unlink($cookieFile);
echo "\n[1] Testing Candidate Persona & Isolation...\n";
$login = http_request("{$baseUrl}/login/demo?role=candidate", 'GET', [], $cookieFile);
echo "  Logged in -> Effective URL: {$login['url']} (HTTP {$login['code']})\n";
assert(strpos($login['url'], '/candidate/dashboard') !== false, "Failed redirect to candidate dashboard");

$candUrls = [
    '/candidate/dashboard' => 'Candidate Portal',
    '/candidate/applications' => 'My Job Applications',
    '/candidate/applications/1' => 'Recruitment Pipeline Status',
    '/candidate/interviews' => 'Scheduled Interviews',
    '/candidate/profile' => 'Candidate Profile & Resume'
];

foreach ($candUrls as $path => $expectedString) {
    $res = http_request("{$baseUrl}{$path}", 'GET', [], $cookieFile);
    if ($res['code'] === 200 && strpos($res['body'], $expectedString) !== false) {
        echo "  [PASS] Candidate GET {$path} (HTTP 200, Content verified)\n";
    } else {
        echo "  [FAIL] Candidate GET {$path} (HTTP {$res['code']})\n";
        exit(1);
    }
}

// Check unauthorized candidate access to recruiter & admin
$unauth = http_request("{$baseUrl}/admin/dashboard", 'GET', [], $cookieFile);
echo "  Security Check: Candidate accessing /admin/dashboard redirected to: {$unauth['url']}\n";
assert(strpos($unauth['url'], '/candidate/dashboard') !== false, "Candidate was not blocked from admin dashboard!");
echo "  [PASS] Candidate properly blocked from /admin/dashboard.\n";

// 2. Recruiter Test
if (file_exists($cookieFile)) unlink($cookieFile);
echo "\n[2] Testing Recruiter Persona & Isolation...\n";
$recLogin = http_request("{$baseUrl}/login/demo?role=recruiter", 'GET', [], $cookieFile);
echo "  Logged in -> Effective URL: {$recLogin['url']} (HTTP {$recLogin['code']})\n";
assert(strpos($recLogin['url'], '/recruiter/dashboard') !== false, "Failed redirect to recruiter dashboard");

$recUrls = [
    '/recruiter/dashboard' => 'Recruiter Desk',
    '/recruiter/jobs' => 'My Job Postings',
    '/recruiter/jobs/create' => 'Create New Job Vacancy',
    '/recruiter/pipeline' => 'Recruitment Pipeline Board',
    '/recruiter/applicants/1' => 'Candidate Review'
];

foreach ($recUrls as $path => $expectedString) {
    $res = http_request("{$baseUrl}{$path}", 'GET', [], $cookieFile);
    if ($res['code'] === 200 && strpos($res['body'], $expectedString) !== false) {
        echo "  [PASS] Recruiter GET {$path} (HTTP 200, Content verified)\n";
    } else {
        echo "  [FAIL] Recruiter GET {$path} (HTTP {$res['code']})\n";
        exit(1);
    }
}

// Check unauthorized recruiter access to admin
$unauthRec = http_request("{$baseUrl}/admin/users", 'GET', [], $cookieFile);
echo "  Security Check: Recruiter accessing /admin/users redirected to: {$unauthRec['url']}\n";
assert(strpos($unauthRec['url'], '/recruiter/dashboard') !== false, "Recruiter was not blocked from admin area!");
echo "  [PASS] Recruiter properly blocked from /admin/users.\n";

// 3. Admin Test
if (file_exists($cookieFile)) unlink($cookieFile);
echo "\n[3] Testing Admin Persona & Governance...\n";
$admLogin = http_request("{$baseUrl}/login/demo?role=admin", 'GET', [], $cookieFile);
echo "  Logged in -> Effective URL: {$admLogin['url']} (HTTP {$admLogin['code']})\n";
assert(strpos($admLogin['url'], '/admin/dashboard') !== false, "Failed redirect to admin dashboard");

$admUrls = [
    '/admin/dashboard' => 'Administrator Command Center',
    '/admin/users' => 'User & Access Governance',
    '/admin/jobs' => 'Job Posting Moderation',
    '/admin/categories' => 'Job Departments & Categories',
    '/admin/audit-logs' => 'Recruitment Audit Trail & Logs'
];

foreach ($admUrls as $path => $expectedString) {
    $res = http_request("{$baseUrl}{$path}", 'GET', [], $cookieFile);
    if ($res['code'] === 200 && strpos($res['body'], $expectedString) !== false) {
        echo "  [PASS] Admin GET {$path} (HTTP 200, Content verified)\n";
    } else {
        echo "  [FAIL] Admin GET {$path} (HTTP {$res['code']})\n";
        exit(1);
    }
}

if (file_exists($cookieFile)) unlink($cookieFile);
echo "\n🎉 ALL HTTP ENDPOINTS & SECURITY ISOLATION CHECKS PASSED PERFECTLY!\n\n";
