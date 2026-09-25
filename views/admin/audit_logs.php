<?php
// views/admin/audit_logs.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Recruitment Audit Trail & Logs</h1>
            <p style="font-size: 0.88rem;">Immutable chronological record of recruitment events, stage advances, and logins.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="role-tab">📈 Platform Analytics</a>
            <a href="<?= BASE_URL ?>/admin/users" class="role-tab">👥 User Accounts</a>
            <a href="<?= BASE_URL ?>/admin/jobs" class="role-tab">💼 Job Postings</a>
            <a href="<?= BASE_URL ?>/admin/categories" class="role-tab">📁 Categories</a>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="role-tab active">📜 Audit Trail (<?= count($logs) ?>)</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Audit Filter Bar -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
        <form action="<?= BASE_URL ?>/admin/audit-logs" method="GET">
            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label">Search Log Details</label>
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Search by action, keyword, or user...">
                </div>

                <div>
                    <label class="form-label">Action Event Type</label>
                    <select name="action" class="form-control">
                        <option value="">All Action Types</option>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?= e($act) ?>" <?= ($actionFilter === $act) ? 'selected' : '' ?>>
                                <?= e($act) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Action Event</th>
                        <th>Entity</th>
                        <th>Event Description</th>
                        <th>Triggered By</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td>
                                <span class="badge badge-slate" style="font-family: monospace; font-size: 0.75rem;">
                                    <?= e($l['action']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; font-size: 0.85rem; color: var(--slate-700);">
                                    <?= e($l['entity_type']) ?> #<?= (int)$l['entity_id'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.88rem; color: var(--slate-800); max-width: 400px;">
                                    <?= e($l['details']) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.85rem;">
                                    <?= e($l['user_name'] ?: 'System / Guest') ?>
                                </div>
                                <?php if (!empty($l['user_role'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--slate-500);"><?= ucfirst(e($l['user_role'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.8rem; color: var(--slate-500);"><?= e($l['ip_address']) ?></span>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem;"><?= date('M j, Y \a\t g:i A', strtotime($l['created_at'])) ?></div>
                                <div style="font-size: 0.72rem; color: var(--slate-400);"><?= time_ago($l['created_at']) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
