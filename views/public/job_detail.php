<?php
// views/public/job_detail.php

$candCgpa = (float)($candidateProfile['cgpa'] ?? 0);
$minCgpa = (float)($job['min_cgpa'] ?? 0);
$isEligible = ($minCgpa <= 0) || ($candCgpa >= $minCgpa);
$categoryDisplay = !empty($job['custom_category']) ? $job['custom_category'] : $job['category_name'];
?>
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 1.5rem; font-size: 0.88rem; color: var(--slate-500);">
        <a href="<?= BASE_URL ?>/jobs" style="color: var(--slate-600);">&larr; Back to All Openings</a>
        <span style="margin: 0 0.5rem;">/</span>
        <span><?= e($categoryDisplay) ?></span>
        <span style="margin: 0 0.5rem;">/</span>
        <span style="color: var(--slate-800); font-weight: 600;"><?= e($job['title']) ?></span>
    </div>

    <!-- Job Header Card -->
    <div class="card" style="padding: 2.5rem; margin-bottom: 2rem; border-left: 5px solid var(--primary);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
            <div style="max-width: 650px;">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                    <span class="badge badge-blue"><?= e($categoryDisplay) ?></span>
                    <span class="badge badge-purple"><?= ucfirst(e($job['job_type'])) ?></span>
                    <span class="badge badge-slate"><?= ucfirst(e($job['experience_level'])) ?> Level</span>
                    <?php if ($minCgpa > 0): ?>
                        <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                            🎯 Min CGPA: <?= number_format($minCgpa, 2) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--slate-900);">
                    <?= e($job['title']) ?>
                </h1>

                <div style="font-size: 1.1rem; color: var(--slate-600); margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <span style="font-weight: 700; color: var(--slate-800);">
                        🏢 <?= e($job['company_name'] ?: 'TechCorp Innovations') ?>
                    </span>
                    <span>📍 <?= e($job['location']) ?></span>
                    <span>🕒 Posted <?= time_ago($job['created_at']) ?></span>
                </div>

                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.95rem; color: var(--slate-600);">
                    <div>
                        <span style="color: var(--slate-400);">Salary:</span>
                        <strong style="color: var(--primary);">
                            <?= format_salary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                        </strong>
                    </div>
                    <?php if (!empty($job['deadline'])): ?>
                        <div>
                            <span style="color: var(--slate-400);">Application Deadline:</span>
                            <strong><?= date('M j, Y', strtotime($job['deadline'])) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span style="color: var(--slate-400);">Views:</span>
                        <strong><?= (int)$job['views_count'] ?></strong>
                    </div>
                </div>
            </div>

            <!-- Apply CTA Section -->
            <div style="text-align: right; min-width: 250px;">
                <?php if ($hasApplied): ?>
                    <div style="background: var(--success-light); border: 1px solid #a7f3d0; padding: 1.25rem; border-radius: var(--radius-md); text-align: left;">
                        <div style="color: #065f46; font-weight: 700; margin-bottom: 0.3rem;">✓ Applied on <?= date('M j', strtotime($existingApp['applied_at'])) ?></div>
                        <div style="font-size: 0.85rem; color: #047857; margin-bottom: 0.75rem;">Current Status: <?= stage_badge($existingApp['current_stage']) ?></div>
                        <a href="<?= BASE_URL ?>/candidate/applications/<?= $existingApp['id'] ?>" class="btn btn-primary btn-sm btn-block">
                            Track Status & Timeline &rarr;
                        </a>
                    </div>
                <?php elseif (is_authenticated() && has_role('candidate')): ?>
                    <?php if ($isEligible): ?>
                        <button type="button" class="btn btn-primary btn-lg btn-block" data-modal-target="#applyModal">
                            Apply Now
                        </button>
                        <div style="font-size: 0.78rem; color: var(--slate-500); margin-top: 0.5rem; text-align: center;">
                            Your CGPA: <strong><?= number_format($candCgpa, 2) ?></strong> (Eligible ✅)
                        </div>
                    <?php else: ?>
                        <div style="background: #fef2f2; border: 1px solid #f87171; padding: 1.25rem; border-radius: var(--radius-md); text-align: left;">
                            <div style="color: #991b1b; font-weight: 700; margin-bottom: 0.3rem;">⚠️ Eligibility Notice</div>
                            <div style="font-size: 0.85rem; color: #b91c1c; line-height: 1.4; margin-bottom: 0.5rem;">
                                This role requires a minimum CGPA of <strong><?= number_format($minCgpa, 2) ?></strong>. Your registered CGPA is <strong><?= number_format($candCgpa, 2) ?></strong>.
                            </div>
                            <div style="font-size: 0.78rem; color: #7f1d1d; font-weight: 600;">
                                Candidates below the threshold cannot apply.
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif (is_authenticated() && has_role('recruiter')): ?>
                    <div style="background: var(--slate-100); padding: 1rem; border-radius: var(--radius-md); text-align: center; font-size: 0.88rem;">
                        <span style="font-weight: 700;">Recruiter Mode</span>
                        <div style="margin-top: 0.5rem;">
                            <a href="<?= BASE_URL ?>/recruiter/pipeline" class="btn btn-secondary btn-sm">View Pipeline</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-lg btn-block">
                        Sign In to Apply
                    </a>
                    <div style="margin-top: 0.5rem; text-align: center; font-size: 0.82rem; color: var(--slate-500);">
                        New candidate? <a href="<?= BASE_URL ?>/register" style="font-weight: 600;">Create account</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Job Body Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Main Description -->
        <div>
            <div class="card" style="padding: 2rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.3rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Role Overview
                </h3>
                <div style="font-size: 0.98rem; line-height: 1.7; color: var(--slate-700); white-space: pre-line; margin-bottom: 2rem;">
                    <?= e($job['description']) ?>
                </div>

                <h3 style="font-size: 1.3rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Qualifications & Requirements
                </h3>
                <div style="font-size: 0.98rem; line-height: 1.7; color: var(--slate-700); white-space: pre-line; margin-bottom: 2rem;">
                    <?= e($job['requirements']) ?>
                </div>

                <?php if (!empty($job['benefits'])): ?>
                    <h3 style="font-size: 1.3rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        Perks & Benefits
                    </h3>
                    <div style="font-size: 0.98rem; line-height: 1.7; color: var(--slate-700); white-space: pre-line;">
                        <?= e($job['benefits']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Details -->
        <div>
            <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 1rem;">About the Hiring Team</h4>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;">
                        <?= strtoupper(substr($job['recruiter_name'] ?? 'T', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700;"><?= e($job['recruiter_name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--slate-500);"><?= e($job['company_name'] ?: 'Recruiting Partner') ?></div>
                    </div>
                </div>
                <?php if (!empty($job['recruiter_bio'])): ?>
                    <p style="font-size: 0.88rem; line-height: 1.5; margin-bottom: 1rem;">
                        <?= e($job['recruiter_bio']) ?>
                    </p>
                <?php endif; ?>
                <div style="font-size: 0.85rem; color: var(--slate-500); border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                    Verified Employer on <?= APP_NAME ?>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card" style="padding: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Job Summary</h4>
                <ul style="list-style: none; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.6rem; color: var(--slate-600);">
                    <li><strong>Category:</strong> <?= e($categoryDisplay) ?></li>
                    <li><strong>Job Type:</strong> <?= ucfirst(e($job['job_type'])) ?></li>
                    <li><strong>Experience:</strong> <?= ucfirst(e($job['experience_level'])) ?></li>
                    <li><strong>Location:</strong> <?= e($job['location']) ?></li>
                    <li><strong>Min. Required CGPA:</strong> 
                        <?php if ($minCgpa > 0): ?>
                            <span style="color: #b45309; font-weight: 700;"><?= number_format($minCgpa, 2) ?> / 10.0</span>
                        <?php else: ?>
                            <span style="color: #15803d; font-weight: 600;">None (Open to all)</span>
                        <?php endif; ?>
                    </li>
                    <li><strong>Compensation:</strong> <?= format_salary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Candidate Apply Modal Dialog -->
<?php if (is_authenticated() && has_role('candidate') && !$hasApplied && $isEligible): ?>
<div class="modal-overlay" id="applyModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 style="font-size: 1.25rem;">Apply for <?= e($job['title']) ?></h3>
            <button type="button" class="alert-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/candidate/apply" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

            <div class="modal-body">
                <!-- Eligibility Confirmation Box -->
                <?php if ($minCgpa > 0): ?>
                    <div style="background: #f0fdf4; border: 1px solid #86efac; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: 0.82rem; color: #166534;">
                        ✓ <strong>Eligibility Verified:</strong> Your profile CGPA (<strong><?= number_format($candCgpa, 2) ?></strong>) meets the minimum requirement (<strong><?= number_format($minCgpa, 2) ?></strong>).
                    </div>
                <?php endif; ?>

                <!-- Resume Selection -->
                <div class="form-group">
                    <label class="form-label">Resume / Curriculum Vitae <span style="color: #ef4444;">*</span></label>
                    <?php if (!empty($candidateProfile['resume_path'])): ?>
                        <div style="background: var(--slate-50); border: 1px solid var(--slate-200); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; font-weight: 600;">
                                    <span>📄 Saved Profile Resume:</span>
                                    <span style="color: var(--primary);"><?= e($candidateProfile['resume_path']) ?></span>
                                </div>
                                <span class="badge badge-green">Ready</span>
                            </div>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--slate-500); margin-bottom: 0.5rem;">
                            Or upload a tailored resume for this specific position:
                        </div>
                    <?php endif; ?>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" <?= empty($candidateProfile['resume_path']) ? 'required' : '' ?>>
                    <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.25rem;">
                        Accepted formats: PDF, DOC, DOCX (Max 5MB)
                    </div>
                </div>

                <!-- Cover Letter -->
                <div class="form-group">
                    <label class="form-label" for="cover_letter">Cover Letter / Note to Recruiter (Optional)</label>
                    <textarea name="cover_letter" id="cover_letter" class="form-control" rows="5" placeholder="Highlight your relevant experience, key achievements, and why you are interested in this position..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
