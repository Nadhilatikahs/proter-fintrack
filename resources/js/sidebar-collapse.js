// Sidebar collapse functionality - matching sidebar-menu design
function initSidebarCollapse() {
    const sidebar = document.querySelector('.fi-sidebar');
    if (!sidebar) return;

    const sidebarHeader = document.querySelector('.fi-sidebar-header');
    if (!sidebarHeader) return;

    let sidebarToggler = document.querySelector('.sidebar-toggler');

    // Create collapse button if it doesn't exist
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

    // Add tooltips to navigation items (matching sidebar-menu design)
    if (sidebar) {
        const navItems = sidebar.querySelectorAll('.fi-sidebar-nav-item');
        navItems.forEach(function (item) {
            // Check if tooltip already exists
            if (!item.querySelector('.nav-tooltip')) {
                const link = item.querySelector('a');
                if (link) {
                    // Get the label text - try multiple ways to get the text
                    let label = '';
                    const labelSpan = link.querySelector('.fi-sidebar-nav-item-label, span:not(.fi-sidebar-nav-item-icon):not(svg)');
                    if (labelSpan) {
                        label = labelSpan.textContent.trim();
                    } else {
                        label = link.textContent.trim();
                        // Remove icon text if present
                        const icon = link.querySelector('.fi-sidebar-nav-item-icon, svg');
                        if (icon) {
                            label = label.replace(icon.textContent || '', '').trim();
                        }
                    }

                    if (label) {
                        const tooltip = document.createElement('span');
                        tooltip.className = 'nav-tooltip';
                        tooltip.textContent = label;
                        item.appendChild(tooltip);
                    }
                }
            }
        });
    }

    // Function to adjust main content margin and width
    function adjustMainContent(isCollapsed) {
        const mainContent = document.querySelector('.fi-main');
        if (mainContent) {
            if (isCollapsed) {
                mainContent.style.marginLeft = '117px'; // 85px + 32px margins
                mainContent.style.width = 'calc(100% - 117px)';
            } else {
                mainContent.style.marginLeft = '302px'; // 270px + 32px margins
                mainContent.style.width = 'calc(100% - 302px)';
            }
        }
    }

    // Toggle sidebar's collapsed state
    if (sidebar && sidebarToggler) {
        sidebarToggler.addEventListener('click', function () {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            adjustMainContent(isCollapsed);
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });

        // Restore state from localStorage
        const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (wasCollapsed) {
            sidebar.classList.add('collapsed');
            adjustMainContent(true);
        } else {
            adjustMainContent(false);
        }
    }

    // Handle window resize (matching sidebar-menu script.js)
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            // On larger screens, maintain collapse state
            // Sidebar height is already set via CSS
        } else {
            // On mobile, remove collapsed class
            if (sidebar) {
                sidebar.classList.remove('collapsed');
                adjustMainContent(false);
            }
        }
    });
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarCollapse);
} else {
    initSidebarCollapse();
}

// Also initialize after Livewire updates (for Filament)
if (window.Livewire) {
    document.addEventListener('livewire:load', initSidebarCollapse);
    document.addEventListener('livewire:update', initSidebarCollapse);
}

// Use MutationObserver to handle dynamic content
const observer = new MutationObserver(function (mutations) {
    const sidebar = document.querySelector('.fi-sidebar');
    const sidebarHeader = document.querySelector('.fi-sidebar-header');
    const sidebarToggler = document.querySelector('.sidebar-toggler');

    if (sidebar && sidebarHeader && !sidebarToggler) {
        initSidebarCollapse();
    }
});

// Start observing
if (document.body) {
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}
