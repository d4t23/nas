<?php
session_start();

require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';
require_once __DIR__ . '/../app/classes/ShareManager.php';
require_once __DIR__ . '/../app/classes/User.php';

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();

$fileManager = new FileManager();
$folderManager = new FolderManager();
$shareManager = new ShareManager();
$userModel = new User();

$message = '';
$shareUrl = null;

if (isset($_SESSION['dashboard_message'])) {
    $message = $_SESSION['dashboard_message'];
    unset($_SESSION['dashboard_message']);
}

$folderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : null;
$page = $_GET['page'] ?? null;


// CREATE FOLDER
if (isset($_POST['create_folder'])) {
    $parentId = isset($_POST['parent_folder_id']) && $_POST['parent_folder_id'] !== ''
        ? intval($_POST['parent_folder_id'])
        : $folderId;

    $folderManager->createFolder(
        $user['id'],
        $_POST['folder_name'],
        $parentId
    );

    $redirect = 'dashboard_new.php';
    if ($parentId) {
        $redirect .= '?folder_id=' . intval($parentId);
    }
    header("Location: {$redirect}");
    exit;
}


// DELETE FOLDER
if (isset($_POST['delete_folder'])) {
    $folderManager->deleteFolder(
        intval($_POST['folder_id']),
        $user['id']
    );

    $redirect = 'dashboard_new.php';
    if ($folderId) {
        $redirect .= '?folder_id=' . intval($folderId);
    }
    header("Location: {$redirect}");
    exit;
}


// RENAME FOLDER
if (isset($_POST['rename_folder'])) {
    $folderManager->renameFolder(
        intval($_POST['folder_id']),
        $user['id'],
        $_POST['new_name']
    );

    $redirect = 'dashboard_new.php';
    if ($folderId) {
        $redirect .= '?folder_id=' . intval($folderId);
    }
    header("Location: {$redirect}");
    exit;
}


// MOVE FOLDER
if (isset($_POST['move_folder'])) {
    $folderManager->moveFolder(
        intval($_POST['folder_id']),
        $user['id'],
        $_POST['target_parent_id'] !== '' ? intval($_POST['target_parent_id']) : null
    );

    $redirect = 'dashboard_new.php';
    if ($folderId) {
        $redirect .= '?folder_id=' . intval($folderId);
    }
    header("Location: {$redirect}");
    exit;
}


// UPLOAD FILES
if (isset($_POST['upload_file'])) {

    $uploadFolderId = $_POST['upload_folder_id'] ?: null;
    $folderRelativePaths = [];
    if (!empty($_POST['folder_relative_paths'])) {
        $decoded = json_decode($_POST['folder_relative_paths'], true);
        if (is_array($decoded)) {
            $folderRelativePaths = $decoded;
        }
    }

    $inputGroups = ['files', 'folder_files'];
    $uploadedCount = 0;
    $uploadErrors = [];

    foreach ($inputGroups as $group) {
        if (!isset($_FILES[$group]) || empty($_FILES[$group]['name'])) {
            continue;
        }

        $names = $_FILES[$group]['name'];
        $types = $_FILES[$group]['type'];
        $tmpNames = $_FILES[$group]['tmp_name'];
        $errors = $_FILES[$group]['error'];
        $sizes = $_FILES[$group]['size'];

        if (!is_array($names)) {
            $names = [$names];
            $types = [$types];
            $tmpNames = [$tmpNames];
            $errors = [$errors];
            $sizes = [$sizes];
        }

        foreach ($names as $key => $name) {
            if ($name === '') {
                continue;
            }

            $file = [
                'name' => $names[$key],
                'type' => $types[$key],
                'tmp_name' => $tmpNames[$key],
                'error' => $errors[$key],
                'size' => $sizes[$key],
            ];

            $targetFolderId = $uploadFolderId;
            if ($group === 'folder_files') {
                $relativePath = $folderRelativePaths[$key] ?? '';
                if ($relativePath === '') {
                    $relativePath = trim(str_replace('\\', '/', pathinfo($name, PATHINFO_DIRNAME)), '/');
                }

                if ($relativePath !== '' && $relativePath !== '.') {
                    $resolvedFolderId = $folderManager->ensureFolderPath(
                        $user['id'], 
                        $uploadFolderId,
                        $relativePath
                    );
                    if ($resolvedFolderId !== null) {
                        $targetFolderId = $resolvedFolderId;
                    }
                }
                $file['name'] = basename(str_replace('\\', '/', $name));
            }

            $result = $fileManager->uploadFile(
                $user['id'],
                $file,
                $targetFolderId
            );

            if ($result['success']) {
                $uploadedCount++;
            } else {
                $uploadErrors[] = htmlspecialchars($file['name']) . ': ' . $result['message'];
            }
        }
    }

    if ($uploadedCount > 0) {
        $_SESSION['dashboard_message'] = "Uploaded {$uploadedCount} file(s).";
    }
    if (!empty($uploadErrors)) {
        $_SESSION['dashboard_message'] = implode(' ', $uploadErrors);
    }
    if ($uploadedCount === 0 && empty($uploadErrors)) {
        $_SESSION['dashboard_message'] = 'No files were selected or the files could not be processed.';
    }

    $redirect = 'dashboard_new.php';
    if ($uploadFolderId) {
        $redirect .= '?folder_id=' . intval($uploadFolderId);
    }

    header("Location: {$redirect}");
    exit;
}


