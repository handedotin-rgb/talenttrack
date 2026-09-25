<?php
// views/auth/register.php
?>
<div class="card" style="max-width: 680px; width: 100%; padding: 2.25rem;">
    <div style="text-align: center; margin-bottom: 1.75rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; background: var(--primary-light); color: var(--primary); border-radius: 12px; margin-bottom: 0.75rem;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
        </div>
        <h2 style="font-size: 1.6rem; margin-bottom: 0.35rem; color: var(--slate-900);">Create Your Account</h2>
        <p style="font-size: 0.88rem; color: var(--slate-500);">Join <?= APP_NAME ?> to track job applications or build your hiring team</p>
    </div>

    <!-- Required Notice -->
    <div style="background: #f8fafc; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 0.75rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem; color: var(--slate-600);">
        <span><strong style="color: var(--slate-800);">Note:</strong> Fields marked with an asterisk (<span style="color: #ef4444; font-weight: bold;">*</span>) are mandatory.</span>
        <span style="color: var(--slate-400);">All personal details are protected</span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/register" id="registrationForm" onsubmit="return validateRegistrationForm()">
        <?= csrf_field() ?>

        <!-- Role Selector -->
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="font-weight: 700;">Select Account Type <span style="color: #ef4444;">*</span></label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <label style="border: 2px solid var(--primary); padding: 0.85rem; border-radius: var(--radius-md); display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; background: var(--primary-light); transition: all 0.2s;" id="roleLabelCandidate">
                    <input type="radio" name="role" value="candidate" checked onchange="toggleRoleFields('candidate')" style="margin-top: 0.25rem;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--slate-900);">👤 Job Candidate</div>
                        <div style="font-size: 0.78rem; color: var(--slate-600); margin-top: 0.2rem;">Apply for jobs, track interview stages, manage portfolio</div>
                    </div>
                </label>
                <label style="border: 1px solid var(--slate-300); padding: 0.85rem; border-radius: var(--radius-md); display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s;" id="roleLabelRecruiter">
                    <input type="radio" name="role" value="recruiter" onchange="toggleRoleFields('recruiter')" style="margin-top: 0.25rem;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--slate-900);">💼 Recruiter / Employer</div>
                        <div style="font-size: 0.78rem; color: var(--slate-600); margin-top: 0.2rem;">Post jobs, set minimum CGPA, review pipeline</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Recruiter Approval Notice Banner -->
        <div id="recruiterNotice" style="display: none; background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #92400e;">
            <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                <span style="font-size: 1.1rem; line-height: 1;">⏳</span>
                <div>
                    <strong>Admin Verification Required:</strong> Recruiter accounts require administrator approval before login access is activated. You will receive an approval confirmation once an admin validates your company credentials.
                </div>
            </div>
        </div>

        <!-- Section 1: Basic Information -->
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200); padding-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--primary); color: #fff; border-radius: 50%; font-size: 0.75rem;">1</span>
                Basic Account Information
            </h3>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="name">
                        Full Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Johnathan Smith" required minlength="2" maxlength="100">
                    <div class="field-error" id="nameError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="email">
                        Email Address <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="name@domain.com" required autocomplete="email">
                    <div class="field-error" id="emailError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="phone">
                        Mobile Number (India) <span style="color: #ef4444;">*</span>
                    </label>
                    <div style="display: flex; align-items: stretch;">
                        <span style="display: inline-flex; align-items: center; padding: 0.5rem 0.85rem; background: var(--slate-100); border: 1px solid var(--slate-300); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); font-weight: 700; color: var(--slate-700); font-size: 0.95rem; user-select: none;">
                            🇮🇳 +91
                        </span>
                        <input type="tel" id="phone" name="phone" class="form-control" style="border-radius: 0 var(--radius-md) var(--radius-md) 0;" placeholder="9876543210" maxlength="10" pattern="[6-9][0-9]{9}" inputmode="numeric" required>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">10-digit Indian mobile number (e.g. 98765 43210)</div>
                    <div class="field-error" id="phoneError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="location">
                        Location (City, State / Country) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Austin, TX or Mumbai, MH" required minlength="2">
                    <div class="field-error" id="locationError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Section 2: Candidate Academic & Personal Details -->
        <div id="candidateDetailsSection" style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200); padding-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--primary); color: #fff; border-radius: 50%; font-size: 0.75rem;">2</span>
                Academic & Personal Details
            </h3>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="dob">
                        Date of Birth <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="dob" name="dob" class="form-control" max="<?= date('Y-m-d', strtotime('-16 years')) ?>" min="1940-01-01" required>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">Must be at least 16 years of age</div>
                    <div class="field-error" id="dobError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="degree">
                        Highest Qualification / Degree <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="degree" name="degree" class="form-control" required>
                        <option value="">-- Select Degree --</option>
                        <option value="B.Tech / B.E">B.Tech / B.E (Engineering)</option>
                        <option value="B.Sc">B.Sc (Science)</option>
                        <option value="BCA">BCA (Computer Applications)</option>
                        <option value="M.Tech / M.E">M.Tech / M.E</option>
                        <option value="MCA">MCA (Master of Computer Applications)</option>
                        <option value="M.Sc">M.Sc</option>
                        <option value="B.Com">B.Com</option>
                        <option value="BBA">BBA</option>
                        <option value="MBA">MBA</option>
                        <option value="Diploma">Diploma / Associate</option>
                        <option value="Ph.D">Doctorate / Ph.D</option>
                        <option value="Other">Other Degree</option>
                    </select>
                    <div class="field-error" id="degreeError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="institution">
                    College / University / Institution Name <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" id="institution" name="institution" class="form-control" placeholder="e.g. University of California, Berkeley or IIT Delhi" required>
                <div class="field-error" id="institutionError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="cgpa">
                        CGPA (Scale 0.00 – 10.00) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" id="cgpa" name="cgpa" class="form-control" placeholder="e.g. 8.45" step="0.01" min="0" max="10" required>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">Used for matching job eligibility criteria</div>
                    <div class="field-error" id="cgpaError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="marks">
                        Marks / Aggregate Percentage (%) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" id="marks" name="marks" class="form-control" placeholder="e.g. 82.5" step="0.1" min="0" max="100" required>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">Overall percentage marks (0–100%)</div>
                    <div class="field-error" id="marksError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="headline">
                    Professional Headline / Primary Skills
                </label>
                <input type="text" id="headline" name="headline" class="form-control" placeholder="e.g. Full-Stack PHP & React Developer | Cloud & SQL">
                <div style="font-size: 0.75rem; color: var(--slate-500); margin-top: 0.2rem;">Optional summary of your core strengths</div>
            </div>
        </div>

        <!-- Section 3: Recruiter Corporate Details -->
        <div id="recruiterDetailsSection" style="display: none; margin-bottom: 1.5rem; border-bottom: 1px solid var(--slate-200); padding-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--secondary); color: #fff; border-radius: 50%; font-size: 0.75rem;">2</span>
                Company & Recruiter Verification
            </h3>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="company_name">
                        Company / Organization Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="company_name" name="company_name" class="form-control" placeholder="e.g. TechCorp Innovations LLC">
                    <div class="field-error" id="companyError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="company_website">
                        Company Website / Domain
                    </label>
                    <input type="url" id="company_website" name="company_website" class="form-control" placeholder="https://techcorp.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="recruiter_headline">
                    Your Designation / Job Role <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" id="recruiter_headline" name="recruiter_headline" class="form-control" placeholder="e.g. Senior Talent Acquisition Specialist or Head of HR">
                <div class="field-error" id="recruiterHeadlineError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
            </div>
        </div>

        <!-- Section 4: Security / Password -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: #475569; color: #fff; border-radius: 50%; font-size: 0.75rem;" id="step3Number">3</span>
                Account Security
            </h3>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="password">
                        Password (min 6 characters) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
                    <div class="field-error" id="passwordError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label" for="password_confirm">
                        Confirm Password <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
                    <div class="field-error" id="passwordConfirmError" style="color: #ef4444; font-size: 0.78rem; margin-top: 0.25rem; display: none;"></div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem 1rem; font-size: 1rem; font-weight: 700;" id="submitBtn">
            Complete Registration &rarr;
        </button>
    </form>

    <div style="margin-top: 1.75rem; text-align: center; font-size: 0.88rem; color: var(--slate-500); border-top: 1px solid var(--slate-100); padding-top: 1.25rem;">
        Already have an account? 
        <a href="<?= BASE_URL ?>/login" style="font-weight: 600; color: var(--primary);">Sign in to your account</a>
    </div>
