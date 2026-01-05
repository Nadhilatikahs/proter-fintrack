// FinTrack App JS - No Vite Required

// Axios for AJAX requests (via CDN - loaded in layout)
// Axios is loaded from CDN, configure it
document.addEventListener('DOMContentLoaded', function() {
    if (typeof axios !== 'undefined') {
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        // Set CSRF token for all requests
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
    }
});

// Alpine.js will be loaded via CDN
// Sidebar collapse functionality
function initSidebarCollapse() {
    const sidebar = document.querySelector('.fi-sidebar');
    if (!sidebar) return;

    const sidebarHeader = document.querySelector('.fi-sidebar-header');
    if (!sidebarHeader) return;

    let sidebarToggler = document.querySelector('.sidebar-toggler');

    if (!sidebarToggler) {
        const toggler = document.createElement('button');
        toggler.className = 'sidebar-toggler';
        toggler.innerHTML = '<span class="material-symbols-rounded">chevron_left</span>';
        toggler.setAttribute('aria-label', 'Toggle sidebar');
        toggler.setAttribute('type', 'button');
        sidebarHeader.style.position = 'relative';
        sidebarHeader.appendChild(toggler);
        sidebarToggler = toggler;
    }

    if (sidebar && sidebarToggler) {
        sidebarToggler.addEventListener('click', function () {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (wasCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarCollapse);
} else {
    initSidebarCollapse();
}

