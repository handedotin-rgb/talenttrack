<?php
// views/recruiter/job_create.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <div style="margin-bottom: 0.25rem;">
                <a href="<?= BASE_URL ?>/recruiter/jobs" style="font-size: 0.85rem; font-weight: 600;">&larr; Back to My Jobs</a>
            </div>
            <h1 style="font-size: 1.5rem;">Create New Job Vacancy</h1>
            <p style="font-size: 0.88rem;">Publish an open role with customizable categories and candidate qualification criteria.</p>
        </div>
    </div>
</div>

<div class="container" style="max-width: 900px;">
    <div class="card" style="padding: 2rem;">
        <form method="POST" action="<?= BASE_URL ?>/recruiter/jobs/create" id="jobCreateForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="title">Job Title <span style="color: #ef4444;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Senior Full-Stack Engineer" required>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="category_id">Department / Category <span style="color: #ef4444;">*</span></label>
                    <select id="category_id" name="category_id" class="form-control" required onchange="toggleCustomCategory(this)">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-name="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="job_type">Employment Type</label>
                    <select id="job_type" name="job_type" class="form-control">
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="contract">Contract</option>
                        <option value="remote">Remote</option>
                        <option value="internship">Internship</option>
                    </select>
                </div>
            </div>

            <!-- Custom Category Input (shown when 'Others' selected) -->
            <div class="form-group" id="customCategoryGroup" style="display: none; background: #f8fafc; border: 1px solid var(--slate-200); padding: 1rem; border-radius: var(--radius-md);">
                <label class="form-label" for="custom_category">
                    Specify Custom Category Name <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" id="custom_category" name="custom_category" class="form-control" placeholder="e.g. Artificial Intelligence Research or Product Marketing">
                <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.25rem;">
                    This custom category will be displayed prominently on the job listing.
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="experience_level">Seniority Level</label>
                    <select id="experience_level" name="experience_level" class="form-control">
                        <option value="entry">Entry Level</option>
                        <option value="mid" selected>Mid Level</option>
                        <option value="senior">Senior Level</option>
                        <option value="lead">Lead / Principal / Executive</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="location">Job Location (City / Remote) <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Austin, TX (Hybrid) or Remote" required>
                </div>
            </div>

            <!-- Minimum CGPA Filtering Criterion -->
            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem;">🎯</span>
                    <label class="form-label" for="min_cgpa" style="margin-bottom: 0; font-weight: 700; color: #166534;">
                        Minimum Required Candidate CGPA (Scale 0.00 – 10.00)
                    </label>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <input type="number" id="min_cgpa" name="min_cgpa" class="form-control" style="max-width: 200px; background: #fff; font-weight: 700;" placeholder="0.00" step="0.01" min="0" max="10" value="0.00">
                    <span style="font-size: 0.85rem; color: #15803d; line-height: 1.4;">
                        Candidates with a CGPA below this score will be <strong>automatically blocked</strong> from applying. Enter <strong>0.00</strong> if there is no minimum threshold.
                    </span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="salary_min">Minimum Salary</label>
                    <input type="number" id="salary_min" name="salary_min" class="form-control" placeholder="e.g. 100000" step="1000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="salary_max">Maximum Salary</label>
                    <input type="number" id="salary_max" name="salary_max" class="form-control" placeholder="e.g. 140000" step="1000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="salary_currency">Currency</label>
                    <select id="salary_currency" name="salary_currency" class="form-control">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="INR">INR (₹)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Job Description & Responsibilities <span style="color: #ef4444;">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="6" placeholder="Detail the day-to-day responsibilities, mission, and objectives of this role..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="requirements">Qualifications & Requirements <span style="color: #ef4444;">*</span></label>
                <textarea id="requirements" name="requirements" class="form-control" rows="5" placeholder="Key skills, technical requirements, educational background, or certifications..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="benefits">Benefits & Perks</label>
                <textarea id="benefits" name="benefits" class="form-control" rows="3" placeholder="Health insurance, remote stipends, 401(k), learning budgets..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="deadline">Application Deadline (Optional)</label>
                    <input type="date" id="deadline" name="deadline" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Posting Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" selected>Active & Published</option>
                        <option value="draft">Save as Draft</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= BASE_URL ?>/recruiter/jobs" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">Publish Job Posting</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCustomCategory(select) {
    const selectedOption = select.options[select.selectedIndex];
    const catName = selectedOption.getAttribute('data-name') || '';
    const customGroup = document.getElementById('customCategoryGroup');
    const customInput = document.getElementById('custom_category');

    if (catName.toLowerCase().includes('other')) {
        customGroup.style.display = 'block';
        customInput.setAttribute('required', 'required');
    } else {
        customGroup.style.display = 'none';
        customInput.removeAttribute('required');
    }
}
</script>