// DELETE FILE
if (isset($_POST['delete_file'])) {

    $fileManager->deleteFile(
        $_POST['file_id'],
        $user['id']
    );

    header("Location: dashboard_new.php");
    exit;
}


// RENAME FILE
if (isset($_POST['rename_file'])) {

    $fileManager->renameFile(
        $_POST['file_id'],
        $user['id'],
        $_POST['new_name']
    );

    $redirect = 'dashboard_new.php';
    if ($folderId) {
        $redirect .= '?folder_id=' . intval($folderId);
    }

    header("Location: {$redirect}");
    exit;
}


// STAR FILE
if (isset($_POST['toggle_star'])) {

    $fileManager->toggleStar(
        $_POST['file_id'],
        $user['id']
    );

    header("Location: dashboard_new.php");
    exit;
}


// SHARE FILE
if (isset($_POST['share_file'])) {

    $token = $shareManager->createShare(
        $_POST['file_id']
    );

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


if (isset($_POST['invite_user'])) {
    $email = filter_input(INPUT_POST, 'invite_email', FILTER_VALIDATE_EMAIL);
    if ($email) {
        $message = "Invite request sent to {$email}. Collaborators can now be invited to your workspace.";
    } else {
        $message = 'Please enter a valid email address to invite someone.';
    }
}


$currentFolder = null;
$breadcrumbs = [];

if ($folderId) {
    $currentFolder = $folderManager->getFolder($folderId, $user['id']);
    if (!$currentFolder) {
        header('Location: dashboard_new.php');
        exit;
    }
    $breadcrumbs = $folderManager->getBreadcrumbs($folderId, $user['id']);
}

$files = $fileManager->getFiles($user['id'], $folderId);

$totalFiles = count($files);
$starredCount = 0;
$typeSummary = [
    'Images' => 0,
    'Videos' => 0,
    'Documents' => 0,
    'Audio' => 0,
    'Others' => 0,
];

foreach ($files as $file) {
    if (!empty($file['is_starred'])) {
        $starredCount++;
    }

    if (strpos($file['file_type'], 'image') !== false) {
        $typeSummary['Images']++;
    } elseif (strpos($file['file_type'], 'video') !== false) {
        $typeSummary['Videos']++;
    } elseif (strpos($file['file_type'], 'audio') !== false) {
        $typeSummary['Audio']++;
    } elseif (strpos($file['file_type'], 'pdf') !== false || strpos($file['file_type'], 'text') !== false || strpos($file['file_type'], 'document') !== false) {
        $typeSummary['Documents']++;
    } else {
        $typeSummary['Others']++;
    }
}

$folders = $folderManager->getFolders($user['id'], $folderId);
$folderSizes = $fileManager->getFolderSizes($user['id']);
$sharedFiles = [];
if ($page === 'shared') {
    $sharedFiles = $shareManager->getUserShares($user['id']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>GoCloud Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar-modern.css">

</head>

<body class="dashboard-theme" data-current-folder-id="<?= htmlspecialchars($folderId) ?>">

    <?php include __DIR__ . '/../components/sidebar-modern.php'; ?>

    <div class="dashboard-card" style="margin-left: var(--sb-expanded-width); transition: margin-left var(--sb-transition, 0.35s);">
        <style>
            body.dark-mode .dashboard-card {
                background: #1f2937;
            }
            
            @media (max-width: 991px) {
                .dashboard-card {
                    margin-left: 0 !important;
                }
            }
            
            .sidebar.collapsed ~ .dashboard-card {
                margin-left: var(--sb-collapsed-width);
            }
            
            @media (max-width: 768px) {
                .sidebar.collapsed ~ .dashboard-card {
                    margin-left: 0;
                }
            }
        </style>

        <div class="main-panel p-4 dashboard-main">

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
                            <h4 class="mb-1 dashboard-access-title">Access your files and folders</h4>
                            <p class="text-muted mb-0">Quickly open your documents, media and shared items.</p>
                        </div>
                    </div>

                    <?php if ($page !== 'shared'): ?>
                        <div class="dashboard-view-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="filters d-flex flex-wrap align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" id="recentFilterBtn">Recents</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="starredFilterBtn" data-filter="starred">Starred</button>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="typeFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Type
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="typeFilterDropdown">
                                        <li><button class="dropdown-item type-filter" type="button" data-value="Images">Images</button></li>
                                        <li><button class="dropdown-item type-filter" type="button" data-value="Videos">Videos</button></li>
                                        <li><button class="dropdown-item type-filter" type="button" data-value="Documents">Documents</button></li>
                                        <li><button class="dropdown-item type-filter" type="button" data-value="Audio">Audio</button></li>
                                        <li><button class="dropdown-item type-filter" type="button" data-value="Others">Others</button></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="controls d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="gridViewBtn" title="Grid View"><i class='bx bx-grid-alt'></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="largeGridViewBtn" title="Large Grid View"><i class='bx bx-grid'></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="listViewBtn" title="List View"><i class='bx bx-list-ul'></i></button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="newFolderButton" onclick="newFolder()"><i class='bx bx-folder-plus'></i> New Folder</button>
                                <button type="button" id="uploadButton" class="btn btn-dark rounded-pill px-4 upload-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#uploadModal">
                                    <div class="upload-content d-flex align-items-center gap-2">
                                        <i class='bx bx-upload'></i>
                                        <span>Upload</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="breadcrumb-row mb-4">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="dashboard_new.php">My Go</a></li>
                                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                        <li class="breadcrumb-item <?= $index === count($breadcrumbs) - 1 ? 'active' : '' ?>" <?= $index === count($breadcrumbs) - 1 ? 'aria-current="page"' : '' ?>>
                                            <?php if ($index < count($breadcrumbs) - 1): ?>
                                                <a href="dashboard_new.php?folder_id=<?= $crumb['id'] ?>"><?= htmlspecialchars($crumb['folder_name']) ?></a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($crumb['folder_name']) ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <?php if ($page !== 'shared'): ?>
                <div id="bulkToolbar" class="bulk-toolbar alert alert-secondary rounded-4 d-none d-flex align-items-center justify-content-between gap-3 mb-4 shadow-sm">
                    <div>
                        <strong id="selectedCount">0</strong> selected
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" id="downloadSelected"><i class='bx bx-download'></i> Download</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="shareSelected"><i class='bx bx-share-alt'></i> Share</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="deleteSelected"><i class='bx bx-trash'></i> Delete</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="moveSelected"><i class='bx bx-right-arrow-alt'></i> Move</button>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="starSelected"><i class='bx bx-star'></i> Star</button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="copyLinkSelected"><i class='bx bx-link'></i> Copy Link</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($page === 'shared'): ?>

                <div class="row g-4">
                    <?php if (empty($sharedFiles)): ?>
                        <div class="col-12">
                            <div class="alert alert-secondary">You haven't shared any files yet.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sharedFiles as $share): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                <div class="file-box h-100 p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="fw-semibold text-truncate">
                                                <?= htmlspecialchars($share['file_name']) ?>
                                            </div>
                                            <small class="text-muted">Shared on <?= date('M d, Y', strtotime($share['created_at'])) ?></small>
                                        </div>
                                        <span class="badge bg-primary">Shared</span>
                                    </div>
                                    <a href="share.php?token=<?= htmlspecialchars($share['token']) ?>" class="d-block text-decoration-none">
                                        <i class='bx bx-link-external me-2'></i>
                                        Open shared link
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <?php if (empty($files) && empty($folders)): ?>
                    <div class="empty-state-card card border-0 shadow-sm text-center py-5 px-4 mb-4">
                        <div class="card-body">
                            <img src="../assets/illustrations/empty_home.svg" alt="No files" class="img-fluid mb-4 empty-state-illustration">
                            <h4 class="mb-2">No files uploaded yet</h4>
                            <p class="text-muted mb-4">Upload your first file or create a folder to get started.</p>
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Files</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4 file-container grid-view" id="fileContainer">
                        <div class="file-list-header align-items-center mb-3 px-3 py-2 rounded-4 shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <input type="checkbox" id="masterCheckbox" class="form-check-input">
                                <span class="text-muted small fw-semibold">Name</span>
                            </div>
                            <div class="d-none d-md-flex align-items-center gap-3 flex-1">
                                <span class="text-muted small fw-semibold">Last Modified</span>
                            </div>
                            <div class="d-none d-lg-flex align-items-center gap-3">
                                <span class="text-muted small fw-semibold">Size</span>
                            </div>
                            <div class="text-end d-none d-md-block"><span class="text-muted small fw-semibold">Actions</span></div>
                        </div>
                        <?php foreach ($folders as $folder): ?>
                            <div class="file-item col-xl-3 col-lg-4 col-md-6 col-12"
                                data-item-id="<?= $folder['id'] ?>"
                                data-item-type="folder"
                                data-folder-name="<?= htmlspecialchars($folder['folder_name']) ?>"
                                data-folder-size="<?= isset($folderSizes[$folder['id']]) ? $folderSizes[$folder['id']]['total_size'] : 0 ?>"
                                data-folder-filecount="<?= isset($folderSizes[$folder['id']]) ? $folderSizes[$folder['id']]['file_count'] : 0 ?>"
                                data-folder-created="<?= htmlspecialchars($folder['created_at']) ?>"
                                data-folder-owner="<?= htmlspecialchars($user['name']) ?>">
                                <div class="file-box h-100 position-relative file-card shadow-sm folder-card">
                                    <div class="file-card-header d-flex justify-content-between align-items-start">
                                        <div class="form-check">
                                            <input class="form-check-input file-select" type="checkbox" data-item-id="<?= $folder['id'] ?>">
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                <i class='bx bx-dots-vertical-rounded'></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item" type="button" onclick="location.href='dashboard_new.php?folder_id=<?= $folder['id'] ?>'">
                                                        <i class='bx bx-folder-open me-2'></i>Open
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" onclick="renameFolder(<?= $folder['id'] ?>, '<?= addslashes(htmlspecialchars($folder['folder_name'])) ?>')">
                                                        <i class='bx bx-edit me-2'></i>Rename
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" onclick="promptMoveFolder(<?= $folder['id'] ?>)">
                                                        <i class='bx bx-right-arrow-alt me-2'></i>Move
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" onclick="copyFolder(<?= $folder['id'] ?>)">
                                                        <i class='bx bx-copy me-2'></i>Copy
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="button" onclick="showDetails(this.closest('.file-item'))">
                                                        <i class='bx bx-info-circle me-2'></i>Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <div class="dropdown-divider"></div>
                                                </li>
                                                <li>
                                                    <form method="POST">
                                                        <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                                        <button type="submit" name="delete_folder" class="dropdown-item text-danger">
                                                            <i class='bx bx-trash me-2'></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="file-preview-area text-center py-3 px-3">
                                        <div class="file-icon-box">
                                            <i class='bx bx-folder display-3 text-warning'></i>
                                        </div>
                                    </div>

                                    <div class="file-info px-3 pb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-semibold file-name-label">
                                                <?= htmlspecialchars($folder['folder_name']) ?>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                                            <span><?= isset($folderSizes[$folder['id']]) ? round($folderSizes[$folder['id']]['total_size'] / 1024, 1) . ' KB' : 'Empty' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($files as $file): ?>
                            <?php
                            $fileType = 'Others';
                            if (strpos($file['file_type'], 'image') !== false) {
                                $fileType = 'Images';
                            } elseif (strpos($file['file_type'], 'video') !== false) {
                                $fileType = 'Videos';
                            } elseif (strpos($file['file_type'], 'audio') !== false) {
                                $fileType = 'Audio';
                            } elseif (strpos($file['file_type'], 'pdf') !== false || strpos($file['file_type'], 'text') !== false || strpos($file['file_type'], 'document') !== false) {
                                $fileType = 'Documents';
                            }
                            $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION)) ?: 'other';
                            ?>
                            <div class="file-item col-xl-3 col-lg-4 col-md-6 col-12"
                                data-item-id="<?= $file['id'] ?>"
                                data-item-type="file"
                                data-file-starred="<?= !empty($file['is_starred']) ? '1' : '0' ?>"
                                data-file-type="<?= htmlspecialchars($fileType) ?>"
                                data-file-extension="<?= htmlspecialchars($fileExtension) ?>"
                                data-file-name="<?= htmlspecialchars($file['file_name']) ?>"
                                data-file-size="<?= htmlspecialchars($file['file_size']) ?>"
                                data-file-date="<?= htmlspecialchars($file['created_at']) ?>"
                                data-file-owner="<?= htmlspecialchars($user['name']) ?>"
                                data-file-folder-id="<?= htmlspecialchars($file['folder_id'] ?? '') ?>">
                                <div class="file-box h-100 position-relative file-card shadow-sm">
                                    <div class="file-card-header d-flex justify-content-between align-items-start">
                                        <div class="form-check">
                                            <input class="form-check-input file-select" type="checkbox" data-item-id="<?= $file['id'] ?>">
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                <i class='bx bx-dots-vertical-rounded'></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="download.php?file_id=<?= $file['id'] ?>">
                                                        <i class='bx bx-download me-2'></i>Download
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="toggleRenameForm(<?= $file['id'] ?>)">
                                                        <i class='bx bx-edit me-2'></i>Rename
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="promptMove(<?= $file['id'] ?>)">
                                                        <i class='bx bx-right-arrow-alt me-2'></i>Move
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="copyFile(<?= $file['id'] ?>)">
                                                        <i class='bx bx-copy me-2'></i>Copy
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="showDetails(this.closest('.file-item'))">
                                                        <i class='bx bx-info-circle me-2'></i>Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <div class="dropdown-divider"></div>
                                                </li>
                                                <li>
                                                    <form method="POST">
                                                        <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                        <button class="dropdown-item" name="share_file">
                                                            <i class='bx bx-share-alt me-2'></i>Share
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST">
                                                        <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                        <button class="dropdown-item text-danger" name="delete_file">
                                                            <i class='bx bx-trash me-2'></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="file-card-badge px-3 pt-1">
                                        <span class="badge bg-secondary text-uppercase small"><?= htmlspecialchars($fileType) ?></span>
                                    </div>

                                    <div class="file-preview-area text-center py-3 px-3">
                                        <?php if (strpos($file['file_type'], 'image') !== false): ?>
                                            <img src="../storage/uploads/<?= htmlspecialchars($file['file_path']) ?>"
                                                class="img-fluid rounded file-preview-thumb"
                                                alt="<?= htmlspecialchars($file['file_name']) ?>">
                                        <?php else: ?>
                                            <div class="file-icon-box">
                                                <i class='bx bx-file display-3 text-primary'></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="file-info px-3 pb-3">
                                        <div class="fw-semibold file-name-label mb-2" id="fileNameLabel<?= $file['id'] ?>">
                                            <?= htmlspecialchars($file['file_name']) ?>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center text-muted small mb-3">
                                            <span><?= htmlspecialchars($fileType) ?></span>
                                            <span class="bullet"></span>
                                            <span><?= htmlspecialchars($fileExtension) ?></span>
                                        </div>
                                        <form method="POST" class="inline-rename-form d-none" id="renameForm<?= $file['id'] ?>">
                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" class="form-control" name="new_name" value="<?= htmlspecialchars($file['file_name']) ?>">
                                                <button type="submit" class="btn btn-primary" name="rename_file">Save</button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="toggleRenameForm(<?= $file['id'] ?>)">Cancel</button>
                                            </div>
                                        </form>
                                        <div class="file-meta-row d-flex flex-wrap gap-3 align-items-center text-muted small mt-3">
                                            <span><i class='bx bx-calendar-alt'></i> <?= htmlspecialchars(date('M d, Y', strtotime($file['created_at']))) ?></span>
                                            <span><i class='bx bx-file-blank'></i> <?= round($file['file_size'] / 1024, 1) ?> KB</span>
                                        </div>
                                    </div>

                                    <div class="file-card-footer px-3 pb-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex gap-2 align-items-center">
                                            <form method="POST">
                                                <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                <button type="submit" name="toggle_star" class="btn btn-sm btn-outline-secondary icon-action" title="Star">
                                                    <?php if ($file['is_starred']): ?>
                                                        <i class='bx bxs-star text-warning'></i>
                                                        <span>Star</span>
                                                    <?php else: ?>
                                                        <i class='bx bx-star'></i>
                                                        <span>Star</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-secondary icon-action rename-action" onclick="toggleRenameForm(<?= $file['id'] ?>)" title="Rename">
                                                <i class='bx bx-edit'></i>
                                                <span>Rename</span>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <a href="download.php?file_id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                            <div class="dropdown list-view-actions d-none">
                                                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="download.php?file_id=<?= $file['id'] ?>">
                                                            <i class='bx bx-download me-2'></i>Download
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item" onclick="toggleRenameForm(<?= $file['id'] ?>)">
                                                            <i class='bx bx-edit me-2'></i>Rename
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <div class="dropdown-divider"></div>
                                                    </li>
                                                    <li>
                                                        <form method="POST">
                                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                            <button class="dropdown-item" name="share_file">
                                                                <i class='bx bx-share-alt me-2'></i>Share
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST">
                                                            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                                            <button class="dropdown-item text-danger" name="delete_file">
                                                                <i class='bx bx-trash me-2'></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>

    </div>

    <!-- UPLOAD MODAL -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5>Upload Files</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="upload_folder_id" value="<?= htmlspecialchars($folderId) ?>">
                        <input type="hidden" name="folder_relative_paths" id="folderRelativePaths" value="">
                        <div id="dropArea" class="drop-area">
                            <i class='bx bx-cloud-upload display-3'></i>
                            <h5>Drag & Drop Files or Folders</h5>
                            <p>Upload individual files or entire folders with path preservation.</p>
                            <input type="file" name="files[]" id="fileInput" multiple hidden>
                            <input type="file" name="folder_files[]" id="folderInput" webkitdirectory directory multiple hidden>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-dark" onclick="document.getElementById('fileInput').click()">
                                    Choose Files
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('folderInput').click()">
                                    Choose Folder
                                </button>
                            </div>

                            <div id="selectedFilesInfo" class="text-muted small mt-2"></div>

                        </div>

                        <div class="progress mt-3">

                            <div class="progress-bar" id="uploadProgress" style="width:0%">

                                0%

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary" name="upload_file">

                            Upload

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- INVITE COLLABORATORS MODAL -->
    <div class="modal fade" id="inviteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Invite Collaborators</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Invite team members to collaborate on shared files.</p>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="invite_email" placeholder="user@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permission Level</label>
                            <select class="form-select" name="permission" required>
                                <option value="view">View Only</option>
                                <option value="edit">Can Edit</option>
                                <option value="share">Can Share</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="invite_user">Send Invite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alert.js"></script>

</body>

</html>