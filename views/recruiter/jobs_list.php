<?php
// views/recruiter/jobs_list.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">My Job Postings</h1>
            <p style="font-size: 0.88rem;">Manage your active vacancies, view applicants per job, and update job status.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/recruiter/dashboard" class="role-tab">📊 Overview</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs" class="role-tab active">💼 My Job Postings (<?= count($jobs) ?>)</a>
            <a href="<?= BASE_URL ?>/recruiter/pipeline" class="role-tab">🎯 Pipeline Board</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="role-tab" style="background: var(--primary); color: #ffffff;">+ Post Job</a>
        </div>
    </div>
</div>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div style="font-weight: 700; color: var(--slate-700);">
            Total: <?= count($jobs) ?> Job Vacanc<?= count($jobs) === 1 ? 'y' : 'ies' ?>
        </div>
        <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="btn btn-primary">
            + Post New Vacancy
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💼</div>
            <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">No job postings created yet</h3>
            <p style="margin-bottom: 1.5rem;">Create your first opening to start receiving and tracking candidates.</p>
            <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="btn btn-primary">+ Create Job Posting</a>
        </div>
    <?php else: ?>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title & Category</th>
                            <th>Status</th>
                            <th>Applicants</th>
                            <th>In Interview</th>
                            <th>Posted Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; font-size: 1rem;">
                                        <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" target="_blank">
                                            <?= e($job['title']) ?> &#8599;
                                        </a>
                                    </div>
                                    <div style="font-size: 0.82rem; color: var(--slate-500); margin-top: 0.2rem;">
                                        <?= e($job['category_name']) ?> &bull; <?= ucfirst(e($job['job_type'])) ?> &bull; <?= e($job['location']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($job['status'] === 'active'): ?>
                                        <span class="badge badge-green">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-slate">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/recruiter/pipeline?job_id=<?= $job['id'] ?>" style="font-weight: 700; font-size: 1.05rem;">
                                        <?= (int)$job['applicants_count'] ?> applicants
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-purple">
                                        <?= (int)$job['interview_count'] ?> interviewing
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.88rem;"><?= date('M j, Y', strtotime($job['created_at'])) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--slate-400);"><?= time_ago($job['created_at']) ?></div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                        <a href="<?= BASE_URL ?>/recruiter/pipeline?job_id=<?= $job['id'] ?>" class="btn btn-primary btn-sm" title="View Pipeline">
                                            🎯 Pipeline
                                        </a>
                                        <a href="<?= BASE_URL ?>/recruiter/jobs/<?= $job['id'] ?>/edit" class="btn btn-secondary btn-sm" title="Edit Job">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/recruiter/jobs/<?= $job['id'] ?>/toggle" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm">
                                                <?= $job['status'] === 'active' ? 'Close' : 'Reopen' ?>
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
</div>
