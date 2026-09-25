<?php
// views/candidate/dashboard.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Candidate Portal</h1>
            <p style="font-size: 0.88rem;">Track your submitted applications, interview schedules, and profile.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/candidate/dashboard" class="role-tab active">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/candidate/applications" class="role-tab">📄 Applications (<?= $stats['total_apps'] ?>)</a>
            <a href="<?= BASE_URL ?>/candidate/interviews" class="role-tab">📅 Interviews (<?= $stats['interviews'] ?>)</a>
            <a href="<?= BASE_URL ?>/candidate/profile" class="role-tab">👤 Profile & Resume</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- KPI Metrics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📑</div>
            <div>
                <div class="stat-number"><?= (int)$stats['total_apps'] ?></div>
                <div class="stat-label">Total Applied</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔍</div>
            <div>
                <div class="stat-number"><?= (int)$stats['in_review'] ?></div>
                <div class="stat-label">Under Active Review</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎥</div>
            <div>
                <div class="stat-number"><?= (int)$stats['interviews'] ?></div>
                <div class="stat-label">Scheduled Interviews</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎉</div>
            <div>
                <div class="stat-number"><?= (int)$stats['offers'] ?></div>
                <div class="stat-label">Offers & Placements</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.15rem;">Recent Applications</h3>
                    <p style="font-size: 0.82rem;">Real-time status of your latest submissions</p>
                </div>
                <a href="<?= BASE_URL ?>/candidate/applications" class="btn btn-secondary btn-sm">View All</a>
            </div>

            <?php if (empty($recentApps)): ?>
                <div style="text-align: center; padding: 2.5rem 1rem;">
                    <p style="margin-bottom: 1rem;">You haven't submitted any job applications yet.</p>
                    <a href="<?= BASE_URL ?>/jobs" class="btn btn-primary btn-sm">Browse Available Vacancies</a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($recentApps as $app): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 0.2rem;">
                                    <a href="<?= BASE_URL ?>/candidate/applications/<?= $app['id'] ?>">
                                        <?= e($app['job_title']) ?>
                                    </a>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 0.5rem;">
                                    🏢 <?= e($app['company_name'] ?: 'TechCorp') ?> &bull; 📍 <?= e($app['job_location']) ?> &bull; <?= ucfirst(e($app['job_type'])) ?>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--slate-500);">
                                    <span>Applied <?= time_ago($app['applied_at']) ?></span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?= stage_badge($app['current_stage']) ?>
                                <a href="<?= BASE_URL ?>/candidate/applications/<?= $app['id'] ?>" class="btn btn-secondary btn-sm">
                                    Track Progress &rarr;
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
                        <p style="font-size: 0.82rem;">Scheduled rounds & virtual links</p>
                    </div>
                </div>

                <?php if (empty($upcomingInterviews)): ?>
                    <div style="text-align: center; padding: 2rem 1rem; color: var(--slate-500); font-size: 0.9rem;">
                        No pending interviews scheduled at this time.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($upcomingInterviews as $int): ?>
                            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                    <span class="badge badge-purple"><?= ucfirst(e($int['interview_type'])) ?></span>
                                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">
                                        <?= date('M j, g:i A', strtotime($int['scheduled_at'])) ?>
                                    </span>
                                </div>
                                <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.2rem;">
                                    <?= e($int['title']) ?>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--slate-600); margin-bottom: 0.75rem;">
                                    For: <strong><?= e($int['job_title']) ?></strong> (<?= e($int['company_name'] ?: 'Recruiter') ?>)
                                </div>
                                <?php if (!empty($int['meeting_link'])): ?>
                                    <a href="<?= e($int['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm btn-block">
                                        🔗 Join Video Meeting
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Action Card -->
            <div class="card" style="background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%); border-color: #c7d2fe;">
                <h4 style="font-size: 1rem; margin-bottom: 0.4rem; color: var(--primary);">Ready for your next move?</h4>
                <p style="font-size: 0.85rem; margin-bottom: 1rem;">Keep your profile and portfolio up-to-date to stand out to verified recruiters.</p>
                <a href="<?= BASE_URL ?>/candidate/profile" class="btn btn-primary btn-sm btn-block">
                    Update Profile & Resume
                </a>
            </div>
        </div>
    </div>
</div>
