<?php
// views/admin/dashboard.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Administrator Command Center</h1>
            <p style="font-size: 0.88rem;">System governance, candidate conversion metrics, and platform operations.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="role-tab active">📈 Platform Analytics</a>
            <a href="<?= BASE_URL ?>/admin/users" class="role-tab">👥 User Accounts</a>
            <a href="<?= BASE_URL ?>/admin/jobs" class="role-tab">💼 Job Postings</a>
            <a href="<?= BASE_URL ?>/admin/categories" class="role-tab">📁 Categories</a>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="role-tab">📜 Audit Trail</a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (!empty($pendingRecruiterCount) && $pendingRecruiterCount > 0): ?>
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">⏳</span>
                <div>
                    <strong style="color: #92400e; font-size: 1rem;">
                        <?= $pendingRecruiterCount ?> Recruiter Account<?= $pendingRecruiterCount > 1 ? 's' : '' ?> Awaiting Verification
                    </strong>
                    <div style="font-size: 0.82rem; color: #b45309;">
                        New employers have submitted registrations and require administrator approval.
                    </div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm" style="background: #d97706; color: #fff; font-weight: 700; text-decoration: none;">
                Review Pending Approvals &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Executive KPI Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">👥</div>
            <div>
                <div class="stat-number"><?= (int)$kpis['total_candidates'] ?></div>
                <div class="stat-label">Registered Candidates</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">🏢</div>
            <div>
                <div class="stat-number"><?= (int)$kpis['total_recruiters'] ?></div>
                <div class="stat-label">Active Recruiters</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">💼</div>
            <div>
                <div class="stat-number"><?= (int)$kpis['active_jobs'] ?> / <?= (int)$kpis['total_jobs'] ?></div>
                <div class="stat-label">Active / Total Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e8ff; color: #7e22ce;">📑</div>
            <div>
                <div class="stat-number"><?= (int)$kpis['total_apps'] ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #dcfce7; color: #15803d;">🎉</div>
            <div>
                <div class="stat-number"><?= (int)$kpis['total_hires'] ?></div>
                <div class="stat-label">Completed Hires</div>
            </div>
        </div>
    </div>

    <!-- Recruitment Conversion Funnel Breakdown -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;">Recruitment Pipeline Distribution</h3>
        <p style="font-size: 0.88rem; color: var(--slate-500); margin-bottom: 1.5rem;">
            Real-time candidate count across each stage of the hiring lifecycle
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem;">
            <?php foreach (RECRUITMENT_STAGES as $stageKey => $sInfo): 
                $count = $stageCounts[$stageKey] ?? 0;
            ?>
                <div style="background: var(--slate-50); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem 1rem; text-align: center;">
                    <span class="badge badge-<?= $sInfo['color'] ?>" style="margin-bottom: 0.5rem;">
                        <?= e($sInfo['label']) ?>
                    </span>
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--slate-900);">
                        <?= $count ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--slate-500);">candidates</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Recent Audit Log Activity -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 1.15rem;">Recent Platform Events</h3>
                <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-secondary btn-sm">Full Audit Trail &rarr;</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($recentLogs as $log): ?>
                    <div style="border-bottom: 1px solid var(--slate-100); padding-bottom: 0.75rem; font-size: 0.88rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.2rem;">
                            <span class="badge badge-slate" style="font-family: monospace; font-size: 0.72rem;">
                                <?= e($log['action']) ?>
                            </span>
                            <span style="font-size: 0.75rem; color: var(--slate-400);">
                                <?= time_ago($log['created_at']) ?>
                            </span>
                        </div>
                        <div style="font-weight: 600; color: var(--slate-800);">
                            <?= e($log['details'] ?: 'Action executed') ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.15rem;">
                            By: <strong><?= e($log['user_name'] ?: 'System') ?></strong> &bull; IP: <?= e($log['ip_address']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Registered Users -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 1.15rem;">Recent Platform Users</h3>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary btn-sm">Manage Users &rarr;</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($recentUsers as $u): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--slate-100); padding-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="user-avatar" style="width: 38px; height: 38px;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.95rem;"><?= e($u['name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--slate-500);">
                                    <?= e($u['email']) ?>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-rose' : ($u['role'] === 'recruiter' ? 'badge-purple' : 'badge-blue') ?>">
                                <?= ucfirst(e($u['role'])) ?>
                            </span>
                            <div style="font-size: 0.72rem; color: var(--slate-400); margin-top: 0.2rem;">
                                <?= date('M j, Y', strtotime($u['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
