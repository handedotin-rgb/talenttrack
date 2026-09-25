// assets/js/app.js - TalentTrack Frontend Interactions

document.addEventListener('DOMContentLoaded', () => {
    // Auto-fade flash alerts after 6 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 6000);

    // Modal open buttons: [data-modal-target="#id"]
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = button.getAttribute('data-modal-target');
            const modal = document.querySelector(targetId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Modal close buttons: [data-modal-close]
    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal when clicking outside of dialog
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Confirmation buttons: [data-confirm]
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', (e) => {
            const message = element.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Interactive Star Rating for Recruiter Reviews
    const starContainers = document.querySelectorAll('.star-rating-input');
    starContainers.forEach(container => {
        const input = container.querySelector('input[type="hidden"]');
        const stars = container.querySelectorAll('.star-btn');

        stars.forEach((star, index) => {
            star.addEventListener('click', (e) => {
                e.preventDefault();
                const val = index + 1;
                if (input) input.value = val;
                
                stars.forEach((s, idx) => {
                    if (idx < val) {
                        s.style.color = '#f59e0b';
                        s.innerHTML = '&#9733;';
                    } else {
                        s.style.color = '#cbd5e1';
                        s.innerHTML = '&#9734;';
                    }
                });
            });
        });
    });
});

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
