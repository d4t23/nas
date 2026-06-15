<?php
session_start();

require_once __DIR__ . '/../app/classes/Auth.php';
require_once __DIR__ . '/../app/classes/ConnectedAccounts.php';
require_once __DIR__ . '/../app/classes/FileManager.php';
require_once __DIR__ . '/../app/classes/FolderManager.php';

$auth = new Auth();
$auth->requireAuth();
$user = $auth->user();

$fileManager = new FileManager();
$folderManager = new FolderManager();
$conn = new ConnectedAccounts();

// Disconnect
if (isset($_GET['action']) && $_GET['action'] === 'disconnect' && isset($_GET['provider'])) {
    $provider = $_GET['provider'];
    $conn->disconnect($user['id'], $provider);

    header('Location: connected_accounts.php');
    exit;
}

$accounts = $conn->getByUser($user['id']);

$connected = [];
foreach ($accounts as $a) {
    $connected[$a['provider']] = $a;
}

function providerCard(string $provider, array $connected)
{
    $labels = [
        'google' => 'Google Drive',
        'dropbox' => 'Dropbox',
        'onedrive' => 'OneDrive',
        'icloud' => 'iCloud'
    ];

    $colors = [
        'google' => '#4285F4',
        'dropbox' => '#0061FF',
        'onedrive' => '#0078D4',
        'icloud' => '#7D7D7D'
    ];

    $logo = "../assets/images/{$provider}.png";

    $isConnected = isset($connected[$provider]);

    ob_start();
?>

    <div class="col-md-6 col-lg-4">
        <div class="provider-card">

            <div class="provider-top">
                <div class="provider-logo"
                    style="background: <?= $colors[$provider] ?>15;">
                    <img src="<?= $logo ?>" alt="<?= $labels[$provider] ?>">
                </div>

                <div class="provider-status">
                    <?php if ($isConnected): ?>
                        <span class="status connected">
                            <i class="fa-solid fa-circle-check"></i>
                            Connected
                        </span>
                    <?php else: ?>
                        <span class="status disconnected">
                            <i class="fa-solid fa-circle"></i>
                            Not Connected
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="provider-body">
                <h5><?= $labels[$provider] ?></h5>

                <p>
                    Connect your <?= $labels[$provider] ?>
                    account to import, sync and manage
                    your cloud files easily.
                </p>
            </div>

            <div class="provider-footer">
                <?php if ($isConnected): ?>
                    <a href="?action=disconnect&provider=<?= $provider ?>"
                        class="disconnect-btn">
                        Disconnect
                    </a>
                <?php else: ?>
                    <a href="oauth_start.php?provider=<?= $provider ?>"
                        class="connect-btn">
                        Connect Account
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php
    return ob_get_clean();
}
?>

<?php include __DIR__ . '/../components/navbar.php'; ?>
<?php include __DIR__ . '/../components/sidebar-modern.php'; ?>
<link rel=\"stylesheet\" href=\"../assets/css/acount.css\">
<link rel=\"stylesheet\" href=\"../assets/css/sidebar-modern.css\">
<main class="main-wrapper">

    <div class="top-navbar-space"></div>

    <div class="dashboard-body">

        <div class="container-fluid file-area connected-wrapper">

            <div class="connected-header">
                <h2>Connected Accounts</h2>

                <p>
                    Connect external cloud storage providers
                    to upload, sync and manage your files.
                </p>
            </div>

            <div class="row g-4">

                <?= providerCard('google', $connected) ?>

                <?= providerCard('dropbox', $connected) ?>

                <?= providerCard('onedrive', $connected) ?>

                <?= providerCard('icloud', $connected) ?>

            </div>

        </div>

    </div>

</main>