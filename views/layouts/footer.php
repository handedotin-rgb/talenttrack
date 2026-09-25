<?php
// views/layouts/footer.php
?>
</div> <!-- End .main-wrapper -->

<footer class="footer">
    <div class="footer-container">
        <div style="max-width: 360px;">
            <div class="brand-logo" style="margin-bottom: 0.8rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="6"></circle>
                    <circle cx="12" cy="12" r="2"></circle>
                </svg>
                <span><?= APP_NAME ?></span>
            </div>
            <p style="font-size: 0.88rem; line-height: 1.6;">
                The complete recruitment process tracking platform bridging candidates, talent acquisition specialists, and executive hiring administrators.
            </p>
        </div>

        <div>
            <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem;">Candidates</h4>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.88rem;">
                <li><a href="<?= BASE_URL ?>/jobs">Browse Open Vacancies</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/applications">Application Status Tracker</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/interviews">Interview Preparation</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/profile">Resume & Portfolio</a></li>
            </ul>
        </div>

        <div>
            <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem;">Recruiters</h4>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.88rem;">
                <li><a href="<?= BASE_URL ?>/recruiter/jobs/create">Post a Job</a></li>
                <li><a href="<?= BASE_URL ?>/recruiter/pipeline">Applicant Pipeline Board</a></li>
                <li><a href="<?= BASE_URL ?>/recruiter/jobs">Manage Active Listings</a></li>
                <li><a href="<?= BASE_URL ?>/recruiter/dashboard">Recruiter Dashboard</a></li>
            </ul>
        </div>

        <div>
            <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem;">Company & Help</h4>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.88rem;">
                <li><a href="<?= BASE_URL ?>/jobs">Search Jobs</a></li>
                <li><a href="<?= BASE_URL ?>/login">Account Sign In</a></li>
                <li><a href="<?= BASE_URL ?>/register">Register Account</a></li>
                <li><a href="mailto:support@talenttrack.com">Contact Support</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div>&copy; <?= date('Y') ?> <?= APP_NAME ?> Inc. All rights reserved. Professional Recruitment Tracking Platform.</div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
