<?php
require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';

$auth = new Auth();
$user = $auth->user();

if (!$user) {
    header('Location: login.php');
    exit;
}

$fileManager = new FileManager();
$folderManager = new FolderManager();

$message = '';

// Load deleted items so actions like emptying the bin can operate on them
$deletedFiles = $fileManager->getDeletedFiles($user['id']);
$deletedFolders = $folderManager->getDeletedFolders($user['id']);

if (isset($_POST['empty_bin'])) {

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

    $message = 'Recycle bin emptied!';
}

/* =========================
   ACTIONS
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // RESTORE FILE
    if (isset($_POST['restore_file'])) {

        if ($fileManager->restoreFile(
            (int) $_POST['file_id'],
            $user['id']
        )) {

            $message = 'File restored successfully!';
        }
    }

    // DELETE FILE PERMANENTLY
    elseif (isset($_POST['permanent_delete_file'])) {

        if ($fileManager->permanentDeleteFile(
            (int) $_POST['file_id'],
            $user['id']
        )) {

            $message = 'File permanently deleted!';
        }
    }


    // Note: no preview rendered here to avoid referencing undefined $file.
    // File previews are rendered in the main listing below.
    // RESTORE FOLDER
    if (isset($_POST['restore_folder'])) {

        if ($folderManager->restoreFolder(
            (int) $_POST['folder_id'],
            $user['id']
        )) {

            $message = 'Folder restored successfully!';
        }
    }

    // DELETE FOLDER PERMANENTLY
    elseif (isset($_POST['permanent_delete_folder'])) {

        if ($folderManager->permanentDeleteFolder(
            (int) $_POST['folder_id'],
            $user['id']
        )) {

            $message = 'Folder permanently deleted!';
        }
    }
}

/* =========================
   GET DATA
========================= */

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Recycle Bin - GoCloud</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- BOXICONS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'>

    <!-- CSS -->
    <link rel="stylesheet"
        href="../assets/css/style.css">

</head>

