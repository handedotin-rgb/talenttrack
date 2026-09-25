<?php
// src/Controllers/AdminController.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/Helpers/auth.php';
require_once dirname(__DIR__) . '/Helpers/csrf.php';
require_once dirname(__DIR__) . '/Helpers/flash.php';
require_once dirname(__DIR__) . '/Helpers/view.php';

class AdminController {
    private PDO $db;
    private int $userId;

    public function __construct() {
        require_role('admin');
        $this->db = get_db();
        $this->userId = (int)$_SESSION['user_id'];
    }

    public function dashboard(): void {
        // Platform Analytics
        $totalCandidates = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'candidate'")->fetchColumn();
        $totalRecruiters = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter'")->fetchColumn();
        $totalJobs = (int)$this->db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
        $activeJobs = (int)$this->db->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
        $totalApps = (int)$this->db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
        $totalHires = (int)$this->db->query("SELECT COUNT(*) FROM applications WHERE current_stage = 'hired' OR status = 'hired'")->fetchColumn();

        // Stage Distribution Funnel
        $stageCounts = [];
        foreach (array_keys(RECRUITMENT_STAGES) as $stageKey) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE current_stage = ?");
            $stmt->execute([$stageKey]);
            $stageCounts[$stageKey] = (int)$stmt->fetchColumn();
        }

        // Recent Platform Audit Logs
        $logStmt = $this->db->query("
            SELECT l.*, u.name as user_name, u.role as user_role
            FROM audit_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT 10
        ");
        $recentLogs = $logStmt->fetchAll();

        // Recent Registrations
        $userStmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
        $recentUsers = $userStmt->fetchAll();

        // Pending Recruiter Count
        $pendingRecruiterCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'recruiter' AND status = 'pending'")->fetchColumn();

        render_view('admin/dashboard', [
            'pageTitle'              => 'Admin Command Center - ' . APP_NAME,
            'kpis'                   => [
                'total_candidates'  => $totalCandidates,
                'total_recruiters'  => $totalRecruiters,
                'total_jobs'        => $totalJobs,
                'active_jobs'       => $activeJobs,
                'total_apps'        => $totalApps,
                'total_hires'       => $totalHires,
                'pending_recruiters'=> $pendingRecruiterCount
            ],
            'pendingRecruiterCount'  => $pendingRecruiterCount,
            'stageCounts'            => $stageCounts,
            'recentLogs'             => $recentLogs,
            'recentUsers'            => $recentUsers
        ]);
    }

