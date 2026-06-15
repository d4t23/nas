<?php
session_start();

require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();

$fileManager = new FileManager();
$folderManager = new FolderManager();

// For sidebar
$activeFolder = $_GET['folder_id'] ?? null;
$page = $_GET['page'] ?? null;

$message = '';

// Load deleted items
$deletedFiles = $fileManager->getDeletedFiles($user['id']);
$deletedFolders = $folderManager->getDeletedFolders($user['id']);

// RESTORE FILE
if (isset($_POST['restore_file'])) {
    if ($fileManager->restoreFile(
        (int) $_POST['file_id'],
        $user['id']
    )) {
        $message = 'File restored successfully!';
        // Reload deleted files
        $deletedFiles = $fileManager->getDeletedFiles($user['id']);
    }
}

// RESTORE FOLDER
if (isset($_POST['restore_folder'])) {
    if ($folderManager->restoreFolder(
        (int) $_POST['folder_id'],
        $user['id']
    )) {
        $message = 'Folder restored successfully!';
        // Reload deleted folders
        $deletedFolders = $folderManager->getDeletedFolders($user['id']);
    }
}

// PERMANENTLY DELETE FILE
if (isset($_POST['permanent_delete_file'])) {
    $fileManager->permanentDeleteFile(
        (int) $_POST['file_id'],
        $user['id']
    );
    $message = 'File permanently deleted!';
    // Reload deleted files
    $deletedFiles = $fileManager->getDeletedFiles($user['id']);
}

// PERMANENTLY DELETE FOLDER
if (isset($_POST['permanent_delete_folder'])) {
    $folderManager->permanentDeleteFolder(
        (int) $_POST['folder_id'],
        $user['id']
    );
    $message = 'Folder permanently deleted!';
    // Reload deleted folders
    $deletedFolders = $folderManager->getDeletedFolders($user['id']);
}

// EMPTY TRASH
if (isset($_POST['empty_trash'])) {
    foreach ($deletedFiles as $file) {
        $fileManager->permanentDeleteFile(
            $file['id'],
            $user['id']
        );
    }

    foreach ($deletedFolders as $folder) {
        $folderManager->permanentDeleteFolder(
            $folder['id'],
            $user['id']
        );
    }

    $message = 'Trash emptied successfully!';
    $deletedFiles = [];
    $deletedFolders = [];
}

$totalDeleted = count($deletedFiles) + count($deletedFolders);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash - GoCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar-modern.css">
</head>

<body class="dashboard-theme">
    <div class="dashboard-card">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <div class="main-panel p-4 dashboard-main">
            <?php include __DIR__ . '/../components/navbar.php'; ?>

            <?php if ($message): ?>
                <div class="alert alert-info mt-3" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="row align-items-center justify-content-between dashboard-head-row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="dashboard-access-row d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="mb-1"><i class='bx bx-trash me-2'></i>Trash</h4>
                            <p class="text-muted mb-0">Deleted files and folders. Items are permanently deleted after 30 days.</p>
                        </div>
                        <?php if ($totalDeleted > 0): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to empty the trash? This cannot be undone.');">
                                <button type="submit" name="empty_trash" class="btn btn-danger">
                                    <i class='bx bx-trash me-1'></i>Empty Trash
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Trash Statistics -->
            <?php if ($totalDeleted > 0): ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted mb-1">Deleted Files</p>
                                        <h3 class="mb-0"><?= count($deletedFiles) ?></h3>
                                    </div>
                                    <i class='bx bx-file bx-lg' style="color: #dc3545; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="text-muted mb-1">Deleted Folders</p>
                                        <h3 class="mb-0"><?= count($deletedFolders) ?></h3>
                                    </div>
                                    <i class='bx bx-folder bx-lg' style="color: #ffc107; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($deletedFiles) && empty($deletedFolders)): ?>
                <div class="empty-state-card card border-0 shadow-sm text-center py-5 px-4 mb-4">
                    <div class="card-body">
                        <i class='bx bx-trash bx-lg mb-3' style="color: #ccc;"></i>
                        <h4 class="mb-2">Trash is empty</h4>
                        <p class="text-muted mb-4">Deleted files and folders will appear here.</p>
                        <a href="dashboard_new.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Deleted Folders -->
                <?php if (!empty($deletedFolders)): ?>
                    <div class="mb-5">
                        <h5 class="mb-3 text-muted fw-bold">
                            <i class='bx bx-folder me-2'></i>Deleted Folders (<?= count($deletedFolders) ?>)
                        </h5>
                        <div class="row g-4">
                            <?php foreach ($deletedFolders as $folder): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                    <div class="file-box h-100 p-4 shadow-sm position-relative file-card">
                                        <div class="file-card-header d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <div class="fw-semibold text-truncate" title="<?= htmlspecialchars($folder['folder_name']) ?>">
                                                    <?= htmlspecialchars($folder['folder_name']) ?>
                                                </div>
                                                <small class="text-muted">Deleted <?= date('M d, Y', strtotime($folder['deleted_at'] ?? $folder['created_at'])) ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                                            <button type="submit" name="restore_folder" class="dropdown-item">
                                                                <i class='bx bx-undo me-2'></i>Restore
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this folder?');">
                                                            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                                            <button type="submit" name="permanent_delete_folder" class="dropdown-item text-danger">
                                                                <i class='bx bx-trash me-2'></i>Delete Permanently
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="file-preview-area text-center py-3 px-3 mb-3">
                                            <i class='bx bx-folder bx-lg' style="color: #999;"></i>
                                        </div>

                                        <div class="file-info-bottom">
                                            <small class="text-muted d-block">Will be permanently deleted in 30 days</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Deleted Files -->
                <?php if (!empty($deletedFiles)): ?>
                    <div class="mb-5">
                        <h5 class="mb-3 text-muted fw-bold">
                            <i class='bx bx-file me-2'></i>Deleted Files (<?= count($deletedFiles) ?>)
                        </h5>
                        <div class="row g-4">
                            <?php foreach ($deletedFiles as $file): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                    <div class="file-box h-100 p-4 shadow-sm position-relative file-card">
                                        <div class="file-card-header d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <div class="fw-semibold text-truncate" title="<?= htmlspecialchars($file['file_name']) ?>">
                                                    <?= htmlspecialchars(substr($file['file_name'], 0, 30)) ?>
                                                </div>
                                                <small class="text-muted"><?= round($file['file_size'] / 1024, 2) ?> KB</small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                            <button type="submit" name="restore_file" class="dropdown-item">
                                                                <i class='bx bx-undo me-2'></i>Restore
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this file?');">
                                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                            <button type="submit" name="permanent_delete_file" class="dropdown-item text-danger">
                                                                <i class='bx bx-trash me-2'></i>Delete Permanently
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="file-preview-area text-center py-3 px-3 mb-3">
                                            <i class='bx bx-file bx-lg' style="color: #999;"></i>
                                        </div>

                                        <div class="file-info-bottom">
                                            <small class="text-muted d-block">Deleted <?= date('M d, Y', strtotime($file['deleted_at'] ?? $file['created_at'])) ?></small>
                                            <small class="text-muted d-block">Will be permanently deleted in 30 days</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../components/modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>