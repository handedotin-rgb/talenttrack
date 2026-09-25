<?php
// views/public/home.php
?>
<div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); color: #ffffff; padding: 4.5rem 1.5rem 5rem 1.5rem; text-align: center; position: relative;">
    <div style="max-width: 900px; margin: 0 auto;">
        <span style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); padding: 0.35rem 1rem; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 1.2rem;">
            🎯 Full-Cycle Recruitment Lifecycle System
        </span>
        <h1 style="font-size: 3rem; color: #ffffff; margin-bottom: 1.25rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1.15;">
            Track Every Stage of the <span style="color: #67e8f9;">Recruitment Journey</span>
        </h1>
        <p style="font-size: 1.2rem; color: #cbd5e1; max-width: 720px; margin: 0 auto 2.5rem auto;">
            A unified tracking ecosystem for <strong style="color: #ffffff;">Candidates</strong>, <strong style="color: #ffffff;">Recruiters</strong>, and <strong style="color: #ffffff;">Administrators</strong> with real-time status pipelines, interview coordination, and hiring analytics.
        </p>

        <!-- Search Bar -->
        <form action="<?= BASE_URL ?>/jobs" method="GET" style="background: #ffffff; padding: 0.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); display: flex; flex-wrap: wrap; gap: 0.5rem; max-width: 780px; margin: 0 auto 2rem auto;">
            <div style="flex: 2; min-width: 220px; display: flex; align-items: center; padding-left: 0.75rem;">
                <span style="font-size: 1.2rem; margin-right: 0.5rem;">🔍</span>
                <input type="text" name="q" placeholder="Job title, keywords, or tech stack..." style="width: 100%; border: none; outline: none; font-size: 1rem; color: var(--slate-800);">
            </div>
            <div style="flex: 1; min-width: 160px; display: flex; align-items: center; border-left: 1px solid var(--slate-200); padding-left: 0.75rem;">
                <span style="font-size: 1.2rem; margin-right: 0.5rem;">📍</span>
                <input type="text" name="location" placeholder="City or Remote" style="width: 100%; border: none; outline: none; font-size: 0.95rem; color: var(--slate-800);">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.8rem; font-size: 1rem; border-radius: var(--radius-md);">
                Find Jobs
            </button>
        </form>

        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/jobs" class="btn btn-secondary" style="padding: 0.65rem 1.5rem; font-weight: 600;">
                🔍 Browse All Jobs
            </a>
            <a href="<?= BASE_URL ?>/register" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 600; background: #38bdf8; color: #0f172a; border-color: #38bdf8;">
                🚀 Create Free Account
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Platform Metrics Bar -->
    <div class="stats-grid" style="margin-top: -3.5rem; position: relative; z-index: 10;">
        <div class="stat-card">
            <div class="stat-icon">💼</div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_jobs']) ?></div>
                <div class="stat-label">Active Job Postings</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_companies']) ?></div>
                <div class="stat-label">Hiring Companies</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_candidates']) ?></div>
                <div class="stat-label">Registered Candidates</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎉</div>
            <div>
                <div class="stat-number"><?= number_format($stats['total_hires']) ?></div>
                <div class="stat-label">Successful Placements</div>
            </div>
        </div>
    </div>

    <!-- The 2 Modules Showcase -->
    <div style="margin: 4rem 0 3rem 0;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <h2 style="font-size: 2rem; margin-bottom: 0.5rem;">A Unified Recruitment Experience</h2>
            <p>Designed for candidates seeking their dream roles and recruiters building high-performing teams.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 2rem;">
            <!-- Candidate Module -->
            <div class="card" style="border-top: 4px solid var(--primary); padding: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        👤
                    </div>
                    <div>
                        <h3 style="font-size: 1.3rem;">For Job Seekers & Candidates</h3>
                        <span style="font-size: 0.85rem; color: var(--slate-500);">Verified Opportunities & Transparent Hiring</span>
                    </div>
                </div>
                <p style="font-size: 0.95rem; margin-bottom: 1.25rem; line-height: 1.6;">
                    Apply for verified opportunities with a structured profile, academic records (CGPA, marks), and resume. Monitor real-time application status through visual pipeline stages and attend scheduled interviews.
                </p>
                <ul style="list-style: none; font-size: 0.88rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.75rem;">
                    <li>✓ Academic criteria tracking & instant eligibility check</li>
                    <li>✓ Interactive visual progress stepper (Applied &rarr; Offer)</li>
                    <li>✓ Complete history & recruiter feedback timeline</li>
                    <li>✓ Video interview calendar & direct meeting links</li>
                </ul>
                <a href="<?= BASE_URL ?>/register" class="btn btn-outline btn-block">
                    Register as Candidate &rarr;
                </a>
            </div>

            <!-- Recruiter Module -->
            <div class="card" style="border-top: 4px solid var(--secondary); padding: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #e0f2fe; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        💼
                    </div>
                    <div>
                        <h3 style="font-size: 1.3rem;">For Recruiters & Hiring Teams</h3>
                        <span style="font-size: 0.85rem; color: var(--slate-500);">Comprehensive Pipeline & Talent Sourcing</span>
                    </div>
                </div>
                <p style="font-size: 0.95rem; margin-bottom: 1.25rem; line-height: 1.6;">
                    Publish openings across comprehensive categories, define academic filters like minimum CGPA, manage applicants in a dynamic pipeline board, schedule rounds, and evaluate candidates.
                </p>
                <ul style="list-style: none; font-size: 0.88rem; color: var(--slate-700); display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.75rem;">
                    <li>✓ Custom job categories + "Others" option support</li>
                    <li>✓ Set minimum CGPA thresholds to filter qualified applicants</li>
                    <li>✓ Stage-by-stage applicant evaluation pipeline</li>
                    <li>✓ Integrated interview scheduler with 5-star ratings</li>
                </ul>
                <a href="<?= BASE_URL ?>/register" class="btn btn-outline btn-block">
                    Register as Recruiter &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Job Vacancies -->
    <div style="margin: 4rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h2 style="font-size: 1.8rem;">Featured Opportunities</h2>
                <p>High-priority openings from verified hiring teams</p>
            </div>
            <a href="<?= BASE_URL ?>/jobs" class="btn btn-secondary">
                View All Jobs &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
            <?php foreach ($featuredJobs as $job): ?>
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <span class="badge badge-blue"><?= e($job['category_name']) ?></span>
                            <span style="font-size: 0.8rem; color: var(--slate-400);"><?= time_ago($job['created_at']) ?></span>
                        </div>
                        <h3 style="font-size: 1.15rem; margin-bottom: 0.4rem;">
                            <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>"><?= e($job['title']) ?></a>
                        </h3>
                        <div style="font-size: 0.9rem; font-weight: 600; color: var(--slate-700); margin-bottom: 0.75rem;">
                            🏢 <?= e($job['company_name'] ?: 'TechCorp') ?> &bull; <span style="font-weight: 400; color: var(--slate-500);"><?= e($job['location']) ?></span>
                        </div>
                        <p style="font-size: 0.88rem; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= e($job['description']) ?>
                        </p>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--slate-500); text-transform: uppercase; font-weight: 600;">Salary Range</div>
                            <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                <?= format_salary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" class="btn btn-primary btn-sm">
                            Apply Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Job Categories Grid -->
    <div style="margin: 4rem 0 2rem 0; background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.8rem;">Explore By Job Category</h2>
            <p>Find roles matching your technical and professional specialization</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/jobs?category=<?= $cat['id'] ?>" class="card" style="padding: 1.25rem; border: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: space-between; text-decoration: none;">
                    <div>
                        <div style="font-weight: 700; color: var(--slate-900); font-size: 0.95rem; margin-bottom: 0.2rem;">
                            <?= e($cat['name']) ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--slate-500);">
                            <?= (int)$cat['job_count'] ?> open position<?= $cat['job_count'] == 1 ? '' : 's' ?>
                        </div>
                    </div>
                    <span style="color: var(--primary); font-size: 1.2rem;">&rarr;</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
