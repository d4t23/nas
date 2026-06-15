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

// Mock data for computers/devices
$computers = [
    [
        'id' => 1,
        'name' => 'Desktop PC',
        'device_type' => 'Computer',
        'os' => 'Windows 10',
        'last_sync' => '2026-05-22 14:30:00',
        'status' => 'online'
    ],
    [
        'id' => 2,
        'name' => 'Laptop',
        'device_type' => 'Laptop',
        'os' => 'Windows 11',
        'last_sync' => '2026-05-21 09:15:00',
        'status' => 'offline'
    ],
    [
        'id' => 3,
        'name' => 'MacBook Pro',
        'device_type' => 'Laptop',
        'os' => 'macOS Monterey',
        'last_sync' => '2026-05-20 16:45:00',
        'status' => 'offline'
    ]
];

$message = '';

// Remove device
if (isset($_POST['remove_device'])) {
    $message = 'Device removed successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computers - GoCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar-modern.css">
</head>

<body class="dashboard-theme">
    <div class="dashboard-card">
        <?php include __DIR__ . '/../components/sidebar-modern.php'; ?>

        <div class="dashboard-card" style="margin-left: var(--sb-expanded-width); transition: margin-left var(--sb-transition, 0.35s);"> p-4 dashboard-main">
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
                            <h4 class="mb-1"><i class='bx bx-desktop me-2'></i>Computers & Devices</h4>
                            <p class="text-muted mb-0">Manage computers and devices connected to your GoCloud account.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($computers as $computer): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-12">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <?php if ($computer['device_type'] === 'Laptop'): ?>
                                                <i class='bx bx-laptop me-2' style="color: #007bff;"></i>
                                            <?php else: ?>
                                                <i class='bx bx-desktop me-2' style="color: #6f42c1;"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($computer['name']) ?>
                                        </h5>
                                        <p class="text-muted mb-1">
                                            <small>
                                                <i class='bx bx-chip me-1'></i>
                                                <?= htmlspecialchars($computer['os']) ?>
                                            </small>
                                        </p>
                                    </div>
                                    <span class="badge <?= $computer['status'] === 'online' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($computer['status']) ?>
                                    </span>
                                </div>

                                <div class="device-info mb-3">
                                    <p class="mb-2">
                                        <small class="text-muted">
                                            <i class='bx bx-time me-1'></i>
                                            Last sync: <?= date('M d, Y H:i', strtotime($computer['last_sync'])) ?>
                                        </small>
                                    </p>
                                </div>

                                <div class="device-actions d-flex gap-2">
                                    <button class="btn btn-sm btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#syncModal<?= $computer['id'] ?>">
                                        <i class='bx bx-sync me-1'></i>Sync Now
                                    </button>
                                    <form method="POST" class="d-inline flex-grow-1">
                                        <input type="hidden" name="device_id" value="<?= $computer['id'] ?>">
                                        <button type="submit" name="remove_device" class="btn btn-sm btn-outline-danger w-100">
                                            <i class='bx bx-trash me-1'></i>Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sync Modal -->
                    <div class="modal fade" id="syncModal<?= $computer['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Sync <?= htmlspecialchars($computer['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Syncing will update all files on this device with the latest changes from your GoCloud account.</p>
                                    <div class="alert alert-info">
                                        <small>This operation may take a few minutes depending on the number of files.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary">Start Sync</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-5 pt-4 border-top">
                <h5 class="mb-3"><i class='bx bx-plus-circle me-2'></i>Add New Device</h5>
                <div class="card border-2 border-dashed" style="background-color: #f8f9fa;">
                    <div class="card-body p-4 text-center">
                        <i class='bx bx-download bx-lg mb-2' style="color: #ccc;"></i>
                        <h6 class="mb-2">Download GoCloud Sync</h6>
                        <p class="text-muted mb-3">Download and install GoCloud Sync app on your device to start syncing files automatically.</p>
                        <a href="#" class="btn btn-primary me-2">
                            <i class='bx bxl-windows me-1'></i>Windows
                        </a>
                        <a href="#" class="btn btn-primary me-2">
                            <i class='bx bxl-apple me-1'></i>macOS
                        </a>
                        <a href="#" class="btn btn-primary">
                            <i class='bx bxl-linux me-1'></i>Linux
                        </a>
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