<?php
if (!isset($auth)) {
    return;
}

$user = $auth->user();
$folders = $folderManager->getFolders($user['id']);
$activeFolder = $_GET['folder_id'] ?? null;
$page = $_GET['page'] ?? null;

$storage = $fileManager->getStorageSummary($user['id']);
$used = $storage['storage_used'];
$limit = $storage['storage_limit'];
$percent = $limit > 0 ? round($used / $limit * 100, 0) : 0;
?>

<aside id="sidebar" class="sidebar hide-on-mobile">
    <div class="brand">
        <a href="dashboard_new.php" class="d-flex align-items-center text-decoration-none">
            <div>
                <div class="fw-bold">GoCloud</div>
                <small class="text-muted">Cloud Storage</small>
            </div>
        </a>
    </div>

    <div class="mb-3">
        <button id="sidebarUploadBtn" class="btn btn-outline-primary w-100 btn-sm">Upload</button>
    </div>

    <nav class="menu flex-grow-1">
        <ul class="list-unstyled">
            <li><a href="dashboard_new.php" class="menu-item <?= $page !== 'shared' && !$activeFolder ? 'active' : '' ?>"><i class="bx bx-grid-alt"></i> Dashboard</a></li>
            <li><a href="dashboard_new.php" class="menu-item <?= $activeFolder ? 'active' : '' ?>"><i class="bx bx-folder"></i> My Go</a></li>
            <li><a href="computers.php" class="menu-item"><i class="bx bx-desktop"></i> Computers</a></li>
            <li><a href="shared.php" class="menu-item"><i class="bx bx-group"></i> Shared With Me</a></li>
            <li><a href="recent.php" class="menu-item"><i class="bx bx-time"></i> Recent</a></li>
            <li><a href="starred.php" class="menu-item"><i class="bx bx-star"></i> Starred</a></li>
            <li><a href="trash.php" class="menu-item"><i class="bx bx-trash"></i> Trash</a></li>
            <li><a href="backups.php" class="menu-item"><i class="bx bx-refresh"></i> Backups</a></li>
            <li><a href="connected_accounts.php" class="menu-item"><i class="bx bx-link"></i> Connected Accounts</a></li>
        </ul>
    </nav>

    <div class="mt-auto">
        <div class="storage-box mb-3 p-2 bg-white rounded">
            <div class="d-flex justify-content-between small mb-1"><strong>Storage</strong><span class="text-muted small"><?= round($used / 1024, 1) ?> MB</span></div>
            <div class="progress" style="height:8px; border-radius:6px;">
                <div class="progress-bar bg-info" role="progressbar" style="width:<?= $percent ?>%"></div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="settings.php" class="menu-item flex-grow-1"><i class="bx bx-cog"></i> Settings</a>
            <a href="../public/logout.php" class="menu-item text-danger"><i class="bx bx-log-out"></i> Logout</a>
        </div>
    </div>
</aside>

<script>
    // Sidebar upload button opens the upload menu in navbar (delegated)
    document.getElementById('sidebarUploadBtn')?.addEventListener('click', function() {
        const uploadBtn = document.getElementById('uploadDropdownBtn');
        if (uploadBtn) uploadBtn.click();
    });
</script>