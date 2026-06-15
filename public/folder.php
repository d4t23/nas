<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/classes/FolderManager.php";
require_once __DIR__ . "/../app/classes/FileManager.php";
require_once __DIR__ . "/../app/classes/Auth.php";
require_once __DIR__ . "/../uploads/upload_file.php";

/* INIT CLASSES */
$folderManager = new FolderManager($db);
$fileManager = new FileManager($db);

/* CHECK USER */
$userId = $user['id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

/* GET FOLDER ID */
$folderId = $_GET['id'] ?? null;

if (!$folderId) {
    die("Folder ID is required");
}

/* GET CURRENT FOLDER */
$folder = $folderManager->getFolder((int)$folderId, (int)$userId);

if (!$folder) {
    die("Folder not found");
}



/* GET SUB FOLDERS */
$subFolders = $folderManager->getSubFolders((int)$folderId, (int)$userId);

?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($folder['folder_name']) ?> - GoCloud</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">

    <!-- Bootstrap (kama hauna kwenye layout nyingine) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="mb-0">
                    <i class='bx bx-folder me-2'></i>
                    <?= htmlspecialchars($folder['folder_name']) ?>
                </h4>
                <small class="text-muted">Folder contents</small>
            </div>

            <div class="d-flex gap-2">

                <!-- ADD FILE -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                    <i class='bx bx-upload me-1'></i> Add File
                </button>

                <!-- ADD SUB FOLDER -->
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createSubFolderModal">
                    <i class='bx bx-folder-plus me-1'></i> Add Folder
                </button>

            </div>
        </div>

        <!-- SUB FOLDERS -->
        <div class="mb-4">
            <h6>Folders</h6>

            <div class="row">
                <?php if (!empty($subFolders)): ?>
                    <?php foreach ($subFolders as $sf): ?>
                        <div class="col-md-3 mb-2">
                            <a href="folder.php?id=<?= $sf['id'] ?>" class="card p-3 text-decoration-none">
                                <i class='bx bx-folder fs-3'></i>
                                <div><?= htmlspecialchars($sf['folder_name']) ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No sub folders</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- FILES -->
        <div>
            <h6>Files</h6>

            <div class="row">
                <?php if (!empty($files)): ?>
                    <?php foreach ($files as $file): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card p-3">

                                <i class='bx bx-file fs-3'></i>

                                <div class="mt-2">
                                    <?= htmlspecialchars($file['file_name']) ?>
                                </div>

                                <a href="<?= $file['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    Open
                                </a>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No files in this folder</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ================= MODALS ================= -->

    <!-- UPLOAD FILE -->
    <div class="modal fade" id="uploadFileModal">
        <div class="modal-dialog">
            <form method="POST" action="upload_file.php" enctype="multipart/form-data">

                <input type="hidden" name="folder_id" value="<?= $folderId ?>">

                <div class="modal-content p-3">

                    <h5>Upload File</h5>

                    <input type="file" name="file" class="form-control mb-3" required>

                    <button class="btn btn-primary w-100">Upload</button>

                </div>

            </form>
        </div>
    </div>

    <!-- CREATE SUB FOLDER -->
    <div class="modal fade" id="createSubFolderModal">
        <div class="modal-dialog">
            <form method="POST" action="create_folder.php">

                <input type="hidden" name="parent_id" value="<?= $folderId ?>">

                <div class="modal-content p-3">

                    <h5>Create Folder</h5>

                    <input type="text" name="folder_name" class="form-control mb-3" placeholder="Folder name" required>

                    <button class="btn btn-primary w-100">Create</button>

                </div>

            </form>
        </div>
    </div>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>