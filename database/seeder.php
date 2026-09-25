<?php
// database/seeder.php

function seed_database(PDO $db): void {
    // Check if data already exists
    $count = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count > 0) {
        echo "Database already seeded ({$count} users present). Skipping seeding.\n";
        return;
    }

    echo "Seeding initial data...\n";

    // 1. Users
    $passwordHash = password_hash('Password@123', PASSWORD_DEFAULT);
    $adminPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
    $recruiterPassword = password_hash('Recruiter@123', PASSWORD_DEFAULT);
    $candidatePassword = password_hash('Candidate@123', PASSWORD_DEFAULT);

    $users = [
        // Admin
        [
            'name' => 'System Administrator',
            'email' => 'admin@talenttrack.com',
            'password_hash' => $adminPassword,
            'role' => 'admin',
            'company_name' => 'TalentTrack HQ',
            'phone' => '+1 (555) 019-2831',
            'location' => 'San Francisco, CA',
            'headline' => 'Chief Platform Administrator',
            'bio' => 'Overseeing recruitment operations, security compliance, and platform governance.',
            'skills' => 'System Administration, HR Ops, Talent Analytics',
            'experience_years' => 10,
            'status' => 'active'
        ],
        // Recruiter 1
        [
            'name' => 'Sarah Jenkins',
            'email' => 'recruiter@techcorp.com',
            'password_hash' => $recruiterPassword,
            'role' => 'recruiter',
            'company_name' => 'TechCorp Innovations',
            'phone' => '+1 (555) 234-5678',
            'location' => 'Austin, TX',
            'headline' => 'Senior Talent Acquisition Lead at TechCorp',
            'bio' => 'Passionate about connecting exceptional engineering talent with high-impact product teams.',
            'skills' => 'Technical Hiring, Talent Pipelines, Executive Search',
            'experience_years' => 7,
            'status' => 'active'
        ],
        // Recruiter 2
        [
            'name' => 'Alex Rivera',
            'email' => 'alex@cloudscale.io',
            'password_hash' => $recruiterPassword,
            'role' => 'recruiter',
            'company_name' => 'CloudScale Systems',
            'phone' => '+1 (555) 345-6789',
            'location' => 'Seattle, WA',
            'headline' => 'Director of People & Hiring at CloudScale',
            'bio' => 'Scaling cloud infrastructure and cybersecurity engineering squads across the Americas.',
            'skills' => 'Cloud Recruitment, Culture Building, Leadership Hiring',
            'experience_years' => 8,
            'status' => 'active'
        ],
        // Candidate 1
        [
            'name' => 'David Kim',
            'email' => 'candidate@example.com',
            'password_hash' => $candidatePassword,
            'role' => 'candidate',
            'company_name' => null,
            'phone' => '+1 (555) 456-7890',
            'location' => 'New York, NY',
            'headline' => 'Senior Full-Stack Developer | PHP, JS & Cloud',
            'bio' => 'Full-stack software engineer with 6+ years designing resilient web applications, REST APIs, and microservices.',
            'skills' => 'PHP, Laravel, MySQL, JavaScript, React, Tailwind CSS, Docker, Git',
            'experience_years' => 6,
            'status' => 'active'
        ],
        // Candidate 2
        [
            'name' => 'Emily Watson',
            'email' => 'emily.designer@example.com',
            'password_hash' => $candidatePassword,
            'role' => 'candidate',
            'company_name' => null,
            'phone' => '+1 (555) 567-8901',
            'location' => 'Chicago, IL',
            'headline' => 'Product Designer & Design Systems Architect',
            'bio' => 'Crafting frictionless digital experiences with user-centric design principles and scalable UI components.',
            'skills' => 'Figma, UI/UX, Design Systems, Wireframing, HTML/CSS, Prototyping',
            'experience_years' => 4,
            'status' => 'active'
        ],
        // Candidate 3
        [
            'name' => 'Marcus Vance',
            'email' => 'marcus.devops@example.com',
            'password_hash' => $candidatePassword,
            'role' => 'candidate',
            'company_name' => null,
            'phone' => '+1 (555) 678-9012',
            'location' => 'Denver, CO',
            'headline' => 'Site Reliability & DevOps Engineer',
            'bio' => 'Specializing in Kubernetes orchestration, CI/CD automation, and high-availability cloud infrastructure.',
            'skills' => 'Kubernetes, Docker, AWS, Terraform, Linux, CI/CD, Python',
            'experience_years' => 5,
            'status' => 'active'
        ]
    ];

    $userStmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, company_name, phone, location, headline, bio, skills, experience_years, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $userStmt->execute([
            $u['name'], $u['email'], $u['password_hash'], $u['role'],
            $u['company_name'], $u['phone'], $u['location'], $u['headline'],
            $u['bio'], $u['skills'], $u['experience_years'], $u['status']
        ]);
    }

    // 2. Job Categories
    $categories = [
        ['Engineering & Technology', 'engineering-tech', 'code', 'Software engineering, web, mobile, DevOps and systems architecture.'],
        ['Product & UX Design', 'product-design', 'layout', 'Product management, user experience, UI design and graphic arts.'],
        ['Data & AI Solutions', 'data-ai', 'database', 'Machine learning, business intelligence, data engineering and analytics.'],
        ['Marketing & Growth', 'marketing-growth', 'trending-up', 'Growth marketing, SEO, content creation, and product brand campaigns.'],
        ['Sales & Partnerships', 'sales-business', 'briefcase', 'Account executive, business development, and enterprise partner sales.'],
        ['People & Operations', 'people-operations', 'users', 'Talent acquisition, HR business partner, office and people operations.']
    ];

    $catStmt = $db->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }

    // 3. Jobs
    $jobs = [
        [
            'recruiter_id' => 2, // Sarah (TechCorp)
            'category_id' => 1, // Engineering
            'title' => 'Senior Full-Stack PHP & Modern Web Engineer',
            'slug' => 'senior-full-stack-php-engineer',
            'job_type' => 'full-time',
            'experience_level' => 'senior',
            'location' => 'Austin, TX (Hybrid)',
            'salary_min' => 125000,
            'salary_max' => 160000,
            'salary_currency' => 'USD',
            'description' => "TechCorp Innovations is seeking a seasoned Senior Full-Stack Engineer to spearhead the architecture and delivery of our flagship enterprise SaaS products.\n\nYou will work directly with product managers and fellow engineers to craft high-throughput web architectures, maintain robust API integrations, and ensure seamless candidate experience.",
            'requirements' => "- 5+ years of robust server-side development experience with PHP and SQL (MySQL or PostgreSQL).\n- Strong client-side proficiency with modern JavaScript, HTML5, and responsive CSS.\n- Experience designing secure RESTful APIs, relational schemas, and microservice integration.\n- Proficiency in version control (Git), Docker, and continuous integration pipelines.\n- Solid understanding of application security (OWASP standards, CSRF, XSS prevention).",
            'benefits' => "Competitive base salary with equity options\nComprehensive Medical, Dental & Vision coverage\n$3,000 annual continuous learning & conference budget\nFlexible hybrid work schedule with home office stipend",
            'deadline' => date('Y-m-d', strtotime('+45 days')),
            'is_featured' => 1,
            'status' => 'active'
        ],
        [
            'recruiter_id' => 2, // Sarah (TechCorp)
            'category_id' => 2, // Product Design
            'title' => 'Lead UI/UX Product Designer',
            'slug' => 'lead-ui-ux-product-designer',
            'job_type' => 'full-time',
            'experience_level' => 'lead',
            'location' => 'Remote (US)',
            'salary_min' => 130000,
            'salary_max' => 165000,
            'salary_currency' => 'USD',
            'description' => "We are looking for a visionary Lead Product Designer to guide our digital design strategy, define comprehensive design systems, and deliver delightfully intuitive workflows across web and mobile platforms.",
            'requirements' => "- 6+ years in product design for B2B SaaS or consumer web applications.\n- Mastery of Figma, design systems, wireframing, and interactive prototyping.\n- Proven portfolio demonstrating end-to-end design lifecycles from user interviews to shipped features.\n- Strong collaboration skills bridging engineering, product management, and customer success teams.",
            'benefits' => "100% remote-first company culture\nUnlimited PTO and wellness stipends\nTop-of-the-line Apple workstation setup\n401(k) retirement match up to 5%",
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'is_featured' => 1,
            'status' => 'active'
        ],
        [
            'recruiter_id' => 3, // Alex (CloudScale)
            'category_id' => 1, // Engineering
            'title' => 'Cloud Platform & DevOps Specialist',
            'slug' => 'cloud-platform-devops-specialist',
            'job_type' => 'full-time',
            'experience_level' => 'senior',
            'location' => 'Seattle, WA (Onsite)',
            'salary_min' => 140000,
            'salary_max' => 180000,
            'salary_currency' => 'USD',
            'description' => "CloudScale is on the hunt for a DevOps Specialist to automate, monitor, and scale our multi-region Kubernetes clusters and cloud infrastructure.",
            'requirements' => "- 4+ years managing production workloads on AWS, GCP, or Azure.\n- Deep hands-on experience with Kubernetes, Docker containerization, and Helm charts.\n- Infrastructure as Code expertise with Terraform and Ansible.\n- Strong background in Linux systems administration, networking, and observability (Prometheus, Grafana).",
            'benefits' => "Full health and dental coverage\nSubsidized public transit and parking\nGenerous annual bonus structure\nOnsite gym and catered lunches",
            'deadline' => date('Y-m-d', strtotime('+60 days')),
            'is_featured' => 1,
            'status' => 'active'
        ],
        [
            'recruiter_id' => 3, // Alex (CloudScale)
            'category_id' => 1, // Engineering
            'title' => 'Junior Backend Software Developer',
            'slug' => 'junior-backend-developer',
            'job_type' => 'contract',
            'experience_level' => 'entry',
            'location' => 'Remote',
            'salary_min' => 65000,
            'salary_max' => 85000,
            'salary_currency' => 'USD',
            'description' => "Kickstart your career with CloudScale's backend development team. You will write backend services, write unit test suites, and assist in API documentation.",
            'requirements' => "- Degree in Computer Science or equivalent practical bootcamp/project experience.\n- Familiarity with PHP, Python, or Node.js backend development.\n- Knowledge of relational database query design (SQL).\n- High eagerness to learn and grow in a collaborative sprint environment.",
            'benefits' => "Mentorship from Staff and Principal Engineers\nFlexible working hours\nCertificate exam fee sponsorships",
            'deadline' => date('Y-m-d', strtotime('+20 days')),
            'is_featured' => 0,
            'status' => 'active'
        ],
        [
            'recruiter_id' => 2, // Sarah (TechCorp)
            'category_id' => 4, // Marketing
            'title' => 'Growth Marketing & Brand Specialist',
            'slug' => 'growth-marketing-specialist',
            'job_type' => 'full-time',
            'experience_level' => 'mid',
            'location' => 'New York, NY',
            'salary_min' => 90000,
            'salary_max' => 115000,
            'salary_currency' => 'USD',
            'description' => "Lead multi-channel customer acquisition campaigns, brand storytelling, and demand-generation pipelines for cutting-edge enterprise software solutions.",
            'requirements' => "- 3+ years in growth marketing, paid acquisition, and content strategy.\n- Proven track record optimizing conversion funnels and ROAS across LinkedIn, Google Ads, and organic channels.\n- Strong analytical mindset using Google Analytics, HubSpot, and BI dashboards.",
            'benefits' => "Generous bonus tied to revenue growth\nAnnual company retreats\nHealth, Dental, and 401(k)",
            'deadline' => date('Y-m-d', strtotime('+40 days')),
            'is_featured' => 0,
            'status' => 'active'
        ]
    ];

    $jobStmt = $db->prepare("INSERT INTO jobs (recruiter_id, category_id, title, slug, job_type, experience_level, location, salary_min, salary_max, salary_currency, description, requirements, benefits, deadline, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($jobs as $j) {
        $jobStmt->execute([
            $j['recruiter_id'], $j['category_id'], $j['title'], $j['slug'],
            $j['job_type'], $j['experience_level'], $j['location'],
            $j['salary_min'], $j['salary_max'], $j['salary_currency'],
            $j['description'], $j['requirements'], $j['benefits'],
            $j['deadline'], $j['is_featured'], $j['status']
        ]);
    }

    // 4. Sample Candidate Applications
    // App 1: David Kim (Candidate #4) -> Senior Full-Stack PHP (Job #1) in 'interview' stage
    // App 2: Emily Watson (Candidate #5) -> Lead UI/UX Product Designer (Job #2) in 'screening' stage
    // App 3: Marcus Vance (Candidate #6) -> Cloud Platform & DevOps (Job #3) in 'assessment' stage
    // App 4: David Kim (Candidate #4) -> Junior Backend Developer (Job #4) in 'offer' stage

    $apps = [
        [
            'job_id' => 1,
            'candidate_id' => 4,
            'resume_path' => 'sample_resume_david_kim.pdf',
            'cover_letter' => "Dear Hiring Team,\n\nI am thrilled to apply for the Senior Full-Stack PHP & Modern Web Engineer role at TechCorp. Having engineered full-stack enterprise web platforms for over six years, I have deep hands-on expertise with PHP 8, SQL optimization, and reactive frontends. I would love the opportunity to contribute to your core SaaS roadmap.",
            'current_stage' => 'interview',
            'recruiter_rating' => 5,
            'recruiter_notes' => 'Impressive portfolio and deep PHP/SQL expertise. Completed initial phone screen with flying colors. Moving to technical round with engineering team.',
            'status' => 'in_progress'
        ],
        [
            'job_id' => 2,
            'candidate_id' => 5,
            'resume_path' => 'sample_resume_emily_watson.pdf',
            'cover_letter' => "Hello TechCorp Team,\n\nI have been following TechCorp's product journey with great admiration. As a product designer with a strong foundation in scalable design tokens and design systems, I believe I can elevate your platform's usability and visual coherence.",
            'current_stage' => 'screening',
            'recruiter_rating' => 4,
            'recruiter_notes' => 'Portfolio has stellar case studies. Reviewing background with Product Director.',
            'status' => 'in_progress'
        ],
        [
            'job_id' => 3,
            'candidate_id' => 6,
            'resume_path' => 'sample_resume_marcus_vance.pdf',
            'cover_letter' => "Hi Alex,\n\nI am excited by CloudScale's mission to optimize multi-cloud infrastructure. With 5 years orchestrating Kubernetes and AWS Terraform pipelines, I am confident I can support your high availability and reliability goals.",
            'current_stage' => 'assessment',
            'recruiter_rating' => 4,
            'recruiter_notes' => 'Passed initial screening. Candidate sent take-home infrastructure architecture challenge.',
            'status' => 'in_progress'
        ],
        [
            'job_id' => 4,
            'candidate_id' => 4,
            'resume_path' => 'sample_resume_david_kim.pdf',
            'cover_letter' => "Dear CloudScale Hiring Lead,\n\nI would be delighted to bring my backend engineering experience to CloudScale's team.",
            'current_stage' => 'offer',
            'recruiter_rating' => 5,
            'recruiter_notes' => 'Exceptional technical depth. Extended formal employment offer letter.',
            'status' => 'in_progress'
        ]
    ];

    $appStmt = $db->prepare("INSERT INTO applications (job_id, candidate_id, resume_path, cover_letter, current_stage, recruiter_rating, recruiter_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($apps as $a) {
        $appStmt->execute([
            $a['job_id'], $a['candidate_id'], $a['resume_path'],
            $a['cover_letter'], $a['current_stage'], $a['recruiter_rating'],
            $a['recruiter_notes'], $a['status']
        ]);
    }

    // 5. Application History (Tracking Stage Progression)
    $historyRecords = [
        // App 1 progression (David Kim)
        [1, null, 'applied', 'Application received via portal.', 4, date('Y-m-d H:i:s', strtotime('-5 days'))],
        [1, 'applied', 'screening', 'Resume screened by Sarah Jenkins. Qualifications verified.', 2, date('Y-m-d H:i:s', strtotime('-4 days'))],
        [1, 'screening', 'interview', 'Candidate invited to Round 1 Technical Architecture interview.', 2, date('Y-m-d H:i:s', strtotime('-2 days'))],

        // App 2 progression (Emily Watson)
        [2, null, 'applied', 'Application submitted with Figma portfolio.', 5, date('Y-m-d H:i:s', strtotime('-3 days'))],
        [2, 'applied', 'screening', 'Under initial recruiter portfolio review.', 2, date('Y-m-d H:i:s', strtotime('-1 day'))],

        // App 3 progression (Marcus Vance)
        [3, null, 'applied', 'Application received.', 6, date('Y-m-d H:i:s', strtotime('-6 days'))],
        [3, 'applied', 'screening', 'Screening call conducted.', 3, date('Y-m-d H:i:s', strtotime('-4 days'))],
        [3, 'screening', 'assessment', 'Take-home Kubernetes assessment provided.', 3, date('Y-m-d H:i:s', strtotime('-2 days'))],

        // App 4 progression (David Kim -> Offer)
        [4, null, 'applied', 'Application received.', 4, date('Y-m-d H:i:s', strtotime('-10 days'))],
        [4, 'applied', 'screening', 'Screened by Alex.', 3, date('Y-m-d H:i:s', strtotime('-8 days'))],
        [4, 'screening', 'interview', 'Completed technical interview with Staff Engineer.', 3, date('Y-m-d H:i:s', strtotime('-5 days'))],
        [4, 'interview', 'offer', 'Formal compensation package and offer letter sent.', 3, date('Y-m-d H:i:s', strtotime('-1 day'))]
    ];

    $histStmt = $db->prepare("INSERT INTO application_history (application_id, from_stage, to_stage, note, changed_by_user_id, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($historyRecords as $h) {
        $histStmt->execute($h);
    }

    // 6. Scheduled Interviews
    $interviews = [
        [
            'application_id' => 1,
            'recruiter_id' => 2, // Sarah
            'candidate_id' => 4, // David Kim
            'title' => 'Technical System Architecture Interview',
            'interview_type' => 'technical',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+2 days 14:00:00')),
            'duration_minutes' => 60,
            'meeting_link' => 'https://meet.google.com/talenttrack-eng-interview',
            'location' => 'Google Meet Virtual Video Call',
            'status' => 'scheduled',
            'feedback' => null,
            'rating' => null
        ],
        [
            'application_id' => 4,
            'recruiter_id' => 3, // Alex
            'candidate_id' => 4, // David Kim
            'title' => 'Final Offer Discussion & Team Introduction',
            'interview_type' => 'final',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('-2 days 11:00:00')),
            'duration_minutes' => 30,
            'meeting_link' => 'https://meet.google.com/talenttrack-offer-chat',
            'location' => 'Google Meet Virtual Video Call',
            'status' => 'completed',
            'feedback' => 'Candidate accepted offer terms enthusiastically. Formal start date set.',
            'rating' => 5
        ]
    ];

    $intStmt = $db->prepare("INSERT INTO interviews (application_id, recruiter_id, candidate_id, title, interview_type, scheduled_at, duration_minutes, meeting_link, location, status, feedback, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($interviews as $i) {
        $intStmt->execute([
            $i['application_id'], $i['recruiter_id'], $i['candidate_id'],
            $i['title'], $i['interview_type'], $i['scheduled_at'],
            $i['duration_minutes'], $i['meeting_link'], $i['location'],
            $i['status'], $i['feedback'], $i['rating']
        ]);
    }

    // 7. Audit Logs
    $logs = [
        [1, 'INITIALIZE_SYSTEM', 'system', 1, 'System tables and default demo records seeded.', '127.0.0.1'],
        [2, 'POST_JOB', 'jobs', 1, 'Recruiter Sarah posted "Senior Full-Stack PHP & Modern Web Engineer"', '127.0.0.1'],
        [4, 'SUBMIT_APPLICATION', 'applications', 1, 'Candidate David Kim applied for Senior Full-Stack PHP role', '127.0.0.1'],
        [2, 'UPDATE_STAGE', 'applications', 1, 'Application stage moved to interview', '127.0.0.1'],
    ];

    $logStmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($logs as $l) {
        $logStmt->execute($l);
    }

    echo "Seeding completed successfully with realistic sample data!\n";
}
