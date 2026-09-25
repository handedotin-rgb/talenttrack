<?php
// views/admin/jobs.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Job Posting Moderation</h1>
            <p style="font-size: 0.88rem;">Oversee all job vacancies posted across hiring organizations on <?= APP_NAME ?>.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="role-tab">📈 Platform Analytics</a>
            <a href="<?= BASE_URL ?>/admin/users" class="role-tab">👥 User Accounts</a>
            <a href="<?= BASE_URL ?>/admin/jobs" class="role-tab active">💼 Job Postings (<?= count($jobs) ?>)</a>
            <a href="<?= BASE_URL ?>/admin/categories" class="role-tab">📁 Categories</a>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="role-tab">📜 Audit Trail</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Search Bar -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
        <form action="<?= BASE_URL ?>/admin/jobs" method="GET">
            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label">Search Job Postings</label>
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Job title, company, or location...">
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="closed" <?= ($statusFilter === 'closed') ? 'selected' : '' ?>>Closed</option>
                        <option value="draft" <?= ($statusFilter === 'draft') ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="<?= BASE_URL ?>/admin/jobs" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Jobs Moderation Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Title & Organization</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Applicants</th>
                        <th>Featured</th>
                        <th>Created</th>
                        <th style="text-align: right;">Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; font-size: 0.95rem;">
                                    <a href="<?= BASE_URL ?>/jobs/<?= $j['id'] ?>" target="_blank">
                                        <?= e($j['title']) ?> &#8599;
                                    </a>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 0.2rem;">
                                    🏢 <?= e($j['company_name'] ?: 'Recruiter') ?> &bull; 📍 <?= e($j['location']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= e($j['category_name']) ?></span>
                            </td>
                            <td>
                                <?php if ($j['status'] === 'active'): ?>
                                    <span class="badge badge-green">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-slate"><?= ucfirst(e($j['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= (int)$j['applicants_count'] ?></strong>
                            </td>
                            <td>
                                <?php if ($j['is_featured']): ?>
                                    <span class="badge badge-amber">★ Featured</span>
                                <?php else: ?>
                                    <span style="color: var(--slate-400); font-size: 0.85rem;">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem;"><?= date('M j, Y', strtotime($j['created_at'])) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                    <form method="POST" action="<?= BASE_URL ?>/admin/jobs/<?= $j['id'] ?>/feature" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Toggle Featured">
                                            <?= $j['is_featured'] ? 'Unfeature' : '★ Feature' ?>
                                        </button>
                                    </form>

                                    <a href="<?= BASE_URL ?>/jobs/<?= $j['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
                                        View &#8599;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