    public function users(): void {
        $search = trim($_GET['search'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        $sql = "
            SELECT u.*,
                   (SELECT COUNT(*) FROM jobs WHERE recruiter_id = u.id) as jobs_count,
                   (SELECT COUNT(*) FROM applications WHERE candidate_id = u.id) as applications_count
            FROM users u
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.company_name LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        if (!empty($roleFilter)) {
            $sql .= " AND u.role = ?";
            $params[] = $roleFilter;
        }

        if (!empty($statusFilter)) {
            $sql .= " AND u.status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        // Fetch pending recruiters awaiting admin verification
        $pendingRecruiters = $this->db->query("
            SELECT * FROM users 
            WHERE role = 'recruiter' AND status = 'pending' 
            ORDER BY created_at DESC
        ")->fetchAll();

        render_view('admin/users', [
            'pageTitle'         => 'User & Access Management - ' . APP_NAME,
            'users'             => $users,
            'pendingRecruiters' => $pendingRecruiters,
            'search'            => $search,
            'roleFilter'        => $roleFilter,
            'statusFilter'      => $statusFilter,
            'totalUsers'        => count($users)
        ]);
    }

    public function toggleUserStatus(int $id): void {
        verify_csrf();

        // Prevent self-deactivation
        if ($id === $this->userId) {
            set_flash('error', 'You cannot deactivate your own administrator account.');
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $stmt = $this->db->prepare("SELECT id, name, status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
            $this->db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

            audit_log($this->db, $this->userId, 'TOGGLE_USER_STATUS', 'users', $id, "Admin changed user {$user['name']} status to {$newStatus}.");
            set_flash('success', "User '{$user['name']}' status changed to " . ucfirst($newStatus) . ".");
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    public function resetPassword(int $id): void {
        verify_csrf();

        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $tempPassword = 'Reset@' . rand(1000, 9999);
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);

            audit_log($this->db, $this->userId, 'ADMIN_RESET_PASSWORD', 'users', $id, "Admin reset password for {$user['name']}.");
            set_flash('success', "Password for {$user['name']} has been reset to: {$tempPassword}");
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    public function approveRecruiter(int $id): void {
        verify_csrf();

        $stmt = $this->db->prepare("SELECT id, name, company_name, email FROM users WHERE id = ? AND role = 'recruiter'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
            audit_log($this->db, $this->userId, 'APPROVE_RECRUITER', 'users', $id, "Admin approved recruiter {$user['name']} for {$user['company_name']}.");
            set_flash('success', "Recruiter '{$user['name']}' from '{$user['company_name']}' has been approved and activated!");
        } else {
            set_flash('error', 'Recruiter account not found.');
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    public function rejectRecruiter(int $id): void {
        verify_csrf();

        $stmt = $this->db->prepare("SELECT id, name, company_name FROM users WHERE id = ? AND role = 'recruiter'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $this->db->prepare("UPDATE users SET status = 'rejected' WHERE id = ?")->execute([$id]);
            audit_log($this->db, $this->userId, 'REJECT_RECRUITER', 'users', $id, "Admin declined recruiter registration for {$user['name']}.");
            set_flash('warning', "Recruiter registration for '{$user['name']}' has been declined.");
        } else {
            set_flash('error', 'Recruiter account not found.');
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }

    public function jobs(): void {
        $search = trim($_GET['search'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        $sql = "
            SELECT j.*, c.name as category_name, u.name as recruiter_name, u.company_name,
                   (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicants_count
            FROM jobs j
            JOIN categories c ON j.category_id = c.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (j.title LIKE ? OR u.company_name LIKE ? OR j.location LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($statusFilter)) {
            $sql .= " AND j.status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY j.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll();

        render_view('admin/jobs', [
            'pageTitle'    => 'Job Moderation & Postings - ' . APP_NAME,
            'jobs'         => $jobs,
            'search'       => $search,
            'statusFilter' => $statusFilter
        ]);
    }

    public function toggleFeaturedJob(int $id): void {
        verify_csrf();

        $stmt = $this->db->prepare("SELECT id, title, is_featured FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
        $job = $stmt->fetch();

        if ($job) {
            $newFeatured = $job['is_featured'] ? 0 : 1;
            $this->db->prepare("UPDATE jobs SET is_featured = ? WHERE id = ?")->execute([$newFeatured, $id]);
            audit_log($this->db, $this->userId, 'TOGGLE_FEATURED_JOB', 'jobs', $id, "Toggled featured to {$newFeatured} for '{$job['title']}'.");
            set_flash('success', "Job '" . $job['title'] . "' featured status updated.");
        }

        header('Location: ' . BASE_URL . '/admin/jobs');
        exit;
    }

    public function categories(): void {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(j.id) as jobs_count
            FROM categories c
            LEFT JOIN jobs j ON c.id = j.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll();

        render_view('admin/categories', [
            'pageTitle'  => 'Job Categories & Departments - ' . APP_NAME,
            'categories' => $categories
        ]);
    }

    public function storeCategory(): void {
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'briefcase');

        if (empty($name)) {
            set_flash('error', 'Category name is required.');
            header('Location: ' . BASE_URL . '/admin/categories');
            exit;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        try {
            $stmt = $this->db->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $icon, $description]);

            audit_log($this->db, $this->userId, 'CREATE_CATEGORY', 'categories', (int)$this->db->lastInsertId(), "Created category '{$name}'.");
            set_flash('success', "Category '{$name}' created successfully.");
        } catch (PDOException $e) {
            set_flash('error', 'A category with this name or slug already exists.');
        }

        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    public function deleteCategory(int $id): void {
        verify_csrf();

        $jobCount = (int)$this->db->prepare("SELECT COUNT(*) FROM jobs WHERE category_id = ?")->execute([$id]) ? $this->db->query("SELECT COUNT(*) FROM jobs WHERE category_id = {$id}")->fetchColumn() : 0;

        if ($jobCount > 0) {
            set_flash('error', "Cannot delete category: {$jobCount} job posting(s) are currently assigned to it.");
            header('Location: ' . BASE_URL . '/admin/categories');
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);

        audit_log($this->db, $this->userId, 'DELETE_CATEGORY', 'categories', $id, "Deleted category #{$id}.");
        set_flash('success', 'Category deleted successfully.');
        header('Location: ' . BASE_URL . '/admin/categories');
        exit;
    }

    public function auditLogs(): void {
        $search = trim($_GET['search'] ?? '');
        $actionFilter = trim($_GET['action'] ?? '');

        $sql = "
            SELECT l.*, u.name as user_name, u.email as user_email, u.role as user_role
            FROM audit_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR l.action LIKE ? OR l.details LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($actionFilter)) {
            $sql .= " AND l.action = ?";
            $params[] = $actionFilter;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Get distinct action types for filter
        $actions = $this->db->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

        render_view('admin/audit_logs', [
            'pageTitle'    => 'System Audit & Activity Logs - ' . APP_NAME,
            'logs'         => $logs,
            'actions'      => $actions,
            'search'       => $search,
            'actionFilter' => $actionFilter
        ]);
    }
}
