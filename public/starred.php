<?php
session_start();

require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';
require_once __DIR__ . '/../app/classes/ShareManager.php';

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();

$fileManager = new FileManager();
$folderManager = new FolderManager();
$shareManager = new ShareManager();

// For sidebar
$activeFolder = $_GET['folder_id'] ?? null;
$page = $_GET['page'] ?? null;

$message = '';
$shareUrl = null;

// DELETE FILE
if (isset($_POST['delete_file'])) {
    $fileManager->deleteFile(
        $_POST['file_id'],
        $user['id']
    );
    header("Location: starred.php");
    exit;
}

// RENAME FILE
if (isset($_POST['rename_file'])) {
    $fileManager->renameFile(
        $_POST['file_id'],
        $user['id'],
        $_POST['new_name']
    );
    header("Location: starred.php");
    exit;
}

// UNSTAR FILE
if (isset($_POST['toggle_star'])) {
    $fileManager->toggleStar(
        $_POST['file_id'],
        $user['id']
    );
    header("Location: starred.php");
    exit;
}

// SHARE FILE
if (isset($_POST['share_file'])) {
    $token = $shareManager->createShare($_POST['file_id']);
    $shareUrl = sprintf(
        '%s/share.php?token=%s',
        dirname(
            (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
                . $_SERVER['HTTP_HOST']
                . $_SERVER['REQUEST_URI']
        ),
        $token
    );
}

// Get starred files
$files = $fileManager->getStarredFiles($user['id']);

// Categorize files
$categorizedFiles = [
    'Images' => [],
    'Videos' => [],
    'Documents' => [],
    'Audio' => [],
    'Others' => []
];

foreach ($files as $file) {
    if (strpos($file['file_type'], 'image') !== false) {
        $categorizedFiles['Images'][] = $file;
    } elseif (strpos($file['file_type'], 'video') !== false) {
        $categorizedFiles['Videos'][] = $file;
    } elseif (strpos($file['file_type'], 'audio') !== false) {
        $categorizedFiles['Audio'][] = $file;
    } elseif (strpos($file['file_type'], 'pdf') !== false || strpos($file['file_type'], 'text') !== false || strpos($file['file_type'], 'document') !== false) {
        $categorizedFiles['Documents'][] = $file;
    } else {
        $categorizedFiles['Others'][] = $file;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starred - GoCloud</title>
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

            <?php if ($shareUrl): ?>
                <div class="alert alert-success mt-3" role="alert">
                    Share link created: <a href="<?= htmlspecialchars($shareUrl) ?>" target="_blank">Copy share link</a>
                </div>
            <?php endif; ?>

            <div class="row align-items-center justify-content-between dashboard-head-row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="dashboard-access-row d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="mb-1"><i class='bx bx-star me-2'></i>Starred Files</h4>
                            <p class="text-muted mb-0">Your favorite files marked as starred.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($files)): ?>
                <div class="empty-state-card card border-0 shadow-sm text-center py-5 px-4 mb-4">
                    <div class="card-body">
                        <i class='bx bx-star bx-lg mb-3' style="color: #ccc;"></i>
                        <h4 class="mb-2">No starred files</h4>
                        <p class="text-muted mb-4">Star your favorite files to find them here easily.</p>
                        <a href="dashboard_new.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($categorizedFiles as $category => $categoryFiles): ?>
                    <?php if (!empty($categoryFiles)): ?>
                        <div class="mb-5">
                            <h5 class="mb-3 text-muted fw-bold">
                                <i class='bx bx-folder me-2'></i><?= $category ?> (<?= count($categoryFiles) ?>)
                            </h5>
                            <div class="row g-4">
                                <?php foreach ($categoryFiles as $file): ?>
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
                                                            <a class="dropdown-item" href="../storage/uploads/<?= htmlspecialchars($file['file_path']) ?>" download>
                                                                <i class='bx bx-download me-2'></i>Download
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                                <button type="submit" name="toggle_star" class="dropdown-item">
                                                                    <i class='bx bx-star me-2'></i>Unstar
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                                <button type="submit" name="share_file" class="dropdown-item">
                                                                    <i class='bx bx-share-alt me-2'></i>Share
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                                <button type="submit" name="delete_file" class="dropdown-item text-danger">
                                                                    <i class='bx bx-trash me-2'></i>Delete
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
                                                <small class="text-muted d-block">Modified: <?= date('M d, Y', strtotime($file['updated_at'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../components/modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>