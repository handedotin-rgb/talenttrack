<?php
// views/admin/categories.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Job Departments & Categories</h1>
            <p style="font-size: 0.88rem;">Organize job postings by technical domain and functional department.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="role-tab">📈 Platform Analytics</a>
            <a href="<?= BASE_URL ?>/admin/users" class="role-tab">👥 User Accounts</a>
            <a href="<?= BASE_URL ?>/admin/jobs" class="role-tab">💼 Job Postings</a>
            <a href="<?= BASE_URL ?>/admin/categories" class="role-tab active">📁 Categories (<?= count($categories) ?>)</a>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="role-tab">📜 Audit Trail</a>
        </div>
    </div>
</div>

<div class="container">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Categories Table -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category Name & Slug</th>
                            <th>Description</th>
                            <th>Associated Jobs</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; font-size: 0.95rem;"><?= e($cat['name']) ?></div>
                                    <div style="font-size: 0.78rem; font-family: monospace; color: var(--slate-500);"><?= e($cat['slug']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--slate-600);"><?= e($cat['description'] ?: '—') ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-blue"><?= (int)$cat['jobs_count'] ?> job<?= $cat['jobs_count'] == 1 ? '' : 's' ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($cat['jobs_count'] == 0): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/categories/<?= $cat['id'] ?>/delete" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm" data-confirm="Delete category '<?= e($cat['name']) ?>'?">
                                                Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 0.75rem; color: var(--slate-400);">Has Active Jobs</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Category Form -->
        <div class="card">
            <h3 style="font-size: 1.15rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                + Add New Category
            </h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/categories">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Category Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Cyber Security & DevOps" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief summary of roles in this department..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Create Category
                </button>
            </form>
        </div>
    </div>
</div>
