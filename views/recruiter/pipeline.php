<?php
// views/recruiter/pipeline.php

$stages = RECRUITMENT_STAGES;
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Recruitment Pipeline Board</h1>
            <p style="font-size: 0.88rem;">Visual Kanban workflow of all candidates across hiring stages.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/recruiter/dashboard" class="role-tab">📊 Overview</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs" class="role-tab">💼 My Job Postings</a>
            <a href="<?= BASE_URL ?>/recruiter/pipeline" class="role-tab active">🎯 Pipeline Board</a>
            <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="role-tab" style="background: var(--primary); color: #ffffff;">+ Post Job</a>
        </div>
    </div>
</div>

<div class="container" style="max-width: 1440px;">
    <!-- Pipeline Filter Bar -->
    <div class="card" style="padding: 1rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form action="<?= BASE_URL ?>/recruiter/pipeline" method="GET" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <label style="font-weight: 700; font-size: 0.9rem;">Filter by Job Vacancy:</label>
            <select name="job_id" class="form-control" style="width: auto; min-width: 280px;" onchange="this.form.submit()">
                <option value="">All Job Postings (<?= $totalCount ?> candidates)</option>
                <?php foreach ($jobs as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= ($selectedJobId == $j['id']) ? 'selected' : '' ?>>
                        <?= e($j['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selectedJobId): ?>
                <a href="<?= BASE_URL ?>/recruiter/pipeline" class="btn btn-secondary btn-sm">Clear Filter</a>
            <?php endif; ?>
        </form>

        <div style="font-size: 0.88rem; color: var(--slate-600);">
            Total Active Candidates: <strong><?= $totalCount ?></strong>
        </div>
    </div>

    <!-- Kanban Stage Board -->
    <div class="pipeline-board">
        <?php foreach ($stages as $stageKey => $sInfo): 
            $columnCandidates = $pipeline[$stageKey] ?? [];
            $colCount = count($columnCandidates);
        ?>
            <div class="pipeline-column">
                <div class="pipeline-column-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="badge badge-<?= $sInfo['color'] ?>" style="font-size: 0.72rem;">
                            <?= e($sInfo['label']) ?>
                        </span>
                    </div>
                    <span class="pipeline-column-count"><?= $colCount ?></span>
                </div>

                <div class="pipeline-cards">
                    <?php if (empty($columnCandidates)): ?>
                        <div style="text-align: center; padding: 2rem 0.5rem; color: var(--slate-400); font-size: 0.82rem;">
                            No candidates in this stage
                        </div>
                    <?php else: ?>
                        <?php foreach ($columnCandidates as $c): ?>
                            <div class="applicant-card">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                                    <h4 style="font-size: 0.95rem; font-weight: 700;">
                                        <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $c['id'] ?>">
                                            <?= e($c['candidate_name']) ?>
                                        </a>
                                    </h4>
                                    <?php if (!empty($c['recruiter_rating'])): ?>
                                        <span style="color: #f59e0b; font-size: 0.85rem;" title="<?= (int)$c['recruiter_rating'] ?> / 5 stars">
                                            <?= str_repeat('★', (int)$c['recruiter_rating']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div style="font-size: 0.82rem; color: var(--slate-700); font-weight: 600; margin-bottom: 0.3rem;">
                                    <?= e($c['job_title']) ?>
                                </div>

                                <div style="font-size: 0.78rem; color: var(--slate-500); margin-bottom: 0.75rem;">
                                    <?php if (!empty($c['candidate_location'])): ?>
                                        📍 <?= e($c['candidate_location']) ?> &bull;
                                    <?php endif; ?>
                                    <?= (int)$c['experience_years'] ?> yrs exp
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--slate-100); padding-top: 0.6rem;">
                                    <span style="font-size: 0.72rem; color: var(--slate-400);">
                                        <?= time_ago($c['applied_at']) ?>
                                    </span>
                                    <a href="<?= BASE_URL ?>/recruiter/applicants/<?= $c['id'] ?>" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                        Review &rarr;
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
