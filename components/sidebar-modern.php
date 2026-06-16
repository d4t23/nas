<?php
if (!isset($auth)) {
    return;
}

$user = $auth->user();
$activeFolder = $_GET['folder_id'] ?? null;
$page = $_GET['page'] ?? null;
$currentPath = basename($_SERVER['PHP_SELF'], '.php');

// Determine active menu item
$activeMenu = 'dashboard';
if ($currentPath === 'folder') $activeMenu = 'folders';
elseif ($currentPath === 'shared') $activeMenu = 'shared-files';
elseif ($currentPath === 'recent') $activeMenu = 'recent';
elseif ($currentPath === 'settings') $activeMenu = 'settings';
elseif ($currentPath === 'connected_accounts') $activeMenu = 'users';
elseif ($currentPath === 'backups') $activeMenu = 'reports';
elseif ($currentPath === 'computers') $activeMenu = 'devices';
?>

<aside class="sidebar" id="modernSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="dashboard_new.php" class="sidebar-logo">
            <img src="../assets/images/logo.png" alt="GoCloud">
            <span>GoCloud</span>
        </a>
        <button class="sidebar-toggle" id="sidebarToggleBtn" aria-label="Toggle sidebar" title="Toggle sidebar">
            <i class='bx bx-chevron-left'></i>
        </button>
    </div>

    <!-- Main Navigation Menu -->
    <nav class="sidebar-menu">
        <!-- Main Section -->
        <li class="sidebar-menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
            <a href="dashboard_new.php" class="sidebar-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <i class='bx bx-grid-alt'></i>
                <span class="sidebar-link-label">Dashboard</span>
                <span class="sidebar-tooltip">Dashboard</span>
            </a>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'files' ? 'active' : '' ?>">
            <a href="dashboard_new.php" class="sidebar-link <?= $activeMenu === 'files' ? 'active' : '' ?>">
                <i class='bx bx-file'></i>
                <span class="sidebar-link-label">Files</span>
                <span class="sidebar-tooltip">Files</span>
            </a>
        </li>

        <li class="sidebar-menu-item has-submenu <?= $activeMenu === 'folders' ? 'open' : '' ?>">
            <a href="javascript:void(0)" class="sidebar-link" data-toggle-submenu="folders-submenu">
                <i class='bx bx-folder'></i>
                <span class="sidebar-link-label">Folders</span>
                <span class="sidebar-submenu"><i class='bx bx-chevron-down'></i></span>
                <span class="sidebar-tooltip">Folders</span>
            </a>
            <ul class="sidebar-submenu-list" id="folders-submenu">
                <li class="sidebar-submenu-item">
                    <a href="dashboard_new.php" class="sidebar-submenu-link <?= $activeMenu === 'folders' && !$activeFolder ? 'active' : '' ?>">
                        <i class='bx bx-folder-open'></i>
                        My Go
                    </a>
                </li>
                <li class="sidebar-submenu-item">
                    <a href="javascript:void(0)" class="sidebar-submenu-link" onclick="newFolder()">
                        <i class='bx bx-folder-plus'></i>
                        New Folder
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'shared-files' ? 'active' : '' ?>">
            <a href="shared.php" class="sidebar-link <?= $activeMenu === 'shared-files' ? 'active' : '' ?>">
                <i class='bx bx-share-alt'></i>
                <span class="sidebar-link-label">Shared Files</span>
                <span class="sidebar-badge">2</span>
                <span class="sidebar-tooltip">Shared Files</span>
            </a>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'recent' ? 'active' : '' ?>">
            <a href="recent.php" class="sidebar-link <?= $activeMenu === 'recent' ? 'active' : '' ?>">
                <i class='bx bx-time-five'></i>
                <span class="sidebar-link-label">Recent</span>
                <span class="sidebar-tooltip">Recent Activity</span>
            </a>
        </li>

        <!-- Divider -->
        <li style="list-style: none; margin: 0;">
            <hr class="sidebar-divider">
        </li>

        <!-- Management Section -->
        <li style="list-style: none;">
            <span class="sidebar-section-label">Management</span>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'users' ? 'active' : '' ?>">
            <a href="connected_accounts.php" class="sidebar-link <?= $activeMenu === 'users' ? 'active' : '' ?>">
                <i class='bx bx-user'></i>
                <span class="sidebar-link-label">Users</span>
                <span class="sidebar-tooltip">Users</span>
            </a>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'devices' ? 'active' : '' ?>">
            <a href="computers.php" class="sidebar-link <?= $activeMenu === 'devices' ? 'active' : '' ?>">
                <i class='bx bx-desktop'></i>
                <span class="sidebar-link-label">Devices</span>
                <span class="sidebar-tooltip">Devices</span>
            </a>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'reports' ? 'active' : '' ?>">
            <a href="backups.php" class="sidebar-link <?= $activeMenu === 'reports' ? 'active' : '' ?>">
                <i class='bx bx-bar-chart'></i>
                <span class="sidebar-link-label">Backups</span>
                <span class="sidebar-tooltip">Backups</span>
            </a>
        </li>

        <!-- Divider -->
        <li style="list-style: none; margin: 0;">
            <hr class="sidebar-divider">
        </li>

        <!-- System Section -->
        <li style="list-style: none;">
            <span class="sidebar-section-label">System</span>
        </li>

        <li class="sidebar-menu-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
            <a href="settings.php" class="sidebar-link <?= $activeMenu === 'settings' ? 'active' : '' ?>">
                <i class='bx bx-cog'></i>
                <span class="sidebar-link-label">Settings</span>
                <span class="sidebar-tooltip">Settings</span>
            </a>
        </li>

        <li class="sidebar-menu-item">
            <a href="javascript:void(0)" class="sidebar-link" onclick="openHelpCenter()">
                <i class='bx bx-help-circle'></i>
                <span class="sidebar-link-label">Help Center</span>
                <span class="sidebar-tooltip">Help Center</span>
            </a>
        </li>

        <li class="sidebar-menu-item">
            <a href="../public/logout.php" class="sidebar-link" onclick="return confirm('Are you sure you want to logout?')">
                <i class='bx bx-log-out'></i>
                <span class="sidebar-link-label">Logout</span>
                <span class="sidebar-tooltip">Logout</span>
            </a>
        </li>
    </nav>

    <!-- User Profile Section -->
    <div class="sidebar-user">
        <div class="sidebar-user-profile" id="userProfileBtn" title="User menu">
            <div class="sidebar-user-avatar">
                <?php if ($user['profile_picture']): ?>
                    <img src="../storage/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
                <?php else: ?>
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="sidebar-user-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar JavaScript -->
<script>
    /**
     * Modern Sidebar Controller
     * Handles toggle, submenu, theme, and responsive behavior
     */
    class ModernSidebar {
        constructor() {
            this.sidebar = document.getElementById('modernSidebar');
            this.toggleBtn = document.getElementById('sidebarToggleBtn');
            this.userProfileBtn = document.getElementById('userProfileBtn');
            this.isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            this.isDarkMode = localStorage.getItem('darkMode') === 'true' || 
                             window.matchMedia('(prefers-color-scheme: dark)').matches;

            this.init();
        }

        init() {
            this.setupTheme();
            this.setupToggle();
            this.setupSubmenus();
            this.createFloatingButton();
            this.setupResponsive();
            this.restoreState();
        }

        setupTheme() {
            if (this.isDarkMode) {
                document.body.classList.add('dark-mode');
            }

            // Listen for theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                this.isDarkMode = e.matches;
                document.body.classList.toggle('dark-mode', this.isDarkMode);
                localStorage.setItem('darkMode', this.isDarkMode);
            });
        }

        setupToggle() {
            this.toggleBtn?.addEventListener('click', () => this.toggleSidebar());
            document.addEventListener('click', (e) => {
                if (!this.sidebar.contains(e.target) && 
                    !this.toggleBtn.contains(e.target) &&
                    window.innerWidth < 768) {
                    this.closeSidebar();
                }
            });
        }

        toggleSidebar() {
            if (window.innerWidth <= 768) {
                const isOpen = this.sidebar.classList.toggle('open');
                if (!isOpen) {
                    this.sidebar.classList.remove('collapsed');
                }
                return;
            }

            this.isCollapsed = !this.isCollapsed;
            this.sidebar.classList.toggle('collapsed', this.isCollapsed);
            localStorage.setItem('sidebarCollapsed', this.isCollapsed);
            this.updateFloatingButtonVisibility();
        }

        closeSidebar() {
            this.sidebar.classList.remove('open');
        }

        openSidebar() {
            this.sidebar.classList.add('open');
        }

        setupSubmenus() {
            const submenuTriggers = document.querySelectorAll('[data-toggle-submenu]');
            submenuTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    const submenuId = trigger.dataset.toggleSubmenu;
                    const menuItem = trigger.closest('.has-submenu');
                    menuItem?.classList.toggle('open');
                });
            });
        }

        setupResponsive() {
            const mediaQuery = window.matchMedia('(max-width: 768px)');
            
            mediaQuery.addEventListener('change', (e) => {
                if (e.matches) {
                    // Mobile
                    this.sidebar.classList.remove('collapsed');
                    this.isCollapsed = false;
                } else {
                    // Desktop
                    if (this.isCollapsed) {
                        this.sidebar.classList.add('collapsed');
                    }
                }
            });

            // Check on init
            if (mediaQuery.matches) {
                this.sidebar.classList.remove('collapsed');
            }

                // Close sidebar when overlay is clicked on mobile
                this.sidebar.addEventListener('click', (e) => {
                    if (e.target === this.sidebar && window.innerWidth <= 768) {
                        this.sidebar.classList.remove('open');
                    }
                });

            window.addEventListener('resize', () => this.updateFloatingButtonVisibility());
        }

        restoreState() {
            if (this.isCollapsed && window.innerWidth >= 768) {
                this.sidebar.classList.add('collapsed');
            }
            this.updateFloatingButtonVisibility();
        }

        createFloatingButton() {
            // floating button that appears when sidebar is collapsed (small icon to reopen)
            this.floatingBtn = document.createElement('button');
            this.floatingBtn.id = 'sidebarOpenBtn';
            this.floatingBtn.className = 'sidebar-open-btn';
            this.floatingBtn.title = 'Open sidebar';
            this.floatingBtn.innerHTML = "<i class='bx bx-menu'></i>";
            this.floatingBtn.style.position = 'fixed';
            this.floatingBtn.style.left = '12px';
            this.floatingBtn.style.top = '16px';
            this.floatingBtn.style.zIndex = '1200';
            this.floatingBtn.style.width = '40px';
            this.floatingBtn.style.height = '40px';
            this.floatingBtn.style.borderRadius = '8px';
            this.floatingBtn.style.display = 'none';
            this.floatingBtn.style.alignItems = 'center';
            this.floatingBtn.style.justifyContent = 'center';
            this.floatingBtn.style.background = 'var(--sb-accent-light)';
            this.floatingBtn.style.color = '#fff';
            this.floatingBtn.style.border = 'none';
            this.floatingBtn.style.boxShadow = '0 6px 20px rgba(15,23,42,0.18)';
            document.body.appendChild(this.floatingBtn);

            this.floatingBtn.addEventListener('click', () => {
                // Expand sidebar and focus first link
                this.sidebar.classList.remove('collapsed');
                this.isCollapsed = false;
                localStorage.setItem('sidebarCollapsed', 'false');
                this.updateFloatingButtonVisibility();
            });
        }

        updateFloatingButtonVisibility() {
            if (!this.floatingBtn) return;
            // show button when collapsed on wider screens or when sidebar is closed on mobile
            const shouldShow = this.sidebar.classList.contains('collapsed') && window.innerWidth >= 768;
            this.floatingBtn.style.display = shouldShow ? 'flex' : 'none';
        }

        toggleTheme() {
            this.isDarkMode = !this.isDarkMode;
            document.body.classList.toggle('dark-mode', this.isDarkMode);
            localStorage.setItem('darkMode', this.isDarkMode);
        }
    }

    // Initialize sidebar on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new ModernSidebar());
    } else {
        new ModernSidebar();
    }

    // Global helper functions
    function newFolder() {
        const folderName = prompt('Enter folder name:');
        if (folderName && folderName.trim()) {
            const currentFolderId = document.body.dataset.currentFolderId || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'dashboard_new.php';
            form.innerHTML = `
                <input type="hidden" name="create_folder" value="1">
                <input type="hidden" name="folder_name" value="${folderName.trim()}">
                <input type="hidden" name="parent_folder_id" value="${currentFolderId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openHelpCenter() {
        // Open help center modal or redirect to help page
        alert('Help Center coming soon!');
    }
</script>
