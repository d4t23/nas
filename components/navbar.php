<?php if (!isset($user)): return;
endif; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid py-2">
        <button id="sidebarToggle" class="btn btn-outline-secondary d-lg-none me-2">
            <i class='bx bx-menu fs-4'></i>
        </button>

        <a class="navbar-brand d-flex align-items-center me-3" href="dashboard_new.php">
            <img src="../assets/images/logo.png" alt="GoCloud" class="brand-logo-image rounded me-2" style="width:36px;height:36px;">
        </a>

        <form class="d-flex flex-grow-1 mx-2" method="GET" action="dashboard_new.php">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0"><i class='bx bx-search'></i></span>
                <input class="form-control border-0 bg-transparent form-control-sm" name="q" type="search" placeholder="Search files, folders..." aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-primary rounded-pill btn-sm" type="submit">Search</button>
            </div>
        </form>

        <div class="d-flex align-items-center gap-3">


            <div class="dropdown">
                <button id="uploadDropdownBtn" class="btn btn-outline-primary btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-upload"></i>
                    <span class="d-none d-md-inline ms-1">Upload</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:220px;">
                    <li>
                        <label class="dropdown-item mb-1 text-truncate" style="cursor:pointer;">
                            Upload Files
                            <form method="POST" enctype="multipart/form-data" class="d-none" id="uploadFilesForm">
                                <input type="file" name="files[]" id="fileInput" multiple>
                            </form>
                        </label>
                    </li>
                    <li>
                        <label class="dropdown-item mb-1" style="cursor:pointer;">Upload Folder
                            <form method="POST" enctype="multipart/form-data" class="d-none" id="uploadFolderForm">
                                <input webkitdirectory directory type="file" name="folder_files[]" id="folderInput">
                            </form>
                        </label>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="connected_accounts.php?provider=google">Import from Google Drive</a></li>
                    <li><a class="dropdown-item" href="connected_accounts.php?provider=onedrive">Import from OneDrive</a></li>
                    <li><a class="dropdown-item" href="connected_accounts.php?provider=dropbox">Import from Dropbox</a></li>
                    <li><a class="dropdown-item" href="#">Import from iCloud</a></li>
                </ul>
            </div>

            <div class="d-none d-md-flex flex-column text-end me-2">
                <div class="fw-semibold"><?= htmlspecialchars($user['name']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
            </div>

            <div class="dropdown">
                <a href="#" class="text-decoration-none d-inline-flex align-items-center" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if ($user['profile_picture']): ?>
                        <img src="../storage/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="../public/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });

        document.addEventListener('click', function(event) {
            if (sidebar.classList.contains('show') && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Upload dropdown: delegate clicks to hidden inputs
    document.addEventListener('click', function(e) {
        const label = e.target.closest('label.dropdown-item');
        if (!label) return;
        const fileInput = document.getElementById('fileInput');
        const folderInput = document.getElementById('folderInput');
        if (label.textContent.trim().startsWith('Upload Files')) {
            fileInput?.click();
        }
        if (label.textContent.trim().startsWith('Upload Folder')) {
            folderInput?.click();
        }
    });

    // small typing placeholder effect removed to keep UX minimal
</script>