</div>

<script>
function toggleRoleFields(role) {
    const candSection = document.getElementById('candidateDetailsSection');
    const recSection = document.getElementById('recruiterDetailsSection');
    const recNotice = document.getElementById('recruiterNotice');
    const labelCand = document.getElementById('roleLabelCandidate');
    const labelRec = document.getElementById('roleLabelRecruiter');

    // Candidate Inputs
    const dob = document.getElementById('dob');
    const degree = document.getElementById('degree');
    const institution = document.getElementById('institution');
    const cgpa = document.getElementById('cgpa');
    const marks = document.getElementById('marks');

    // Recruiter Inputs
    const company = document.getElementById('company_name');
    const recHeadline = document.getElementById('recruiter_headline');

    if (role === 'recruiter') {
        candSection.style.display = 'none';
        recSection.style.display = 'block';
        recNotice.style.display = 'block';

        labelRec.style.border = '2px solid var(--secondary)';
        labelRec.style.background = '#e0f2fe';
        labelCand.style.border = '1px solid var(--slate-300)';
        labelCand.style.background = 'transparent';

        // Remove candidate required flags
        dob.removeAttribute('required');
        degree.removeAttribute('required');
        institution.removeAttribute('required');
        cgpa.removeAttribute('required');
        marks.removeAttribute('required');

        // Add recruiter required flags
        company.setAttribute('required', 'required');
        recHeadline.setAttribute('required', 'required');

        document.getElementById('submitBtn').innerText = 'Submit Recruiter Registration for Approval →';
    } else {
        candSection.style.display = 'block';
        recSection.style.display = 'none';
        recNotice.style.display = 'none';

        labelCand.style.border = '2px solid var(--primary)';
        labelCand.style.background = 'var(--primary-light)';
        labelRec.style.border = '1px solid var(--slate-300)';
        labelRec.style.background = 'transparent';

        // Add candidate required flags
        dob.setAttribute('required', 'required');
        degree.setAttribute('required', 'required');
        institution.setAttribute('required', 'required');
        cgpa.setAttribute('required', 'required');
        marks.setAttribute('required', 'required');

        // Remove recruiter required flags
        company.removeAttribute('required');
        recHeadline.removeAttribute('required');

        document.getElementById('submitBtn').innerText = 'Complete Registration →';
    }
}

