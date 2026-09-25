<?php
// src/Controllers/JobController.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/Helpers/auth.php';
require_once dirname(__DIR__) . '/Helpers/csrf.php';
require_once dirname(__DIR__) . '/Helpers/flash.php';
require_once dirname(__DIR__) . '/Helpers/view.php';

class JobController {
    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    public function home(): void {
        // Featured Jobs
        $stmt = $this->db->query("
            SELECT j.*, c.name as category_name, u.company_name, u.name as recruiter_name
            FROM jobs j
            JOIN categories c ON j.category_id = c.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE j.status = 'active'
            ORDER BY j.is_featured DESC, j.created_at DESC
            LIMIT 6
        ");
        $featuredJobs = $stmt->fetchAll();

        // Categories with job count
        $catStmt = $this->db->query("
            SELECT c.*, COUNT(j.id) as job_count
            FROM categories c
            LEFT JOIN jobs j ON c.id = j.category_id AND j.status = 'active'
            GROUP BY c.id
            ORDER BY job_count DESC
            LIMIT 6
        ");
        $categories = $catStmt->fetchAll();

        // Quick Stats
        $stats = [
            'total_jobs'        => (int)$this->db->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn(),
            'total_companies'   => (int)$this->db->query("SELECT COUNT(DISTINCT company_name) FROM users WHERE role = 'recruiter'")->fetchColumn(),
            'total_candidates'  => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'")->fetchColumn(),
            'total_hires'       => (int)$this->db->query("SELECT COUNT(*) FROM applications WHERE current_stage = 'hired' OR status = 'hired'")->fetchColumn()
        ];

        render_view('public/home', [
            'pageTitle'    => APP_NAME . ' - Next-Gen Recruitment & Candidate Tracking',
            'featuredJobs' => $featuredJobs,
            'categories'   => $categories,
            'stats'        => $stats
        ]);
    }

    public function index(): void {
        $query = trim($_GET['q'] ?? '');
        $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        $jobType = trim($_GET['type'] ?? '');
        $experience = trim($_GET['exp'] ?? '');
        $location = trim($_GET['location'] ?? '');

        $sql = "
            SELECT j.*, c.name as category_name, u.company_name, u.name as recruiter_name
            FROM jobs j
            JOIN categories c ON j.category_id = c.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE j.status = 'active'
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR u.company_name LIKE ?)";
            $wildcard = "%{$query}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($categoryId)) {
            $sql .= " AND j.category_id = ?";
            $params[] = $categoryId;
        }

        if (!empty($jobType)) {
            $sql .= " AND j.job_type = ?";
            $params[] = $jobType;
        }

        if (!empty($experience)) {
            $sql .= " AND j.experience_level = ?";
            $params[] = $experience;
        }

        if (!empty($location)) {
            $sql .= " AND j.location LIKE ?";
            $params[] = "%{$location}%";
        }

        $sql .= " ORDER BY j.is_featured DESC, j.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll();

        // All categories for filter dropdown
        $allCategories = $this->db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

        render_view('public/jobs', [
            'pageTitle'     => 'Browse Open Job Vacancies - ' . APP_NAME,
            'jobs'          => $jobs,
            'categories'    => $allCategories,
            'currentQuery'  => $query,
            'currentCat'    => $categoryId,
            'currentType'   => $jobType,
            'currentExp'    => $experience,
            'currentLoc'    => $location,
            'totalFound'    => count($jobs)
        ]);
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("
            SELECT j.*, c.name as category_name, u.company_name, u.name as recruiter_name, u.bio as recruiter_bio, u.email as recruiter_email
            FROM jobs j
            JOIN categories c ON j.category_id = c.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE j.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $job = $stmt->fetch();

        if (!$job) {
            set_flash('error', 'Job posting not found or has expired.');
            header('Location: ' . BASE_URL . '/jobs');
            exit;
        }

        // Increment view count
        $this->db->prepare("UPDATE jobs SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

        // Check if current user has already applied
        $hasApplied = false;
        $existingApp = null;
        $candidateProfile = null;

        if (is_authenticated() && has_role('candidate')) {
            $userId = $_SESSION['user_id'];
            $appCheck = $this->db->prepare("SELECT id, current_stage, applied_at FROM applications WHERE job_id = ? AND candidate_id = ? LIMIT 1");
            $appCheck->execute([$id, $userId]);
            $existingApp = $appCheck->fetch();
            $hasApplied = !empty($existingApp);

            $candStmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $candStmt->execute([$userId]);
            $candidateProfile = $candStmt->fetch();
        }

        render_view('public/job_detail', [
            'pageTitle'        => $job['title'] . ' at ' . ($job['company_name'] ?: 'TechCorp') . ' - ' . APP_NAME,
            'job'              => $job,
            'hasApplied'       => $hasApplied,
            'existingApp'      => $existingApp,
            'candidateProfile' => $candidateProfile
        ]);
    }
}
