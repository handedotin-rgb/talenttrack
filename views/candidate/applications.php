<?php
// views/candidate/applications.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">My Job Applications</h1>
            <p style="font-size: 0.88rem;">Review the active status and history of all submitted applications.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/candidate/dashboard" class="role-tab">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/candidate/applications" class="role-tab active">📄 Applications (<?= count($applications) ?>)</a>
            <a href="<?= BASE_URL ?>/candidate/interviews" class="role-tab">📅 Interviews</a>
            <a href="<?= BASE_URL ?>/candidate/profile" class="role-tab">👤 Profile & Resume</a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (empty($applications)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">No applications submitted yet</h3>
            <p style="margin-bottom: 1.5rem;">Browse open vacancies and start applying to launch your recruitment journey.</p>
            <a href="<?= BASE_URL ?>/jobs" class="btn btn-primary">Browse All Open Roles</a>
        </div>
    <?php else: ?>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Role & Company</th>
                            <th>Current Stage</th>
                            <th>Applied Date</th>
                            <th>Recruiter</th>
                            <th>Overall Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; font-size: 1rem;">
                                        <a href="<?= BASE_URL ?>/candidate/applications/<?= $app['id'] ?>">
                                            <?= e($app['job_title']) ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.82rem; color: var(--slate-500); margin-top: 0.2rem;">
                                        🏢 <?= e($app['company_name'] ?: 'TechCorp') ?> &bull; 📍 <?= e($app['job_location']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= stage_badge($app['current_stage']) ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.88rem;"><?= date('M j, Y', strtotime($app['applied_at'])) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--slate-400);"><?= time_ago($app['applied_at']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.88rem;"><?= e($app['recruiter_name']) ?></div>
                                </td>
                                <td>
                                    <?php if ($app['status'] === 'withdrawn'): ?>
                                        <span class="badge badge-slate">Withdrawn</span>
                                    <?php elseif ($app['current_stage'] === 'hired'): ?>
                                        <span class="badge badge-green">Hired</span>
                                    <?php elseif ($app['current_stage'] === 'rejected'): ?>
                                        <span class="badge badge-rose">Closed</span>
                                    <?php else: ?>
                                        <span class="badge badge-blue">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/candidate/applications/<?= $app['id'] ?>" class="btn btn-primary btn-sm">
                                        Track Stage &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
