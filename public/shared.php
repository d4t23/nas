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

// GET ALL SHARED FILES
$sharedFiles = $shareManager->getUserShares($user['id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared With Me - GoCloud</title>
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
                            <h4 class="mb-1"><i class='bx bx-group me-2'></i>Shared With Me</h4>
                            <p class="text-muted mb-0">Files you have shared with others.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($sharedFiles)): ?>
                <div class="empty-state-card card border-0 shadow-sm text-center py-5 px-4 mb-4">
                    <div class="card-body">
                        <i class='bx bx-share-alt bx-lg mb-3' style="color: #ccc;"></i>
                        <h4 class="mb-2">No shared files yet</h4>
                        <p class="text-muted mb-4">Files you share will appear here with their sharing links.</p>
                        <a href="dashboard_new.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($sharedFiles as $share): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <div class="file-box h-100 p-4 shadow-sm position-relative file-card">
                                <div class="file-card-header d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="fw-semibold text-truncate" title="<?= htmlspecialchars($share['file_name']) ?>">
                                            <?= htmlspecialchars(substr($share['file_name'], 0, 30)) ?>
                                        </div>
                                        <small class="text-muted">Shared on <?= date('M d, Y', strtotime($share['created_at'])) ?></small>
                                    </div>
                                    <span class="badge bg-success">Shared</span>
                                </div>

                                <div class="file-preview-area text-center py-3 px-3 mb-3">
                                    <i class='bx bx-file bx-lg' style="color: #999;"></i>
                                </div>

                                <div class="file-share-actions d-flex gap-2 mb-3">
                                    <a href="share.php?token=<?= htmlspecialchars($share['token']) ?>" class="btn btn-sm btn-primary flex-grow-1" target="_blank">
                                        <i class='bx bx-link-external me-1'></i>View Link
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?= htmlspecialchars($share['token']) ?>')">
                                        <i class='bx bx-copy'></i>
                                    </button>
                                </div>

                                <div class="share-link-box p-2 bg-light rounded" style="font-size: 0.75rem; word-break: break-all;">
                                    <code><?php echo substr(
                                                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
                                                    . $_SERVER['HTTP_HOST']
                                                    . dirname($_SERVER['REQUEST_URI'])
                                                    . '/share.php?token=' . $share['token'],
                                                0,
                                                50
                                            ) ?>...</code>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../components/modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function copyToClipboard(token) {
            const shareUrl = '<?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/share.php?token=' ?>' + token;
            navigator.clipboard.writeText(shareUrl).then(() => {
                alert('Share link copied to clipboard!');
            });
        }
    </script>
</body>

</html>