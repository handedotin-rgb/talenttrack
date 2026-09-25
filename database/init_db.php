<?php
// database/init_db.php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

function initialize_database(): void {
    $db = get_db();
    $driver = DB_DRIVER;

    echo "Initializing database using driver: [{$driver}]...\n";

    if ($driver === 'sqlite') {
        // SQLite Table Creation
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'candidate',
                company_name TEXT NULL,
                company_website TEXT NULL,
                phone TEXT NULL,
                dob TEXT NULL,
                location TEXT NULL,
                degree TEXT NULL,
                institution TEXT NULL,
                cgpa REAL NULL,
                marks REAL NULL,
                headline TEXT NULL,
                bio TEXT NULL,
                skills TEXT NULL,
                experience_years INTEGER DEFAULT 0,
                resume_path TEXT NULL,
                avatar TEXT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                is_verified INTEGER DEFAULT 0,
                otp_code TEXT NULL,
                otp_expires_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                icon TEXT DEFAULT 'briefcase',
                description TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS jobs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                recruiter_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                slug TEXT NOT NULL,
                job_type TEXT NOT NULL DEFAULT 'full-time',
                experience_level TEXT NOT NULL DEFAULT 'mid',
                location TEXT NOT NULL,
                salary_min INTEGER NULL,
                salary_max INTEGER NULL,
                salary_currency TEXT DEFAULT 'USD',
                min_cgpa REAL DEFAULT 0.0,
                custom_category TEXT NULL,
                description TEXT NOT NULL,
                requirements TEXT NOT NULL,
                benefits TEXT NULL,
                deadline DATE NULL,
                is_featured INTEGER DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'active',
                views_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
            )",
            "CREATE TABLE IF NOT EXISTS applications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                job_id INTEGER NOT NULL,
                candidate_id INTEGER NOT NULL,
                resume_path TEXT NOT NULL,
                cover_letter TEXT NULL,
                current_stage TEXT NOT NULL DEFAULT 'applied',
                recruiter_rating INTEGER DEFAULT NULL,
                recruiter_notes TEXT NULL,
                status TEXT NOT NULL DEFAULT 'in_progress',
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(job_id, candidate_id),
                FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
                FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS application_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                application_id INTEGER NOT NULL,
                from_stage TEXT NULL,
                to_stage TEXT NOT NULL,
                note TEXT NULL,
                changed_by_user_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
                FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS interviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                application_id INTEGER NOT NULL,
                recruiter_id INTEGER NOT NULL,
                candidate_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                interview_type TEXT NOT NULL DEFAULT 'technical',
                scheduled_at DATETIME NOT NULL,
                duration_minutes INTEGER DEFAULT 45,
                meeting_link TEXT NULL,
                location TEXT NULL,
                status TEXT NOT NULL DEFAULT 'scheduled',
                feedback TEXT NULL,
                rating INTEGER NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
                FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                action TEXT NOT NULL,
                entity_type TEXT NOT NULL,
                entity_id INTEGER NULL,
                details TEXT NULL,
                ip_address TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )"
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }
    } else {
        // MySQL Execution
        $schemaFile = __DIR__ . '/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $db->exec($sql);
        }
    }

    echo "Tables successfully created.\n";

    // Call Seeder
    require_once __DIR__ . '/seeder.php';
    seed_database($db);
}

// Run if called directly
if (php_sapi_name() === 'cli' || !empty($_GET['run_init'])) {
    initialize_database();
}
