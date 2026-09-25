<?php
// database/migrate.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = get_db();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Add users columns
$userCols = [
    'dob' => 'TEXT NULL',
    'cgpa' => 'REAL NULL',
    'marks' => 'REAL NULL',
    'degree' => 'TEXT NULL',
    'institution' => 'TEXT NULL',
    'company_website' => 'TEXT NULL',
    'is_verified' => 'INTEGER DEFAULT 0',
    'otp_code' => 'TEXT NULL',
    'otp_expires_at' => 'DATETIME NULL'
];

if (DB_DRIVER === 'sqlite') {
    $existingUserCols = array_column($db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    foreach ($userCols as $col => $type) {
        if (!in_array($col, $existingUserCols, true)) {
            $db->exec("ALTER TABLE users ADD COLUMN $col $type");
            echo "Added column users.$col\n";
        }
    }

    // Add jobs columns
    $jobCols = [
        'min_cgpa' => 'REAL DEFAULT 0.0',
        'custom_category' => 'TEXT NULL'
    ];
    $existingJobCols = array_column($db->query("PRAGMA table_info(jobs)")->fetchAll(PDO::FETCH_ASSOC), 'name');
    foreach ($jobCols as $col => $type) {
        if (!in_array($col, $existingJobCols, true)) {
            $db->exec("ALTER TABLE jobs ADD COLUMN $col $type");
            echo "Added column jobs.$col\n";
        }
    }
}

// Add new categories
$extraCategories = [
    ['Software Development & DevOps', 'software-devops', 'terminal', 'Backend, frontend, full-stack, cloud and site reliability engineering.'],
    ['Finance & Accounting', 'finance-accounting', 'dollar-sign', 'Financial analysis, corporate accounting, auditing, and fintech.'],
    ['Customer Success & Support', 'customer-success', 'headphones', 'Client onboarding, technical support, account management, and CX.'],
    ['Healthcare & Medical', 'healthcare-medical', 'activity', 'Clinical research, hospital administration, telemedicine, and healthcare tech.'],
    ['Education & E-Learning', 'education-elearning', 'book-open', 'Teaching, instructional design, academic counseling, and EdTech.'],
    ['Legal & Compliance', 'legal-compliance', 'shield', 'Corporate legal counsel, intellectual property, regulatory compliance, and risk.'],
    ['Supply Chain & Logistics', 'supply-chain', 'truck', 'Procurement, inventory management, fleet operations, and global logistics.'],
    ['Content & Media', 'content-media', 'edit-3', 'Technical writing, journalism, copywriting, video editing, and digital media.'],
    ['Others', 'others', 'more-horizontal', 'Specialized roles, cross-functional disciplines, and custom career categories.']
];

$catCheck = $db->prepare("SELECT id FROM categories WHERE slug = ?");
$catInsert = $db->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
foreach ($extraCategories as $cat) {
    $catCheck->execute([$cat[1]]);
    if (!$catCheck->fetch()) {
        $catInsert->execute($cat);
        echo "Inserted category: " . $cat[0] . "\n";
    }
}

// Populate sample values for existing candidates
$db->exec("UPDATE users SET cgpa = 8.5, marks = 85.0, dob = '2000-05-15', degree = 'B.Tech Computer Science', institution = 'State University', location = 'New York, NY' WHERE role = 'candidate' AND (cgpa IS NULL OR cgpa = 0)");
$db->exec("UPDATE jobs SET min_cgpa = 7.0 WHERE id = 1");
$db->exec("UPDATE users SET is_verified = 1 WHERE is_verified IS NULL OR is_verified = 0");

echo "Migration completed successfully!\n";
