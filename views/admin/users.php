<?php
// views/admin/users.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">User & Access Governance</h1>
            <p style="font-size: 0.88rem;">Approve recruiter registrations, manage accounts, and monitor platform members.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="role-tab">📈 Analytics</a>
            <a href="<?= BASE_URL ?>/admin/users" class="role-tab active">
                👥 Users & Approvals (<?= count($users) ?>)
                <?php if (!empty($pendingRecruiters)): ?>
                    <span style="background: #ef4444; color: #fff; border-radius: 999px; padding: 0.1rem 0.45rem; font-size: 0.72rem; margin-left: 0.3rem;">
                        <?= count($pendingRecruiters) ?> pending
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/jobs" class="role-tab">💼 Job Postings</a>
            <a href="<?= BASE_URL ?>/admin/categories" class="role-tab">📁 Categories</a>
            <a href="<?= BASE_URL ?>/admin/audit-logs" class="role-tab">📜 Audit Trail</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Pending Recruiter Approvals Alert & Table -->
    <?php if (!empty($pendingRecruiters)): ?>
        <div class="card" style="border: 2px solid #f59e0b; background: #fffdf5; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #fde68a; padding-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.5rem;">⏳</span>
                    <div>
                        <h3 style="font-size: 1.15rem; color: #92400e; margin-bottom: 0.1rem;">
                            Recruiter Verification & Approvals (<?= count($pendingRecruiters) ?> Pending)
                        </h3>
                        <p style="font-size: 0.82rem; color: #b45309; margin: 0;">
                            These employers have registered and require your verification before they can sign in and publish job listings.
                        </p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" style="background: #ffffff; border-radius: var(--radius-md); overflow: hidden;">
                    <thead>
                        <tr style="background: #fef3c7;">
                            <th>Recruiter Name</th>
                            <th>Company & Designation</th>
                            <th>Contact Info</th>
                            <th>Location</th>
                            <th>Requested</th>
                            <th style="text-align: right;">Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRecruiters as $pr): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--slate-900);"><?= e($pr['name']) ?></div>
                                    <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.72rem;">Recruiter</span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= e($pr['company_name'] ?: 'Not provided') ?></div>
                                    <div style="font-size: 0.8rem; color: var(--slate-500);">
                                        <?= e($pr['headline'] ?: 'Recruiter') ?>
                                        <?php if (!empty($pr['company_website'])): ?>
                                            &bull; <a href="<?= e($pr['company_website']) ?>" target="_blank" rel="noopener" style="color: var(--primary);">Website &nearr;</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;"><?= e($pr['email']) ?></div>
                                    <div style="font-size: 0.78rem; color: var(--slate-500);"><?= e($pr['phone'] ?: 'No phone') ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem;"><?= e($pr['location'] ?: 'Not specified') ?></span>
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; color: var(--slate-600);"><?= time_ago($pr['created_at']) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $pr['id'] ?>/approve" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm" style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.4rem 0.75rem;">
                                                ✓ Approve & Activate
                                            </button>
                                        </form>

                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $pr['id'] ?>/reject" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5; font-weight: 600; padding: 0.4rem 0.75rem;" data-confirm="Decline recruiter registration for <?= e($pr['name']) ?>?">
                                                ✗ Decline
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Search & Filter Bar -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
        <form action="<?= BASE_URL ?>/admin/users" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label">Search Users</label>
                    <input type="text" name="search" value="<?= e($search) ?>" class="form-control" placeholder="Name, email, or company...">
                </div>

                <div>
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="candidate" <?= ($roleFilter === 'candidate') ? 'selected' : '' ?>>Candidate</option>
                        <option value="recruiter" <?= ($roleFilter === 'recruiter') ? 'selected' : '' ?>>Recruiter</option>
                        <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending Approval</option>
                        <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Declined</option>
                        <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactive / Suspended</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User Name & Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Academic / Company Details</th>
                        <th>Activity Summary</th>
                        <th>Registered</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.95rem;"><?= e($u['name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--slate-500);">
                                            <?= e($u['email']) ?>
                                            <?php if (!empty($u['phone'])): ?>
                                                &bull; <?= e($u['phone']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'badge-rose' : ($u['role'] === 'recruiter' ? 'badge-purple' : 'badge-blue') ?>">
                                    <?= ucfirst(e($u['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge badge-green">Active</span>
                                <?php elseif ($u['status'] === 'pending'): ?>
                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">Pending</span>
                                <?php elseif ($u['status'] === 'rejected'): ?>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">Declined</span>
                                <?php else: ?>
                                    <span class="badge badge-rose">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; color: var(--slate-700);">
                                    <?php if ($u['role'] === 'candidate'): ?>
                                        <?php if (!empty($u['cgpa']) || !empty($u['degree'])): ?>
                                            <div><strong>Degree:</strong> <?= e($u['degree'] ?: 'N/A') ?></div>
                                            <div><strong>CGPA:</strong> <?= $u['cgpa'] !== null ? number_format((float)$u['cgpa'], 2) : 'N/A' ?> &bull; <strong>Marks:</strong> <?= $u['marks'] !== null ? number_format((float)$u['marks'], 1) . '%' : 'N/A' ?></div>
                                        <?php else: ?>
                                            <span style="color: var(--slate-400);">Not specified</span>
                                        <?php endif; ?>
                                    <?php elseif ($u['role'] === 'recruiter'): ?>
                                        <div><strong>Company:</strong> <?= e($u['company_name'] ?: 'N/A') ?></div>
                                        <div style="font-size: 0.78rem; color: var(--slate-500);"><?= e($u['headline'] ?: '') ?></div>
                                    <?php else: ?>
                                        <span>System Admin</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; color: var(--slate-600);">
                                    <?php if ($u['role'] === 'recruiter'): ?>
                                        <?= (int)$u['jobs_count'] ?> job listings
                                    <?php elseif ($u['role'] === 'candidate'): ?>
                                        <?= (int)$u['applications_count'] ?> applications
                                    <?php else: ?>
                                        Full System Access
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.4rem; justify-content: flex-end; align-items: center;">
                                    <?php if ($u['role'] === 'recruiter' && $u['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/approve" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm" style="background: #10b981; color: #fff; font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/reject" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5; font-size: 0.75rem; padding: 0.3rem 0.6rem;" data-confirm="Decline recruiter?">
                                                Decline
                                            </button>
                                        </form>
                                    <?php elseif ($u['role'] === 'recruiter' && $u['status'] === 'rejected'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/approve" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm" style="background: #10b981; color: #fff; font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                                Re-Approve
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/toggle" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm" data-confirm="Toggle status for <?= e($u['name']) ?>?">
                                                <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= BASE_URL ?>/admin/users/<?= $u['id'] ?>/reset-password" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-secondary btn-sm" data-confirm="Generate a new temporary password for <?= e($u['name']) ?>?">
                                            🔑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
