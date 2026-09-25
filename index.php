<?php
// index.php - Front Controller & Application Router

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Helpers/auth.php';
require_once __DIR__ . '/src/Helpers/csrf.php';
require_once __DIR__ . '/src/Helpers/flash.php';
require_once __DIR__ . '/src/Helpers/view.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/JobController.php';
require_once __DIR__ . '/src/Controllers/CandidateController.php';
require_once __DIR__ . '/src/Controllers/RecruiterController.php';
require_once __DIR__ . '/src/Controllers/AdminController.php';

// Auto-initialize SQLite DB if file does not exist
if (DB_DRIVER === 'sqlite' && !file_exists(SQLITE_FILE)) {
    require_once __DIR__ . '/database/init_db.php';
    initialize_database();
}

// Parse request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Remove base directory path from URL if running in a subdirectory
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}
$path = '/' . trim($path, '/');
if ($path === '//') {
    $path = '/';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// -------------------------------------------------------------
// Route Matching
// -------------------------------------------------------------

// Static Assets fallback (when using php built-in server)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|pdf|docx?|ico)$/', $path)) {
    return false;
}

// 1. Public & Auth Routes
if ($path === '/' && $method === 'GET') {
    (new JobController())->home();
    exit;
}

if ($path === '/jobs' && $method === 'GET') {
    (new JobController())->index();
    exit;
}

if (preg_match('#^/jobs/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    (new JobController())->show((int)$matches[1]);
    exit;
}

if ($path === '/login') {
    $auth = new AuthController();
    if ($method === 'GET') {
        $auth->showLogin();
    } else {
        $auth->login();
    }
    exit;
}

if ($path === '/login/demo' && $method === 'GET') {
    (new AuthController())->demoLogin();
    exit;
}

if ($path === '/register') {
    $auth = new AuthController();
    if ($method === 'GET') {
        $auth->showRegister();
    } else {
        $auth->register();
    }
    exit;
}

if ($path === '/verify-otp') {
    $auth = new AuthController();
    if ($method === 'GET') {
        $auth->showVerifyOtp();
    } else {
        $auth->verifyOtp();
    }
    exit;
}

if ($path === '/resend-otp' && $method === 'POST') {
    (new AuthController())->resendOtp();
    exit;
}

if ($path === '/logout') {
    (new AuthController())->logout();
    exit;
}

// 2. Candidate Routes
if ($path === '/candidate/dashboard' && $method === 'GET') {
    (new CandidateController())->dashboard();
    exit;
}

if ($path === '/candidate/applications' && $method === 'GET') {
    (new CandidateController())->applications();
    exit;
}

if (preg_match('#^/candidate/applications/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    (new CandidateController())->applicationDetail((int)$matches[1]);
    exit;
}

if (preg_match('#^/candidate/applications/([0-9]+)/withdraw$#', $path, $matches) && $method === 'POST') {
    (new CandidateController())->withdraw((int)$matches[1]);
    exit;
}

if ($path === '/candidate/interviews' && $method === 'GET') {
    (new CandidateController())->interviews();
    exit;
}

if ($path === '/candidate/profile') {
    $cand = new CandidateController();
    if ($method === 'GET') {
        $cand->profile();
    } else {
        $cand->updateProfile();
    }
    exit;
}

if ($path === '/candidate/apply' && $method === 'POST') {
    (new CandidateController())->apply();
    exit;
}

// 3. Recruiter Routes
if ($path === '/recruiter/dashboard' && $method === 'GET') {
    (new RecruiterController())->dashboard();
    exit;
}

if ($path === '/recruiter/jobs' && $method === 'GET') {
    (new RecruiterController())->jobs();
    exit;
}

if ($path === '/recruiter/jobs/create') {
    $rec = new RecruiterController();
    if ($method === 'GET') {
        $rec->createJob();
    } else {
        $rec->storeJob();
    }
    exit;
}

if (preg_match('#^/recruiter/jobs/([0-9]+)/edit$#', $path, $matches)) {
    $rec = new RecruiterController();
    if ($method === 'GET') {
        $rec->editJob((int)$matches[1]);
    } else {
        $rec->updateJob((int)$matches[1]);
    }
    exit;
}

if (preg_match('#^/recruiter/jobs/([0-9]+)/toggle$#', $path, $matches) && $method === 'POST') {
    (new RecruiterController())->toggleJobStatus((int)$matches[1]);
    exit;
}

if ($path === '/recruiter/pipeline' && $method === 'GET') {
    (new RecruiterController())->pipeline();
    exit;
}

if (preg_match('#^/recruiter/applicants/([0-9]+)$#', $path, $matches) && $method === 'GET') {
    (new RecruiterController())->reviewApplicant((int)$matches[1]);
    exit;
}

if (preg_match('#^/recruiter/applicants/([0-9]+)/stage$#', $path, $matches) && $method === 'POST') {
    (new RecruiterController())->updateStage((int)$matches[1]);
    exit;
}

if ($path === '/recruiter/interviews/schedule' && $method === 'POST') {
    (new RecruiterController())->scheduleInterview();
    exit;
}

if (preg_match('#^/recruiter/interviews/([0-9]+)/status$#', $path, $matches) && $method === 'POST') {
    (new RecruiterController())->updateInterviewStatus((int)$matches[1]);
    exit;
}

if (preg_match('#^/recruiter/applicants/([0-9]+)/resume$#', $path, $matches) && $method === 'GET') {
    (new RecruiterController())->downloadResume((int)$matches[1]);
    exit;
}

// 4. Admin Routes
if ($path === '/admin/dashboard' && $method === 'GET') {
    (new AdminController())->dashboard();
    exit;
}

if ($path === '/admin/users' && $method === 'GET') {
    (new AdminController())->users();
    exit;
}

if (preg_match('#^/admin/users/([0-9]+)/toggle$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->toggleUserStatus((int)$matches[1]);
    exit;
}

if (preg_match('#^/admin/users/([0-9]+)/approve$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->approveRecruiter((int)$matches[1]);
    exit;
}

if (preg_match('#^/admin/users/([0-9]+)/reject$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->rejectRecruiter((int)$matches[1]);
    exit;
}

if (preg_match('#^/admin/users/([0-9]+)/reset-password$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->resetPassword((int)$matches[1]);
    exit;
}

if ($path === '/admin/jobs' && $method === 'GET') {
    (new AdminController())->jobs();
    exit;
}

if (preg_match('#^/admin/jobs/([0-9]+)/feature$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->toggleFeaturedJob((int)$matches[1]);
    exit;
}

if ($path === '/admin/categories') {
    $adm = new AdminController();
    if ($method === 'GET') {
        $adm->categories();
    } else {
        $adm->storeCategory();
    }
    exit;
}

if (preg_match('#^/admin/categories/([0-9]+)/delete$#', $path, $matches) && $method === 'POST') {
    (new AdminController())->deleteCategory((int)$matches[1]);
    exit;
}

if ($path === '/admin/audit-logs' && $method === 'GET') {
    (new AdminController())->auditLogs();
    exit;
}

// 404 Fallback
http_response_code(404);
render_view('public/404', ['pageTitle' => 'Page Not Found - ' . APP_NAME]);
