<?php
// views/recruiter/applicant_review.php

$stages = RECRUITMENT_STAGES;
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <div style="margin-bottom: 0.25rem;">
                <a href="<?= BASE_URL ?>/recruiter/pipeline" style="font-size: 0.85rem; font-weight: 600;">&larr; Back to Pipeline Board</a>
            </div>
            <h1 style="font-size: 1.6rem;">Candidate Review: <?= e($app['candidate_name']) ?></h1>
            <p style="font-size: 0.88rem;">Application for: <strong><?= e($app['job_title']) ?></strong></p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <?= stage_badge($app['current_stage']) ?>
            <button type="button" class="btn btn-primary btn-sm" data-modal-target="#scheduleInterviewModal">
                📅 Schedule Interview Round
            </button>
        </div>
    </div>
</div>

<div class="container" style="max-width: 1180px;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Left Column: Profile, Resume, Notes & History -->
        <div>
            <!-- Candidate Profile Card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="user-avatar" style="width: 56px; height: 56px; font-size: 1.4rem;">
                            <?= strtoupper(substr($app['candidate_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h2 style="font-size: 1.3rem;"><?= e($app['candidate_name']) ?></h2>
                            <div style="font-size: 0.9rem; color: var(--slate-600);"><?= e($app['candidate_headline'] ?: 'Candidate') ?></div>
                            <div style="font-size: 0.82rem; color: var(--slate-400);">
                                📍 <?= e($app['candidate_location'] ?: 'Location not specified') ?> &bull; <?= (int)$app['experience_years'] ?> years experience
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $app['id'] ?>/resume" class="btn btn-primary btn-sm" target="_blank">
                            📄 Download Resume File
                        </a>
                    </div>
                </div>

                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <span style="color: var(--slate-500); display: block;">Email Address:</span>
                        <a href="mailto:<?= e($app['candidate_email']) ?>"><strong><?= e($app['candidate_email']) ?></strong></a>
                    </div>
                    <?php if (!empty($app['candidate_phone'])): ?>
                        <div>
                            <span style="color: var(--slate-500); display: block;">Phone:</span>
                            <strong><?= e($app['candidate_phone']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span style="color: var(--slate-500); display: block;">Applied On:</span>
                        <strong><?= date('M j, Y', strtotime($app['applied_at'])) ?> (<?= time_ago($app['applied_at']) ?>)</strong>
                    </div>
                </div>

                <?php if (!empty($app['candidate_skills'])): ?>
                    <div style="margin-bottom: 1.25rem;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem;">Skills & Competencies</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            <?php foreach (explode(',', $app['candidate_skills']) as $skill): ?>
                                <span style="background: var(--slate-100); border: 1px solid var(--slate-200); padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">
                                    <?= e(trim($skill)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($app['candidate_bio'])): ?>
                    <div style="margin-bottom: 1.25rem;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 0.4rem;">Candidate Bio</h4>
                        <p style="font-size: 0.9rem; line-height: 1.6; color: var(--slate-700);">
                            <?= e($app['candidate_bio']) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($app['cover_letter'])): ?>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <h4 style="font-size: 0.95rem; margin-bottom: 0.4rem;">Candidate Cover Letter</h4>
                        <div style="background: #fafafa; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1rem; font-size: 0.9rem; line-height: 1.6; color: var(--slate-700); white-space: pre-line;">
                            <?= e($app['cover_letter']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Scheduled Interviews Card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3 style="font-size: 1.15rem;">Interview Rounds (<?= count($interviews) ?>)</h3>
                    <button type="button" class="btn btn-secondary btn-sm" data-modal-target="#scheduleInterviewModal">
                        + Book New Round
                    </button>
                </div>

                <?php if (empty($interviews)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--slate-500); font-size: 0.9rem;">
                        No interview rounds booked for this candidate yet.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($interviews as $i): ?>
                            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.2rem;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div>
                                        <span class="badge badge-purple"><?= ucfirst(e($i['interview_type'])) ?></span>
                                        <h4 style="font-size: 1.05rem; margin-top: 0.25rem;"><?= e($i['title']) ?></h4>
                                    </div>
                                    <span class="badge <?= $i['status'] === 'completed' ? 'badge-green' : 'badge-blue' ?>">
                                        <?= ucfirst(e($i['status'])) ?>
                                    </span>
                                </div>

                                <div style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 0.75rem; display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <div>📅 <?= date('M j, Y \a\t g:i A', strtotime($i['scheduled_at'])) ?></div>
                                    <div>⏱ <?= (int)$i['duration_minutes'] ?> mins</div>
                                    <div>📍 <?= e($i['location'] ?: 'Virtual Call') ?></div>
                                </div>

                                <?php if (!empty($i['meeting_link'])): ?>
                                    <div style="margin-bottom: 0.75rem;">
                                        <a href="<?= e($i['meeting_link']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                            🔗 Open Video Meeting Link
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- Recruiter Interview Evaluation Form -->
                                <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.85rem; margin-top: 0.75rem;">
                                    <form method="POST" action="<?= BASE_URL ?>/recruiter/interviews/<?= $i['id'] ?>/status">
                                        <?= csrf_field() ?>
                                        <div style="font-size: 0.82rem; font-weight: 700; color: var(--slate-700); margin-bottom: 0.5rem;">
                                            Update Round Status & Evaluation:
                                        </div>
                                        <div class="form-row" style="margin-bottom: 0.5rem;">
                                            <div>
                                                <select name="status" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                                    <option value="scheduled" <?= ($i['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                                                    <option value="completed" <?= ($i['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                                                    <option value="rescheduled" <?= ($i['status'] === 'rescheduled') ? 'selected' : '' ?>>Rescheduled</option>
                                                    <option value="cancelled" <?= ($i['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            <div>
                                                <select name="rating" class="form-control" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                                    <option value="">Rating (1-5)</option>
                                                    <option value="5" <?= ($i['rating'] == 5) ? 'selected' : '' ?>>★★★★★ Strong Hire (5)</option>
                                                    <option value="4" <?= ($i['rating'] == 4) ? 'selected' : '' ?>>★★★★☆ Hire (4)</option>
                                                    <option value="3" <?= ($i['rating'] == 3) ? 'selected' : '' ?>>★★★☆☆ Neutral (3)</option>
                                                    <option value="2" <?= ($i['rating'] == 2) ? 'selected' : '' ?>>★★☆☆☆ Lean No (2)</option>
                                                    <option value="1" <?= ($i['rating'] == 1) ? 'selected' : '' ?>>★☆☆☆☆ Strong No (1)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">
                                            <input type="text" name="feedback" value="<?= e($i['feedback']) ?>" class="form-control" style="font-size: 0.85rem;" placeholder="Interviewer evaluation notes & summary...">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Save Evaluation</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Application Progress Log -->
            <div class="card">
                <h3 style="font-size: 1.15rem; margin-bottom: 0.75rem;">Recruitment Audit Trail</h3>
                <div class="timeline">
                    <?php foreach ($history as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-title">
                                <?= ucfirst(e($h['to_stage'])) ?> Stage
                            </div>
                            <div class="timeline-time">
                                <?= date('M j, Y \a\t g:i A', strtotime($h['created_at'])) ?>
                            </div>
                            <?php if (!empty($h['note'])): ?>
                                <div class="timeline-desc"><?= e($h['note']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Stage Transition & Internal Recruiter Notes -->
        <div>
            <div class="card" style="position: sticky; top: 85px;">
                <h3 style="font-size: 1.15rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Advance Recruitment Stage
                </h3>

                <form method="POST" action="<?= BASE_URL ?>/recruiter/applicants/<?= $app['id'] ?>/stage">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="stage">Select Pipeline Stage *</label>
                        <select id="stage" name="stage" class="form-control" style="font-weight: 700; font-size: 1rem; padding: 0.7rem;">
                            <?php foreach ($stages as $key => $s): ?>
                                <option value="<?= $key ?>" <?= ($app['current_stage'] === $key) ? 'selected' : '' ?>>
                                    <?= e($s['label']) ?> (Stage <?= $s['order'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Candidate Score / Evaluation</label>
                        <div class="star-rating-input" style="display: flex; gap: 0.4rem; font-size: 1.6rem; cursor: pointer;">
                            <input type="hidden" name="rating" value="<?= (int)$app['recruiter_rating'] ?>">
                            <?php for ($star = 1; $star <= 5; $star++): 
                                $isFilled = $star <= (int)$app['recruiter_rating'];
                            ?>
                                <button type="button" class="star-btn" style="background: none; border: none; cursor: pointer; font-size: 1.6rem; color: <?= $isFilled ? '#f59e0b' : '#cbd5e1' ?>;">
                                    <?= $isFilled ? '★' : '☆' ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="recruiter_notes">Notes & Feedback (Visible to Candidate)</label>
                        <textarea id="recruiter_notes" name="recruiter_notes" class="form-control" rows="3" placeholder="Provide feedback or next steps for the applicant..."><?= e($app['recruiter_notes']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="note_comment">Internal Stage Transition Note (Audit Log)</label>
                        <input type="text" id="note_comment" name="note_comment" class="form-control" placeholder="e.g. Passed coding assessment, invited to Round 2">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">
                        Update Candidate Stage
                    </button>
                </form>

                <div style="border-top: 1px solid var(--border-color); margin-top: 1.5rem; padding-top: 1.25rem;">
                    <button type="button" class="btn btn-outline btn-block" data-modal-target="#scheduleInterviewModal">
                        📅 Schedule Interview
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog: Schedule Interview -->
<div class="modal-overlay" id="scheduleInterviewModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 style="font-size: 1.2rem;">Book Interview with <?= e($app['candidate_name']) ?></h3>
            <button type="button" class="alert-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/recruiter/interviews/schedule">
            <?= csrf_field() ?>
            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="title">Interview Title / Round *</label>
                    <input type="text" id="title" name="title" class="form-control" value="Round 1: Technical & System Design" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="interview_type">Interview Format</label>
                        <select id="interview_type" name="interview_type" class="form-control">
                            <option value="technical">Technical Assessment</option>
                            <option value="screening">Initial Screening</option>
                            <option value="cultural_fit">Culture & Team Fit</option>
                            <option value="hr">HR & Compensation</option>
                            <option value="final">Final Round / Exec</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">Duration (Minutes)</label>
                        <select id="duration_minutes" name="duration_minutes" class="form-control">
                            <option value="30">30 Minutes</option>
                            <option value="45" selected>45 Minutes</option>
                            <option value="60">60 Minutes</option>
                            <option value="90">90 Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="scheduled_date">Date *</label>
                        <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="scheduled_time">Time *</label>
                        <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" value="14:00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="meeting_link">Virtual Meeting URL</label>
                    <input type="url" id="meeting_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xyz-abcd-efg">
                </div>

                <div class="form-group">
                    <label class="form-label" for="location">Location / Note</label>
                    <input type="text" id="location" name="location" class="form-control" value="Google Meet Virtual Conference Room">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm & Schedule Round</button>
            </div>
        </form>
    </div>
</div>
