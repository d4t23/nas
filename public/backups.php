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

// Mock backup data
$backups = [
    [
        'id' => 1,
        'name' => 'Full System Backup',
        'date' => '2026-05-22 14:30:00',
        'size' => 5242880000,
        'files_count' => 1250,
        'status' => 'completed'
    ],
    [
        'id' => 2,
        'name' => 'Documents Backup',
        'date' => '2026-05-21 10:15:00',
        'size' => 1073741824,
        'files_count' => 450,
        'status' => 'completed'
    ],
    [
        'id' => 3,
        'name' => 'Media Files Backup',
        'date' => '2026-05-20 18:45:00',
        'size' => 8589934592,
        'files_count' => 890,
        'status' => 'completed'
    ]
];

$message = '';

// Create backup
if (isset($_POST['create_backup'])) {
    $message = 'Backup creation started. This may take several minutes.';
}

// Restore backup
if (isset($_POST['restore_backup'])) {
    $message = 'Backup restore started. Please do not close this page.';
}

// Delete backup
if (isset($_POST['delete_backup'])) {
    $message = 'Backup deleted successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups - GoCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                            <h4 class="mb-1"><i class='bx bx-refresh me-2'></i>Backups</h4>
                            <p class="text-muted mb-0">Create and manage backups of your files.</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                            <i class='bx bx-plus me-2'></i>Create New Backup
                        </button>
                    </div>
                </div>
            </div>

            <!-- Backup Statistics -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Backups</p>
                                    <h3 class="mb-0"><?= count($backups) ?></h3>
                                </div>
                                <i class='bx bx-folder-open bx-lg' style="color: #007bff; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Files</p>
                                    <h3 class="mb-0"><?= array_sum(array_column($backups, 'files_count')) ?></h3>
                                </div>
                                <i class='bx bx-file bx-lg' style="color: #28a745; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Size</p>
                                    <h3 class="mb-0"><?= round(array_sum(array_column($backups, 'size')) / (1024 ** 3), 1) ?> GB</h3>
                                </div>
                                <i class='bx bx-database bx-lg' style="color: #ffc107; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backups List -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Recent Backups</h5>

                    <?php if (empty($backups)): ?>
                        <div class="text-center py-5">
                            <i class='bx bx-inbox bx-lg mb-3' style="color: #ccc;"></i>
                            <p class="text-muted">No backups yet. Create your first backup to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Backup Name</th>
                                        <th>Date</th>
                                        <th>Files</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class='bx bx-package' style="color: #007bff;"></i>
                                                    <strong><?= htmlspecialchars($backup['name']) ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?= date('M d, Y H:i', strtotime($backup['date'])) ?></small>
                                            </td>
                                            <td>
                                                <small><?= $backup['files_count'] ?></small>
                                            </td>
                                            <td>
                                                <small><?= round($backup['size'] / (1024 ** 3), 2) ?> GB</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= ucfirst($backup['status']) ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="backup_id" value="<?= $backup['id'] ?>">
                                                        <button type="submit" name="restore_backup" class="btn btn-outline-primary" title="Restore">
                                                            <i class='bx bx-reset'></i>
                                                        </button>
                                                    </form>
                                                    <a href="#" class="btn btn-outline-secondary" title="Download">
                                                        <i class='bx bx-download'></i>
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this backup?');">
                                                        <input type="hidden" name="backup_id" value="<?= $backup['id'] ?>">
                                                        <button type="submit" name="delete_backup" class="btn btn-outline-danger" title="Delete">
                                                            <i class='bx bx-trash'></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Create Backup Modal -->
            <div class="modal fade" id="createBackupModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Backup</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="backupName" class="form-label">Backup Name</label>
                                    <input type="text" class="form-control" id="backupName" name="backup_name" placeholder="e.g., Monthly Backup" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Backup Type</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="fullBackup" value="full" checked>
                                        <label class="form-check-label" for="fullBackup">
                                            Full Backup (all files and folders)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="selectiveBackup" value="selective">
                                        <label class="form-check-label" for="selectiveBackup">
                                            Selective Backup (choose specific files/folders)
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <i class='bx bx-info-circle me-1'></i>
                                        Creating a backup may take several minutes. You'll be notified when it's complete.
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="create_backup" class="btn btn-primary">
                                    <i class='bx bx-plus me-1'></i>Create Backup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include __DIR__ . '/../components/modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>