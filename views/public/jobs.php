<?php
// views/public/jobs.php
?>
<div class="container">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem;">Explore Career Opportunities</h1>
        <p>Discover open roles across top technology organizations and fast-growing teams.</p>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.25rem;">
        <form action="<?= BASE_URL ?>/jobs" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label">Search Query</label>
                    <input type="text" name="q" value="<?= e($currentQuery) ?>" class="form-control" placeholder="Job title or keywords...">
                </div>

                <div>
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($currentCat == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Job Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="full-time" <?= ($currentType === 'full-time') ? 'selected' : '' ?>>Full-time</option>
                        <option value="part-time" <?= ($currentType === 'part-time') ? 'selected' : '' ?>>Part-time</option>
                        <option value="contract" <?= ($currentType === 'contract') ? 'selected' : '' ?>>Contract</option>
                        <option value="remote" <?= ($currentType === 'remote') ? 'selected' : '' ?>>Remote</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Experience</label>
                    <select name="exp" class="form-control">
                        <option value="">All Levels</option>
                        <option value="entry" <?= ($currentExp === 'entry') ? 'selected' : '' ?>>Entry Level</option>
                        <option value="mid" <?= ($currentExp === 'mid') ? 'selected' : '' ?>>Mid Level</option>
                        <option value="senior" <?= ($currentExp === 'senior') ? 'selected' : '' ?>>Senior Level</option>
                        <option value="lead" <?= ($currentExp === 'lead') ? 'selected' : '' ?>>Lead / Executive</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Location</label>
                    <input type="text" name="location" value="<?= e($currentLoc) ?>" class="form-control" placeholder="e.g. Austin, Remote">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
                    <a href="<?= BASE_URL ?>/jobs" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div style="font-weight: 700; color: var(--slate-700);">
            Showing <?= count($jobs) ?> open position<?= count($jobs) === 1 ? '' : 's' ?>
        </div>
        <?php if (is_authenticated() && has_role('recruiter')): ?>
            <a href="<?= BASE_URL ?>/recruiter/jobs/create" class="btn btn-primary btn-sm">
                + Post New Job
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
            <h3 style="font-size: 1.3rem; margin-bottom: 0.5rem;">No job listings found</h3>
            <p style="margin-bottom: 1.5rem;">Try adjusting your search criteria or resetting filters.</p>
            <a href="<?= BASE_URL ?>/jobs" class="btn btn-secondary">Clear All Filters</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($jobs as $job): ?>
                <div class="card" style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
                    <div style="flex: 1; min-width: 280px;">
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; flex-wrap: wrap;">
                            <span class="badge badge-blue"><?= e(!empty($job['custom_category']) ? $job['custom_category'] : $job['category_name']) ?></span>
                            <span class="badge badge-slate"><?= ucfirst(e($job['job_type'])) ?></span>
                            <span class="badge badge-purple"><?= ucfirst(e($job['experience_level'])) ?></span>
                            <?php if (!empty($job['min_cgpa']) && (float)$job['min_cgpa'] > 0): ?>
                                <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                    🎯 Min CGPA: <?= number_format((float)$job['min_cgpa'], 2) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($job['is_featured']): ?>
                                <span class="badge badge-amber">★ Featured</span>
                            <?php endif; ?>
                        </div>

                        <h2 style="font-size: 1.25rem; margin-bottom: 0.4rem;">
                            <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>"><?= e($job['title']) ?></a>
                        </h2>

                        <div style="font-size: 0.9rem; color: var(--slate-600); display: flex; gap: 1rem; flex-wrap: wrap;">
                            <span>🏢 <strong><?= e($job['company_name'] ?: 'TechCorp') ?></strong></span>
                            <span>📍 <?= e($job['location']) ?></span>
                            <span>💰 <?= format_salary($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?></span>
                            <span>📅 Posted <?= time_ago($job['created_at']) ?></span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <a href="<?= BASE_URL ?>/jobs/<?= $job['id'] ?>" class="btn btn-primary">
                            View Details & Apply &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
