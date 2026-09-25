<?php
// views/recruiter/dashboard.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Recruiter Desk</h1>
            <p style="font-size: 0.88rem;">Manage your active job postings, candidate pipelines, and interview loops.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/recruiter/dashboard" class="role-tab active">📊 Overview</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs" class="role-tab">💼 My Job Postings (<?= $stats['active_jobs'] ?>)</a>
            <a href="<?= BASE_URL ?>/recruiter/pipeline" class="role-tab">🎯 Pipeline Board</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="role-tab" style="background: var(--primary); color: #ffffff;">+ Post Job</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Recruiter KPIs -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💼</div>
            <div>
                <div class="stat-number"><?= (int)$stats['active_jobs'] ?></div>
                <div class="stat-label">Active Job Postings</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-number"><?= (int)$stats['total_applicants'] ?></div>
                <div class="stat-label">Total Applicants</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div>
                <div class="stat-number"><?= (int)$stats['in_screening'] ?></div>
                <div class="stat-label">Pending Screening</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎥</div>
            <div>
                <div class="stat-number"><?= (int)$stats['interviews'] ?></div>
                <div class="stat-label">Scheduled Interviews</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Recent Applicants Queue -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.15rem;">Recent Applicants</h3>
                    <p style="font-size: 0.82rem;">Candidates who applied to your open roles</p>
                </div>
                <a href="<?= BASE_URL ?>/recruiter/pipeline" class="btn btn-secondary btn-sm">Full Pipeline Board &rarr;</a>
            </div>

            <?php if (empty($recentApplicants)): ?>
                <div style="text-align: center; padding: 2.5rem 1rem;">
                    <p style="margin-bottom: 1rem;">No applications received yet for your active postings.</p>
                    <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="btn btn-primary btn-sm">+ Post a New Opportunity</a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($recentApplicants as $app): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($app['candidate_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 1.05rem;">
                                        <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $app['id'] ?>">
                                            <?= e($app['candidate_name']) ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--slate-600);">
                                        Applied for: <strong><?= e($app['job_title']) ?></strong>
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--slate-400); margin-top: 0.2rem;">
                                        <?= e($app['candidate_headline'] ?: 'Candidate') ?> &bull; <?= time_ago($app['applied_at']) ?>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <?= stage_badge($app['current_stage']) ?>
                                <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $app['id'] ?>" class="btn btn-primary btn-sm">
                                    Review & Move &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Interviews Widget -->
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <div>
                        <h3 style="font-size: 1.15rem;">Upcoming Interviews</h3>
                        <p style="font-size: 0.82rem;">Your booked video rounds</p>
                    </div>
                </div>

                <?php if (empty($upcomingInterviews)): ?>
                    <div style="text-align: center; padding: 2rem 1rem; color: var(--slate-500); font-size: 0.88rem;">
                        No pending interviews currently booked.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($upcomingInterviews as $int): ?>
                            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                                    <span class="badge badge-purple"><?= ucfirst(e($int['interview_type'])) ?></span>
                                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">
                                        <?= date('M j, g:i A', strtotime($int['scheduled_at'])) ?>
                                    </span>
                                </div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.2rem;">
                                    <?= e($int['candidate_name']) ?>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--slate-600); margin-bottom: 0.5rem;">
                                    <?= e($int['job_title']) ?>
                                </div>
                                <?php if (!empty($int['meeting_link'])): ?>
                                    <a href="<?= e($int['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm btn-block">
                                        🔗 Launch Meeting
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pipeline Shortcuts -->
            <div class="card" style="background: var(--slate-50);">
                <h4 style="font-size: 1rem; margin-bottom: 0.75rem;">Hiring Actions</h4>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="btn btn-primary btn-block">
                        + Post a New Vacancy
                    </a>
                    <a href="<?= BASE_URL ?>/recruiter/pipeline" class="btn btn-outline btn-block">
                        🎯 Open Pipeline Board
                    </a>
                    <a href="<?= BASE_URL ?>/recruiter/jobs" class="btn btn-secondary btn-block">
                        📋 Manage Active Listings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
