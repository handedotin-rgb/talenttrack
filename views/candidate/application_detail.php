<?php
// views/candidate/application_detail.php

$stages = RECRUITMENT_STAGES;
$currentStageKey = $app['current_stage'];
$currentStageOrder = $stages[$currentStageKey]['order'] ?? 1;
$isRejected = ($currentStageKey === 'rejected');
$isHired = ($currentStageKey === 'hired');
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <div style="margin-bottom: 0.25rem;">
                <a href="<?= BASE_URL ?>/candidate/applications" style="font-size: 0.85rem; font-weight: 600;">&larr; Back to Applications</a>
            </div>
            <h1 style="font-size: 1.6rem;"><?= e($app['job_title']) ?></h1>
            <p style="font-size: 0.88rem;">🏢 <?= e($app['company_name'] ?: 'TechCorp') ?> &bull; 📍 <?= e($app['job_location']) ?></p>
        </div>
        <div>
            <?= stage_badge($currentStageKey) ?>
        </div>
    </div>
</div>

<div class="container" style="max-width: 1100px;">
    <!-- Interactive Recruitment Stage Stepper -->
    <div class="card" style="margin-bottom: 2rem; padding: 2rem 1.5rem;">
        <h3 style="font-size: 1.15rem; margin-bottom: 0.25rem; text-align: center;">Recruitment Pipeline Status</h3>
        <p style="text-align: center; font-size: 0.88rem; color: var(--slate-500); margin-bottom: 1.5rem;">
            Track your candidate progression through our hiring milestones
        </p>

        <div class="recruitment-stepper">
            <?php
            $displayStages = ['applied', 'screening', 'interview', 'assessment', 'offer', 'hired'];
            foreach ($displayStages as $idx => $sKey):
                $sData = $stages[$sKey];
                $stepNumber = $idx + 1;
                $isCompleted = (!$isRejected && $sData['order'] < $currentStageOrder) || $isHired;
                $isCurrent = ($currentStageKey === $sKey);

                $stepClass = '';
                if ($isCompleted) {
                    $stepClass = 'completed';
                } elseif ($isCurrent) {
                    $stepClass = 'current';
                }
            ?>
                <div class="step-item <?= $stepClass ?>">
                    <div class="step-circle">
                        <?php if ($isCompleted): ?>
                            &#10003;
                        <?php else: ?>
                            <?= $stepNumber ?>
                        <?php endif; ?>
                    </div>
                    <div class="step-label"><?= e($sData['label']) ?></div>
                </div>
            <?php endforeach; ?>

            <?php if ($isRejected): ?>
                <div class="step-item rejected">
                    <div class="step-circle">&#10005;</div>
                    <div class="step-label" style="color: var(--danger);">Application Closed</div>
                </div>
            <?php endif; ?>
        </div>

        <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem; text-align: center; margin-top: 1rem;">
            <div style="font-weight: 700; color: var(--slate-800); font-size: 0.95rem;">
                Current Stage: <?= e($stages[$currentStageKey]['label'] ?? ucfirst($currentStageKey)) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--slate-600); margin-top: 0.2rem;">
                <?= e($stages[$currentStageKey]['desc'] ?? '') ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Left Column: Feedback, Interviews & Timeline -->
        <div>
            <!-- Recruiter Notes / Feedback (If visible) -->
            <?php if (!empty($app['recruiter_notes'])): ?>
                <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--primary);">
                    <h4 style="font-size: 1.05rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>💬</span> Recruiter Feedback & Update
                    </h4>
                    <p style="font-size: 0.92rem; color: var(--slate-700); line-height: 1.6; white-space: pre-line;">
                        <?= e($app['recruiter_notes']) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Scheduled Interviews for this application -->
            <?php if (!empty($interviews)): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1.15rem; margin-bottom: 1rem;">Scheduled Interview Rounds</h4>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($interviews as $i): ?>
                            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div>
                                        <span class="badge badge-purple"><?= ucfirst(e($i['interview_type'])) ?></span>
                                        <h5 style="font-size: 1.05rem; margin-top: 0.3rem;"><?= e($i['title']) ?></h5>
                                    </div>
                                    <span class="badge <?= $i['status'] === 'completed' ? 'badge-green' : 'badge-blue' ?>">
                                        <?= ucfirst(e($i['status'])) ?>
                                    </span>
                                </div>

                                <div style="font-size: 0.88rem; color: var(--slate-600); margin-bottom: 0.75rem; display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <div>📅 <strong><?= date('M j, Y \a\t g:i A', strtotime($i['scheduled_at'])) ?></strong></div>
                                    <div>⏱ Duration: <strong><?= (int)$i['duration_minutes'] ?> mins</strong></div>
                                    <div>👤 Interviewer: <strong><?= e($i['recruiter_name']) ?></strong></div>
                                </div>

                                <?php if (!empty($i['meeting_link']) && $i['status'] === 'scheduled'): ?>
                                    <div style="margin-top: 0.75rem;">
                                        <a href="<?= e($i['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm">
                                            🔗 Open Video Meeting
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Application Stage History Timeline -->
            <div class="card">
                <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Application Progress Log</h4>
                <p style="font-size: 0.85rem; color: var(--slate-500); margin-bottom: 1.5rem;">
                    Chronological audit log of status updates and recruiter evaluations.
                </p>

                <div class="timeline">
                    <?php foreach ($history as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-title">
                                <?= e(ucfirst($h['to_stage'])) ?> Stage
                            </div>
                            <div class="timeline-time">
                                <?= date('F j, Y \a\t g:i A', strtotime($h['created_at'])) ?> (<?= time_ago($h['created_at']) ?>)
                            </div>
                            <?php if (!empty($h['note'])): ?>
                                <div class="timeline-desc">
                                    <?= e($h['note']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Submission Details -->
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 1rem;">Submission Details</h4>
                <ul style="list-style: none; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.75rem; color: var(--slate-600); margin-bottom: 1.25rem;">
                    <li><strong>Applied On:</strong> <?= date('M j, Y', strtotime($app['applied_at'])) ?></li>
                    <li><strong>Recruiter:</strong> <?= e($app['recruiter_name']) ?></li>
                    <li><strong>Recruiter Contact:</strong> <?= e($app['recruiter_email']) ?></li>
                    <li>
                        <strong>Resume Submitted:</strong>
                        <div style="margin-top: 0.3rem;">
                            <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $app['id'] ?>/resume" class="btn btn-secondary btn-sm" target="_blank">
                                📄 Download Submitted Resume
                            </a>
                        </div>
                    </li>
                </ul>

                <?php if (!empty($app['cover_letter'])): ?>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <strong style="font-size: 0.88rem;">Cover Letter:</strong>
                        <p style="font-size: 0.85rem; margin-top: 0.4rem; line-height: 1.5; color: var(--slate-600); max-height: 200px; overflow-y: auto;">
                            <?= e($app['cover_letter']) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Withdraw action -->
            <?php if ($app['status'] === 'in_progress'): ?>
                <div class="card" style="border-color: #fecaca; background: #fff5f5;">
                    <h5 style="color: var(--danger); font-size: 0.95rem; margin-bottom: 0.3rem;">Withdraw Application</h5>
                    <p style="font-size: 0.82rem; margin-bottom: 1rem;">
                        If you are no longer interested or have accepted another offer, you can withdraw this application.
                    </p>
                    <form method="POST" action="<?= BASE_URL ?>/candidate/applications/<?= $app['id'] ?>/withdraw">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm btn-block" data-confirm="Are you certain you wish to withdraw your application? This cannot be undone.">
                            Withdraw Application
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
