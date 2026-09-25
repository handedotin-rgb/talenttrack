<?php
// views/candidate/profile.php
?>
<div class="role-header">
    <div class="role-header-content">
        <div>
            <h1 style="font-size: 1.5rem;">Candidate Profile & Resume</h1>
            <p style="font-size: 0.88rem;">Keep your professional bio, skills, and resume updated for 1-click applications.</p>
        </div>
        <div class="role-tabs">
            <a href="<?= BASE_URL ?>/candidate/dashboard" class="role-tab">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/candidate/applications" class="role-tab">📄 Applications</a>
            <a href="<?= BASE_URL ?>/candidate/interviews" class="role-tab">📅 Interviews</a>
            <a href="<?= BASE_URL ?>/candidate/profile" class="role-tab active">👤 Profile & Resume</a>
        </div>
    </div>
</div>

<div class="container" style="max-width: 860px;">
    <div class="card" style="padding: 2rem;">
        <form method="POST" action="<?= BASE_URL ?>/candidate/profile" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.6rem;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <h2 style="font-size: 1.3rem; margin-bottom: 0.2rem;"><?= e($user['name']) ?></h2>
                    <div style="font-size: 0.88rem; color: var(--slate-500);">
                        <?= e($user['email']) ?> &bull; <span class="badge badge-blue"><?= ucfirst(e($user['role'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= e($user['phone']) ?>" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="location">Location (City, Country)</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?= e($user['location']) ?>" placeholder="e.g. San Francisco, CA">
                </div>
                <div class="form-group">
                    <label class="form-label" for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" value="<?= e($user['dob'] ?? '') ?>">
                </div>
            </div>

            <!-- Academic & Educational Details -->
            <div style="background: #f8fafc; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--slate-800); margin-bottom: 0.75rem;">
                    🎓 Academic Records & Qualifications
                </h4>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="degree">Degree / Highest Qualification</label>
                        <input type="text" id="degree" name="degree" class="form-control" value="<?= e($user['degree'] ?? '') ?>" placeholder="e.g. B.Tech Computer Science">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="institution">College / University</label>
                        <input type="text" id="institution" name="institution" class="form-control" value="<?= e($user['institution'] ?? '') ?>" placeholder="e.g. UC Berkeley">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="cgpa">CGPA (Scale 0.00 – 10.00)</label>
                        <input type="number" id="cgpa" name="cgpa" class="form-control" step="0.01" min="0" max="10" value="<?= e($user['cgpa'] ?? '') ?>" placeholder="e.g. 8.50">
                        <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">Used for matching job eligibility criteria</div>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="marks">Marks / Percentage (%)</label>
                        <input type="number" id="marks" name="marks" class="form-control" step="0.1" min="0" max="100" value="<?= e($user['marks'] ?? '') ?>" placeholder="e.g. 85.0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="experience_years">Years of Experience</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" max="50" class="form-control" value="<?= (int)$user['experience_years'] ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="headline">Professional Headline / Title</label>
                <input type="text" id="headline" name="headline" class="form-control" value="<?= e($user['headline']) ?>" placeholder="e.g. Senior Full-Stack Engineer | PHP & React Specialist">
            </div>

            <div class="form-group">
                <label class="form-label" for="skills">Skills & Core Competencies (comma separated)</label>
                <input type="text" id="skills" name="skills" class="form-control" value="<?= e($user['skills']) ?>" placeholder="PHP, MySQL, JavaScript, React, Docker, Git">
                <div style="font-size: 0.78rem; color: var(--slate-400); margin-top: 0.25rem;">
                    Separate each skill tag with a comma.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="bio">Professional Summary / Bio</label>
                <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Briefly describe your background, career trajectory, and what drives you..."><?= e($user['bio']) ?></textarea>
            </div>

            <!-- Resume Document Upload -->
            <div class="form-group" style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1.5rem;">
                <label class="form-label" style="font-size: 1rem; margin-bottom: 0.25rem;">Default Resume / CV Document</label>
                <p style="font-size: 0.82rem; color: var(--slate-500); margin-bottom: 0.75rem;">
                    This resume will be automatically selected when you apply for jobs with 1-click apply.
                </p>

                <?php if (!empty($user['resume_path'])): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid var(--border-color); padding: 0.75rem 1rem; border-radius: var(--radius-md); margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; font-weight: 600;">
                            <span>📄 Current Resume:</span>
                            <span style="color: var(--primary);"><?= e($user['resume_path']) ?></span>
                        </div>
                        <span class="badge badge-green">Uploaded</span>
                    </div>
                <?php endif; ?>

                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                <div style="font-size: 0.75rem; color: var(--slate-400); margin-top: 0.35rem;">
                    Supported formats: PDF, DOC, DOCX (Max size: 5MB)
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-lg">
                    Save Profile & Resume
                </button>
            </div>
        </form>
    </div>
</div>