function showError(fieldId, errorMsg) {
    const errorEl = document.getElementById(fieldId);
    if (errorEl) {
        errorEl.innerText = errorMsg;
        errorEl.style.display = 'block';
    }
}

function clearErrors() {
    const errors = document.querySelectorAll('.field-error');
    errors.forEach(el => {
        el.innerText = '';
        el.style.display = 'none';
    });
}

function validateRegistrationForm() {
    clearErrors();
    let isValid = true;

    // Full name
    const name = document.getElementById('name').value.trim();
    if (name.length < 2) {
        showError('nameError', 'Please enter your full legal name (min 2 characters).');
        isValid = false;
    }

    // Email
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        showError('emailError', 'Please enter a valid email address (e.g. name@domain.com).');
        isValid = false;
    }

    // Indian Mobile Phone Validation (10 digits, starting with 6, 7, 8, 9)
    const phoneInput = document.getElementById('phone').value.trim();
    let phoneDigits = phoneInput.replace(/[^0-9]/g, '');
    if (phoneDigits.length === 12 && phoneDigits.startsWith('91')) {
        phoneDigits = phoneDigits.substring(2);
    } else if (phoneDigits.length === 11 && phoneDigits.startsWith('0')) {
        phoneDigits = phoneDigits.substring(1);
    }
    const indianPhoneRegex = /^[6-9]\d{9}$/;
    if (!indianPhoneRegex.test(phoneDigits)) {
        showError('phoneError', 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
        isValid = false;
    }

    // Location
    const location = document.getElementById('location').value.trim();
    if (location.length < 2) {
        showError('locationError', 'Please provide your current city and state/country.');
        isValid = false;
    }

    // Role
    const roleEl = document.querySelector('input[name="role"]:checked');
    const role = roleEl ? roleEl.value : 'candidate';

    if (role === 'candidate') {
        // DOB
        const dob = document.getElementById('dob').value;
        if (!dob) {
            showError('dobError', 'Date of birth is required.');
            isValid = false;
        } else {
            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            if (age < 16) {
                showError('dobError', 'You must be at least 16 years of age to register.');
                isValid = false;
            }
        }

        // Degree
        const degree = document.getElementById('degree').value;
        if (!degree) {
            showError('degreeError', 'Please select your highest educational degree.');
            isValid = false;
        }

        // Institution
        const inst = document.getElementById('institution').value.trim();
        if (inst.length < 2) {
            showError('institutionError', 'Please specify your college/university name.');
            isValid = false;
        }

        // CGPA
        const cgpaVal = parseFloat(document.getElementById('cgpa').value);
        if (isNaN(cgpaVal) || cgpaVal < 0 || cgpaVal > 10) {
            showError('cgpaError', 'CGPA must be a decimal value between 0.00 and 10.00.');
            isValid = false;
        }

        // Marks
        const marksVal = parseFloat(document.getElementById('marks').value);
        if (isNaN(marksVal) || marksVal < 0 || marksVal > 100) {
            showError('marksError', 'Marks/Percentage must be between 0.0% and 100.0%.');
            isValid = false;
        }
    } else if (role === 'recruiter') {
        const company = document.getElementById('company_name').value.trim();
        if (company.length < 2) {
            showError('companyError', 'Company / Organization name is required.');
            isValid = false;
        }

        const recHeadline = document.getElementById('recruiter_headline').value.trim();
        if (recHeadline.length < 2) {
            showError('recruiterHeadlineError', 'Your official job designation is required.');
            isValid = false;
        }
    }

    // Password
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    if (password.length < 6) {
        showError('passwordError', 'Password must be at least 6 characters.');
        isValid = false;
    }
    if (password !== passwordConfirm) {
        showError('passwordConfirmError', 'Passwords do not match. Please re-enter.');
        isValid = false;
    }

    return isValid;
}
</script>
