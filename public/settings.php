<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/User.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';

$auth = new Auth();
$user = $auth->user();

$fileManager = new FileManager();
$folderManager = new FolderManager();

if (!$user) {
    header('Location: login.php');
    exit;
}

$message = '';
$userManager = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // UPLOAD PROFILE PICTURE
    if (
        isset($_POST['upload_picture']) &&
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK
    ) {

        $uploadDir = __DIR__ . '/../storage/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $uploadFile = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($_FILES['profile_picture']['type'], $allowedTypes)) {

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {

                // REMOVE OLD IMAGE
                if (
                    $user['profile_picture'] &&
                    file_exists(__DIR__ . '/../storage/profiles/' . $user['profile_picture'])
                ) {
                    unlink(__DIR__ . '/../storage/profiles/' . $user['profile_picture']);
                }

                $userManager->update([
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'profile_picture' => $fileName
                ]);

                $user['profile_picture'] = $fileName;

                $message = "Profile picture updated successfully!";
            }
        } else {
            $message = "Invalid image type!";
        }
    }


    // REMOVE PROFILE
    elseif (isset($_POST['remove_picture'])) {

        if (
            $user['profile_picture'] &&
            file_exists(__DIR__ . '/../storage/profiles/' . $user['profile_picture'])
        ) {
            unlink(__DIR__ . '/../storage/profiles/' . $user['profile_picture']);
        }

        $userManager->update([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'profile_picture' => null
        ]);

        $user['profile_picture'] = null;

        $message = "Profile picture removed!";
    }


    // UPDATE PROFILE
    elseif (isset($_POST['update_profile'])) {

        $userManager->update([
            'id' => $user['id'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'profile_picture' => $user['profile_picture']
        ]);

        $user['name'] = $_POST['name'];
        $user['email'] = $_POST['email'];

        $message = "Profile updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>GoCloud Account</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
        rel='stylesheet'>

    <link href="../assets/css/setting.css"
        rel="stylesheet">

</head>

<body class="settings-page-body">

    <!-- TOPBAR -->
    <header class="gocloud-topbar">

        <div class="topbar-left">
            <!-- MOBILE MENU BUTTON -->
            <button class="mobile-menu-btn" id="menuToggle">
                <i class='bx bx-menu'></i>
            </button>

            <div class="brand-box">

                <img src="../assets/images/logo.png"
                    alt="GoCloud Logo"
                    class="brand-logo">

                <h3 class="brand-title">
                    GoCloud Account
                </h3>

            </div>

        </div>


        <div class="topbar-right">

            <button class="top-icon-btn">
                <i class='bx bx-help-circle'></i>
            </button>

            <button class="top-icon-btn">
                <i class='bx bx-grid-alt'></i>
            </button>

            <?php if ($user['profile_picture']): ?>

                <img src="../storage/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                    class="top-profile-image"
                    alt="Profile">

            <?php else: ?>

                <div class="top-profile-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>

            <?php endif; ?>

        </div>

    </header>



    <!-- MAIN -->
    <div class="settings-layout">

        <!-- SIDEBAR -->
        <aside class="settings-sidebar">

            <a href="dashboard_new.php"
                class="sidebar-link active">

                <div class="sidebar-icon bg-primary-subtle">
                    <i class='bx bx-home'></i>
                </div>

                <span>Home</span>

            </a>


            <a href="#"
                class="sidebar-link">

                <div class="sidebar-icon bg-success-subtle">
                    <i class='bx bx-user'></i>
                </div>

                <span>Personal info</span>

            </a>


            <a href="#"
                class="sidebar-link">

                <div class="sidebar-icon bg-info-subtle">
                    <i class='bx bx-lock-alt'></i>
                </div>

                <span>Security</span>

            </a>


            <a href="#"
                class="sidebar-link">

                <div class="sidebar-icon bg-warning-subtle">
                    <i class='bx bx-data'></i>
                </div>

                <span>Storage</span>

            </a>


            <a href="#"
                class="sidebar-link">

                <div class="sidebar-icon bg-danger-subtle">
                    <i class='bx bx-share-alt'></i>
                </div>

                <span>Sharing</span>

            </a>


            <a href="logout.php"
                class="sidebar-link logout-link">

                <div class="sidebar-icon bg-danger-subtle">
                    <i class='bx bx-log-out'></i>
                </div>

                <span>Logout</span>

            </a>

        </aside>



        <!-- CONTENT -->
        <main class="settings-main-content">

            <!-- PROFILE -->
            <div class="profile-section">

                <div class="profile-image-wrapper">

                    <?php if ($user['profile_picture']): ?>

                        <img src="../storage/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                            alt="Profile"
                            class="main-profile-image">

                    <?php else: ?>

                        <div class="main-profile-avatar">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>

                    <?php endif; ?>


                    <label for="profile_picture"
                        class="camera-button">

                        <i class='bx bxs-camera'></i>

                    </label>

                </div>


                <h1 class="profile-name">
                    <?= htmlspecialchars($user['name']) ?>
                </h1>

                <p class="profile-email">
                    <?= htmlspecialchars($user['email']) ?>
                </p>

            </div>



            <!-- SEARCH -->
            <div class="search-box-wrapper">

                <i class='bx bx-search'></i>

                <input type="text"
                    placeholder="Search GoCloud Account">

            </div>



            <!-- QUICK BUTTONS -->
            <div class="quick-action-buttons">

                <button class="quick-btn">
                    My password
                </button>

                <button class="quick-btn">
                    Devices
                </button>

                <button class="quick-btn">
                    Password Manager
                </button>

                <button class="quick-btn">
                    My Activity
                </button>

                <button class="quick-btn">
                    Email
                </button>

            </div>



            <!-- INFO CARD -->
            <div class="gocloud-card-box">

                <div class="card-left">

                    <div class="cloud-icon-box">
                        <i class='bx bxs-cloud'></i>
                    </div>

                    <div>

                        <h5>
                            Secure your GoCloud account
                        </h5>

                        <p>
                            Manage your files, devices, storage and privacy settings safely.
                        </p>

                    </div>

                </div>


                <div class="card-actions">

                    <button class="btn btn-light">
                        Dismiss
                    </button>

                    <button class="btn btn-primary rounded-pill px-4">
                        Open Settings
                    </button>

                </div>

            </div>



            <!-- SETTINGS FORM -->
            <div class="settings-form-card">

                <h4 class="mb-4">
                    Profile Settings
                </h4>

                <?php if ($message): ?>

                    <div class="alert alert-info">
                        <?= htmlspecialchars($message) ?>
                    </div>

                <?php endif; ?>


                <!-- PROFILE IMAGE -->
                <form method="POST"
                    enctype="multipart/form-data">

                    <input type="file"
                        id="profile_picture"
                        name="profile_picture"
                        hidden
                        accept="image/*">


                    <div class="d-flex flex-wrap gap-3 mb-5">

                        <button type="submit"
                            name="upload_picture"
                            class="btn btn-dark rounded-pill px-4">

                            Upload Picture

                        </button>


                        <?php if ($user['profile_picture']): ?>

                            <button type="submit"
                                name="remove_picture"
                                class="btn btn-outline-danger rounded-pill px-4">

                                Remove Picture

                            </button>

                        <?php endif; ?>

                    </div>

                </form>



                <!-- UPDATE PROFILE -->
                <form method="POST">

                    <div class="row">

                        <div class="col-md-6 mb-4">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input type="text"
                                class="form-control modern-input"
                                name="name"
                                value="<?= htmlspecialchars($user['name']) ?>"
                                required>

                        </div>



                        <div class="col-md-6 mb-4">

                            <label class="form-label">
                                Email Address
                            </label>

                            <input type="email"
                                class="form-control modern-input"
                                name="email"
                                value="<?= htmlspecialchars($user['email']) ?>"
                                required>

                        </div>

                    </div>



                    <button type="submit"
                        name="update_profile"
                        class="btn btn-primary rounded-pill px-4">

                        Save Changes

                    </button>

                </form>

            </div>

        </main>

    </div>

    <script>
        const menuToggle = document.getElementById("menuToggle");
        const sidebar = document.querySelector(".settings-sidebar");

        menuToggle.addEventListener("click", () => {
            sidebar.classList.toggle("active");
            document.body.classList.toggle("sidebar-open");
        });

        // CLICK OUTSIDE TO CLOSE
        document.addEventListener("click", (e) => {

            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickMenuButton = menuToggle.contains(e.target);

            if (!isClickInsideSidebar && !isClickMenuButton) {

                sidebar.classList.remove("active");
                document.body.classList.remove("sidebar-open");

            }

        });
    </script>
</body>

</html>