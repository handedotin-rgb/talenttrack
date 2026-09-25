<?php
// src/Controllers/CandidateController.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/Helpers/auth.php';
require_once dirname(__DIR__) . '/Helpers/csrf.php';
require_once dirname(__DIR__) . '/Helpers/flash.php';
require_once dirname(__DIR__) . '/Helpers/upload.php';
require_once dirname(__DIR__) . '/Helpers/view.php';

class CandidateController {
    private PDO $db;
    private int $userId;

    public function __construct() {
        require_role('candidate');
        $this->db = get_db();
        $this->userId = (int)$_SESSION['user_id'];
    }

    public function dashboard(): void {
        // Candidate metrics
        $totalApps = (int)$this->db->prepare("SELECT COUNT(*) FROM applications WHERE candidate_id = ?")->execute([$this->userId]) ? $this->db->query("SELECT COUNT(*) FROM applications WHERE candidate_id = {$this->userId}")->fetchColumn() : 0;
        
        $inReview = (int)$this->db->query("
            SELECT COUNT(*) FROM applications 
            WHERE candidate_id = {$this->userId} AND current_stage IN ('screening', 'assessment')
        ")->fetchColumn();

        $interviewsCount = (int)$this->db->query("
            SELECT COUNT(*) FROM interviews 
            WHERE candidate_id = {$this->userId} AND status = 'scheduled'
        ")->fetchColumn();

        $offersCount = (int)$this->db->query("
            SELECT COUNT(*) FROM applications 
            WHERE candidate_id = {$this->userId} AND current_stage IN ('offer', 'hired')
        ")->fetchColumn();

        // Recent applications
        $stmt = $this->db->prepare("
            SELECT a.*, j.title as job_title, j.job_type, j.location as job_location, u.company_name
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE a.candidate_id = ?
            ORDER BY a.applied_at DESC
            LIMIT 5
        ");
        $stmt->execute([$this->userId]);
        $recentApps = $stmt->fetchAll();

        // Upcoming interviews
        $intStmt = $this->db->prepare("
            SELECT i.*, j.title as job_title, u.name as recruiter_name, u.company_name
            FROM interviews i
            JOIN applications a ON i.application_id = a.id
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON i.recruiter_id = u.id
            WHERE i.candidate_id = ? AND i.status = 'scheduled'
            ORDER BY i.scheduled_at ASC
            LIMIT 3
        ");
        $intStmt->execute([$this->userId]);
        $upcomingInterviews = $intStmt->fetchAll();

        render_view('candidate/dashboard', [
            'pageTitle'          => 'Candidate Dashboard - ' . APP_NAME,
            'stats'              => [
                'total_apps'  => $totalApps,
                'in_review'   => $inReview,
                'interviews'  => $interviewsCount,
                'offers'      => $offersCount
            ],
            'recentApps'         => $recentApps,
            'upcomingInterviews' => $upcomingInterviews
        ]);
    }

    public function applications(): void {
        $stmt = $this->db->prepare("
            SELECT a.*, j.title as job_title, j.slug as job_slug, j.job_type, j.location as job_location, 
                   j.salary_min, j.salary_max, j.salary_currency, u.company_name, u.name as recruiter_name
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE a.candidate_id = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->execute([$this->userId]);
        $applications = $stmt->fetchAll();

        render_view('candidate/applications', [
            'pageTitle'    => 'My Job Applications - ' . APP_NAME,
            'applications' => $applications
        ]);
    }

    public function applicationDetail(int $id): void {
        $stmt = $this->db->prepare("
            SELECT a.*, j.title as job_title, j.slug as job_slug, j.job_type, j.location as job_location,
                   j.description as job_description, j.salary_min, j.salary_max, j.salary_currency,
                   u.company_name, u.name as recruiter_name, u.email as recruiter_email
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON j.recruiter_id = u.id
            WHERE a.id = ? AND a.candidate_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $this->userId]);
        $application = $stmt->fetch();

        if (!$application) {
            set_flash('error', 'Application record not found.');
            header('Location: ' . BASE_URL . '/candidate/applications');
            exit;
        }

        // Fetch application timeline history
        $histStmt = $this->db->prepare("
            SELECT h.*, u.name as changed_by_name, u.role as changed_by_role
            FROM application_history h
            LEFT JOIN users u ON h.changed_by_user_id = u.id
            WHERE h.application_id = ?
            ORDER BY h.created_at ASC
        ");
        $histStmt->execute([$id]);
        $history = $histStmt->fetchAll();

        // Fetch interviews for this application
        $intStmt = $this->db->prepare("
            SELECT i.*, u.name as recruiter_name
            FROM interviews i
            JOIN users u ON i.recruiter_id = u.id
            WHERE i.application_id = ?
            ORDER BY i.scheduled_at DESC
        ");
        $intStmt->execute([$id]);
        $interviews = $intStmt->fetchAll();

        render_view('candidate/application_detail', [
            'pageTitle'   => 'Application Tracker: ' . $application['job_title'],
            'app'         => $application,
            'history'     => $history,
            'interviews'  => $interviews
        ]);
    }

    public function interviews(): void {
        $stmt = $this->db->prepare("
            SELECT i.*, a.id as application_id, j.title as job_title, j.location as job_location,
                   u.name as recruiter_name, u.company_name, u.email as recruiter_email
            FROM interviews i
            JOIN applications a ON i.application_id = a.id
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON i.recruiter_id = u.id
            WHERE i.candidate_id = ?
            ORDER BY i.scheduled_at DESC
        ");
        $stmt->execute([$this->userId]);
        $interviews = $stmt->fetchAll();

        render_view('candidate/interviews', [
            'pageTitle'  => 'My Scheduled Interviews - ' . APP_NAME,
            'interviews' => $interviews
        ]);
    }

    public function profile(): void {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $profile = $stmt->fetch();

        render_view('candidate/profile', [
            'pageTitle' => 'Candidate Profile & Resume - ' . APP_NAME,
            'user'      => $profile
        ]);
    }

    public function updateProfile(): void {
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $experienceYears = (int)($_POST['experience_years'] ?? 0);

        if (empty($name)) {
            set_flash('error', 'Full name is required.');
            header('Location: ' . BASE_URL . '/candidate/profile');
            exit;
        }

        // Check if resume uploaded
        $resumeFilename = null;
        if (!empty($_FILES['resume']['name'])) {
            try {
                $resumeFilename = handle_file_upload($_FILES['resume'], RESUMES_PATH);
            } catch (RuntimeException $e) {
                set_flash('error', $e->getMessage());
                header('Location: ' . BASE_URL . '/candidate/profile');
                exit;
            }
        }

        $dob = trim($_POST['dob'] ?? '');
        $degree = trim($_POST['degree'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $cgpa = !empty($_POST['cgpa']) ? round((float)$_POST['cgpa'], 2) : null;
        $marks = !empty($_POST['marks']) ? round((float)$_POST['marks'], 2) : null;

        if ($resumeFilename) {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = ?, phone = ?, location = ?, dob = ?, degree = ?, institution = ?, cgpa = ?, marks = ?, headline = ?, bio = ?, skills = ?, experience_years = ?, resume_path = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $phone, $location, $dob ?: null, $degree ?: null, $institution ?: null, $cgpa, $marks, $headline, $bio, $skills, $experienceYears, $resumeFilename, $this->userId]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = ?, phone = ?, location = ?, dob = ?, degree = ?, institution = ?, cgpa = ?, marks = ?, headline = ?, bio = ?, skills = ?, experience_years = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $phone, $location, $dob ?: null, $degree ?: null, $institution ?: null, $cgpa, $marks, $headline, $bio, $skills, $experienceYears, $this->userId]);
        }

        $_SESSION['user_name'] = $name;
        $_SESSION['user_headline'] = $headline;

        audit_log($this->db, $this->userId, 'UPDATE_PROFILE', 'users', $this->userId, 'Candidate profile updated.');
        set_flash('success', 'Your profile details have been saved successfully.');
        header('Location: ' . BASE_URL . '/candidate/profile');
        exit;
    }

    public function apply(): void {
        verify_csrf();

        $jobId = (int)($_POST['job_id'] ?? 0);
        $coverLetter = trim($_POST['cover_letter'] ?? '');

        // Verify job exists and is active
        $jobStmt = $this->db->prepare("SELECT id, title, recruiter_id, min_cgpa FROM jobs WHERE id = ? AND status = 'active'");
        $jobStmt->execute([$jobId]);
        $job = $jobStmt->fetch();

        if (!$job) {
            set_flash('error', 'The job posting you are applying for is no longer active.');
            header('Location: ' . BASE_URL . '/jobs');
            exit;
        }

        // Fetch candidate profile to check for existing resume and CGPA qualification
        $candStmt = $this->db->prepare("SELECT resume_path, cgpa, marks FROM users WHERE id = ?");
        $candStmt->execute([$this->userId]);
        $cand = $candStmt->fetch();

        // Enforce Minimum CGPA Filter
        $minCgpa = (float)($job['min_cgpa'] ?? 0.0);
        $candCgpa = (float)($cand['cgpa'] ?? 0.0);
        if ($minCgpa > 0 && $candCgpa < $minCgpa) {
            set_flash('error', "Application rejected: This position requires a minimum CGPA of " . number_format($minCgpa, 2) . ". Your registered CGPA is " . number_format($candCgpa, 2) . ".");
            header('Location: ' . BASE_URL . '/jobs/' . $jobId);
            exit;
        }

        // Check if duplicate
        $dupCheck = $this->db->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
        $dupCheck->execute([$jobId, $this->userId]);
        if ($dupCheck->fetch()) {
            set_flash('warning', 'You have already submitted an application for this position.');
            header('Location: ' . BASE_URL . '/candidate/applications');
            exit;
        }

        $resumePath = $cand['resume_path'] ?? null;

        // If a new resume file is provided with the application, upload it
        if (!empty($_FILES['resume']['name'])) {
            try {
                $resumePath = handle_file_upload($_FILES['resume'], RESUMES_PATH);
                // Also update profile default resume if none was set
                if (empty($cand['resume_path'])) {
                    $this->db->prepare("UPDATE users SET resume_path = ? WHERE id = ?")->execute([$resumePath, $this->userId]);
                }
            } catch (RuntimeException $e) {
                set_flash('error', 'Resume upload error: ' . $e->getMessage());
                header('Location: ' . BASE_URL . '/jobs/' . $jobId);
                exit;
            }
        }

        if (empty($resumePath)) {
            set_flash('error', 'Please upload your resume or CV to complete your application.');
            header('Location: ' . BASE_URL . '/jobs/' . $jobId);
            exit;
        }

        // Create application
        $insStmt = $this->db->prepare("
            INSERT INTO applications (job_id, candidate_id, resume_path, cover_letter, current_stage, status)
            VALUES (?, ?, ?, ?, 'applied', 'in_progress')
        ");
        $insStmt->execute([$jobId, $this->userId, $resumePath, $coverLetter]);
        $appId = (int)$this->db->lastInsertId();

        // Create initial application history log
        $histStmt = $this->db->prepare("
            INSERT INTO application_history (application_id, from_stage, to_stage, note, changed_by_user_id)
            VALUES (?, NULL, 'applied', 'Application submitted by candidate.', ?)
        ");
        $histStmt->execute([$appId, $this->userId]);

        audit_log($this->db, $this->userId, 'SUBMIT_APPLICATION', 'applications', $appId, "Applied to job #{$jobId} ({$job['title']}).");

        set_flash('success', "Your application for '{$job['title']}' has been submitted! You can track its progress below.");
        header('Location: ' . BASE_URL . '/candidate/applications/' . $appId);
        exit;
    }

    public function withdraw(int $id): void {
        verify_csrf();

        $stmt = $this->db->prepare("SELECT id, job_id, status FROM applications WHERE id = ? AND candidate_id = ?");
        $stmt->execute([$id, $this->userId]);
        $app = $stmt->fetch();

        if ($app) {
            $this->db->prepare("UPDATE applications SET status = 'withdrawn' WHERE id = ?")->execute([$id]);
            $this->db->prepare("
                INSERT INTO application_history (application_id, from_stage, to_stage, note, changed_by_user_id)
                VALUES (?, 'current', 'withdrawn', 'Application withdrawn by candidate.', ?)
            ")->execute([$id, $this->userId]);

            audit_log($this->db, $this->userId, 'WITHDRAW_APPLICATION', 'applications', $id, 'Candidate withdrew application.');
            set_flash('info', 'Your application has been withdrawn.');
        }

        header('Location: ' . BASE_URL . '/candidate/applications');
        exit;
    }
}