<body class="dashboard-page">

    <div class="container-fluid">

        <div class="row">

            <!-- SIDEBAR -->
            <div class="col-lg-2 p-0">

                <?php include '../components/sidebar.php'; ?>

            </div>

            <!-- MAIN CONTENT -->
            <div class="col-lg-10 p-4">

                <!-- NAVBAR -->
                <?php include '../components/navbar.php'; ?>

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h3 class="fw-bold mb-1">

                            Recycle Bin

                        </h3>

                        <p class="text-muted mb-0">

                            Deleted files and folders appear here

                        </p>

                    </div>

                    <?php if (!empty($deletedFiles) || !empty($deletedFolders)): ?>

                        <form method="POST">

                            <button type="submit"
                                name="empty_bin"
                                class="btn btn-danger rounded-pill px-4">

                                Empty Bin

                            </button>

                        </form>

                    <?php endif; ?>

                </div>

                <!-- ALERT -->
                <?php if ($message): ?>

                    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0">

                        <?= htmlspecialchars($message) ?>

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>

                    </div>

                <?php endif; ?>

                <!-- EMPTY STATE -->
                <?php if (empty($deletedFiles) && empty($deletedFolders)): ?>

                    <div class="card border-0 shadow-sm recycle-card">

                        <div class="card-body text-center py-5">

                            <img src="../assets/illustrations/empty_recycle.svg" alt="Recycle bin empty" class="img-fluid mb-4 recycle-empty-illustration">

                            <h4 class="mt-3">

                                Recycle Bin is Empty

                            </h4>

                            <p class="text-muted">

                                Deleted files and folders will appear here

                            </p>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="row g-4">

                        <!-- FOLDERS -->
                        <?php foreach ($deletedFolders as $folder): ?>

                            <div class="col-md-4 col-lg-3">

                                <div class="card recycle-item h-100 border-0 shadow-sm">

                                    <div class="card-body d-flex flex-column">

                                        <!-- ICON -->
                                        <div class="text-center mb-3">

                                            <i class='bx bxs-folder display-3 text-warning'></i>

                                        </div>

                                        <!-- NAME -->
                                        <h6 class="text-truncate text-center">

                                            <?= htmlspecialchars($folder['folder_name']) ?>

                                        </h6>

                                        <!-- INFO -->
                                        <small class="text-muted text-center d-block mb-3">

                                            Deleted:
                                            <?= date('d M Y', strtotime($folder['deleted_at'])) ?>

                                        </small>

                                        <!-- BUTTONS -->
                                        <div class="mt-auto d-flex gap-2">

                                            <!-- RESTORE -->
                                            <form method="POST" class="w-50">

                                                <input type="hidden"
                                                    name="folder_id"
                                                    value="<?= $folder['id'] ?>">

                                                <button type="submit"
                                                    name="restore_folder"
                                                    class="btn btn-outline-primary btn-sm w-100">

                                                    Restore

                                                </button>

                                            </form>

                                            <!-- DELETE -->
                                            <form method="POST" class="w-50">

                                                <input type="hidden"
                                                    name="folder_id"
                                                    value="<?= $folder['id'] ?>">

                                                <button type="submit"
                                                    name="permanent_delete_folder"
                                                    class="btn btn-outline-danger btn-sm w-100"
                                                    onclick="return confirm('Delete permanently?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>


                        <!-- FILES -->
                        <?php foreach ($deletedFiles as $file): ?>

                            <div class="col-md-4 col-lg-3">

                                <div class="card recycle-item h-100 border-0 shadow-sm">

                                    <div class="card-body d-flex flex-column">

                                        <!-- PREVIEW -->
                                        <div class="recycle-preview mb-3">

                                            <?php if (
                                                !empty($file['file_type']) &&
                                                strpos($file['file_type'], 'image') !== false
                                            ): ?>

                                                <img src="../storage/uploads/<?= htmlspecialchars($file['file_path']) ?>"
                                                    class="img-fluid rounded recycle-image">

                                            <?php else: ?>

                                                <div class="text-center">

                                                    <i class='bx bx-file display-3 text-primary'></i>

                                                </div>

                                            <?php endif; ?>

                                        </div>

                                        <!-- FILE NAME -->
                                        <h6 class="text-truncate text-center">

                                            <?= htmlspecialchars($file['file_name']) ?>

                                        </h6>

                                        <!-- INFO -->
                                        <small class="text-muted text-center d-block">

                                            <?= round($file['file_size'] / 1024, 1) ?> KB

                                        </small>

                                        <small class="text-muted text-center d-block mb-3">

                                            Deleted:
                                            <?= date('d M Y', strtotime($file['deleted_at'])) ?>

                                        </small>

                                        <!-- BUTTONS -->
                                        <div class="mt-auto d-flex gap-2">

                                            <!-- RESTORE -->
                                            <form method="POST" class="w-50">

                                                <input type="hidden"
                                                    name="file_id"
                                                    value="<?= $file['id'] ?>">

                                                <button type="submit"
                                                    name="restore_file"
                                                    class="btn btn-outline-primary btn-sm w-100">

                                                    Restore

                                                </button>

                                            </form>

                                            <!-- DELETE -->
                                            <form method="POST" class="w-50">

                                                <input type="hidden"
                                                    name="file_id"
                                                    value="<?= $file['id'] ?>">

                                                <button type="submit"
                                                    name="permanent_delete_file"
                                                    class="btn btn-outline-danger btn-sm w-100"
                                                    onclick="return confirm('Delete permanently?')">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- EMPTY BIN MODAL -->
    <div class="modal fade"
        id="emptyRecycleModal"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0">

                <div class="modal-body text-center p-5">

                    <i class='bx bx-trash display-1 text-danger'></i>

                    <h4 class="mt-3">

                        Empty Recycle Bin?

                    </h4>

                    <p class="text-muted">

                        This action cannot be undone

                    </p>

                    <button class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/main.js"></script>

</body>

</html>