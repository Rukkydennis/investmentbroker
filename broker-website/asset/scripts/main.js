document.addEventListener('DOMContentLoaded', () => {
    console.log('Futuristic Brokerage Platform Loaded');

    // Header Scroll Effect
    const header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
                header.style.background = 'rgba(5, 5, 5, 0.9)';
            } else {
                header.classList.remove('scrolled');
                header.style.background = 'transparent';
            }
        });
    }

    // Homepage Mobile Menu Toggle
    const navToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');

            // Toggle icon
            const icon = navToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Mobile Sidebar Toggle (Dashboard)
    const mobileToggle = document.querySelector('.mobile-toggle-sidebar'); // Renamed to avoid conflict if both exist, though unlikely on same page
    const mobileClose = document.querySelector('.mobile-close');
    const sidebar = document.querySelector('.sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }

    // Check if the original mobileToggle selector was meant for sidebar on dashboard pages.
    // If we are on dashboard, .mobile-toggle might be the sidebar toggle.
    // We should differentiate. For now, the homepage uses .mobile-toggle and .nav-links.
    // Dashboard usually uses a different structure. 
    // Let's keep the generic .mobile-toggle for sidebar IF .nav-links is NOT present (dashboard mode)

    const sidebarToggle = document.querySelector('.sidebar-toggle'); // Assuming dashboard uses this or we add it
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }

    if (mobileClose && sidebar) {
        mobileClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    if (sidebar) {
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                // Logic needs to match specific toggle button
                if (!sidebar.contains(e.target) &&
                    !e.target.closest('.mobile-toggle') &&
                    !e.target.closest('.mobile-toggle-sidebar') &&
                    sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
});

// Notification System (Futuristic Modal)
window.showNotification = function (message, type = 'info') {
    // Create container if not exists
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }

    // Create modal
    const modal = document.createElement('div');
    modal.className = `notification-modal ${type}`;

    let iconClass = 'fa-info-circle';
    if (type === 'success') iconClass = 'fa-check-circle';
    if (type === 'error') iconClass = 'fa-exclamation-triangle';

    modal.innerHTML = `
        <div class="notification-content">
            <i class="fas ${iconClass} notification-icon"></i>
            <span>${message}</span>
        </div>
        <i class="fas fa-times notification-close"></i>
    `;

    // Close button logic
    const closeBtn = modal.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        closeModal(modal);
    });

    // Auto dismiss after 8 seconds
    setTimeout(() => {
        if (document.body.contains(modal)) {
            closeModal(modal);
        }
    }, 8000);

    function closeModal(element) {
        element.style.animation = 'fadeOutUp 0.4s ease forwards';
        setTimeout(() => element.remove(), 400);
    }

    container.appendChild(modal);
};

// Confirmation Modal Logic
window.showConfirmation = function (title, message, callback) {
    // Remove existing if any
    const existing = document.querySelector('.confirmation-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.className = 'confirmation-overlay';

    overlay.innerHTML = `
        <div class="confirmation-modal">
            <i class="fas fa-question-circle confirmation-icon"></i>
            <h3 class="confirmation-title">${title}</h3>
            <p class="confirmation-text">${message}</p>
            <div class="confirmation-actions">
                <button class="btn-cancel">Cancel</button>
                <button class="btn-confirm">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Event Listeners
    const confirmBtn = overlay.querySelector('.btn-confirm');
    const cancelBtn = overlay.querySelector('.btn-cancel');

    confirmBtn.addEventListener('click', () => {
        overlay.remove();
        if (callback) callback();
    });

    cancelBtn.addEventListener('click', () => {
        overlay.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => overlay.remove(), 300);
    });

    // Close on outside click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            cancelBtn.click();
        }
    });
};

// Profile Dropdown Toggle
window.toggleProfileDropdown = function () {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
};

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const container = document.querySelector('.profile-dropdown-container');
    const dropdown = document.getElementById('profileDropdown');

    // Check if click is outside container. 
    // And ensure we don't close it if it's currently being hovered (though JS click implies mouse is elsewhere usually).
    // Actually, if we use CSS hover, clicking outside might remove .active, which is fine, but hover still keeps it open if mouse is there.

    if (container && dropdown && !container.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});
