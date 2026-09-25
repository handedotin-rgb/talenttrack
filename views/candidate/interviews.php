<?php
// views/candidate/interviews.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Scheduled Interviews</h1>
            <p style="font-size: 0.88rem;">Manage your upcoming interview rounds, virtual video links, and meeting schedules.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/candidate/dashboard" class="role-tab">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/candidate/applications" class="role-tab">📄 Applications</a>
            <a href="<?= BASE_URL ?>/candidate/interviews" class="role-tab active">📅 Interviews (<?= count($interviews) ?>)</a>
            <a href="<?= BASE_URL ?>/candidate/profile" class="role-tab">👤 Profile & Resume</a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (empty($interviews)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">No interviews scheduled yet</h3>
            <p style="margin-bottom: 1.5rem;">Once a hiring team reviews your application and books an interview round, it will appear here.</p>
            <a href="<?= BASE_URL ?>/candidate/applications" class="btn btn-secondary">Check Application Status</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?php foreach ($interviews as $i): ?>
                <div class="card" style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.3rem;">
                                <span class="badge badge-purple"><?= ucfirst(e($i['interview_type'])) ?> Round</span>
                                <span class="badge <?= $i['status'] === 'completed' ? 'badge-green' : 'badge-blue' ?>">
                                    <?= ucfirst(e($i['status'])) ?>
                                </span>
                            </div>
                            <h2 style="font-size: 1.3rem; margin-bottom: 0.25rem;"><?= e($i['title']) ?></h2>
                            <div style="font-size: 0.95rem; color: var(--slate-700);">
                                Position: <strong><?= e($i['job_title']) ?></strong> &bull; <?= e($i['company_name'] ?: 'Recruiter') ?>
                            </div>
                        </div>

                        <?php if ($i['status'] === 'scheduled' && !empty($i['meeting_link'])): ?>
                            <a href="<?= e($i['meeting_link']) ?>" target="_blank" class="btn btn-primary">
                                🔗 Join Virtual Video Call
                            </a>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem; font-size: 0.88rem;">
                        <div>
                            <span style="color: var(--slate-500); display: block;">Date & Time:</span>
                            <strong style="font-size: 0.95rem; color: var(--slate-900);">
                                📅 <?= date('l, F j, Y \a\t g:i A', strtotime($i['scheduled_at'])) ?>
                            </strong>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); display: block;">Expected Duration:</span>
                            <strong>⏱ <?= (int)$i['duration_minutes'] ?> Minutes</strong>
                        </div>
                        <div>
                            <span style="color: var(--slate-500); display: block;">Interviewer:</span>
                            <strong>👤 <?= e($i['recruiter_name']) ?></strong> (<?= e($i['recruiter_email']) ?>)
                        </div>
                        <div>
                            <span style="color: var(--slate-500); display: block;">Location / Format:</span>
                            <strong>📍 <?= e($i['location'] ?: 'Virtual Meeting') ?></strong>
                        </div>
                    </div>

                    <?php if (!empty($i['feedback'])): ?>
                        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.88rem;">
                            <strong>Post-Interview Notes:</strong>
                            <p style="margin-top: 0.25rem; color: var(--slate-700);"><?= e($i['feedback']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                        <a href="<?= BASE_URL ?>/candidate/applications/<?= $i['application_id'] ?>" class="btn btn-secondary btn-sm">
                            View Full Application & Stage &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
