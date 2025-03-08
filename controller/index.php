<?php
require_once 'includes/header.php';
require_once 'includes/utils.php';

// Tab configuration
$tabs = include 'includes/tabs.php';

// Get the current tab
$tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) ? $_GET['tab'] : 'dashboard';

// Alert system
if (!isset($_SESSION['alerts'])) {
    $_SESSION['alerts'] = [];
}

function addAlert($message, $type = 'info')
{
    foreach ($_SESSION['alerts'] as $alert) {
        if ($alert['message'] === $message && $alert['type'] === $type) {
            return; // Cancel if another alert with the same content already exists
        }
    }
    $_SESSION['alerts'][] = [
        'message' => $message,
        'type' => $type,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// POST Handlers
if (isset($_POST['dismissAlert'])) {
    $index = intval($_POST['dismissAlert']);
    unset($_SESSION['alerts'][$index]);
    $_SESSION['alerts'] = array_values($_SESSION['alerts']); // Reset array keys
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<style>
    /* Logout Confirm Modal Style */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: rgb(119, 119, 119);
        color: whitesmoke;
        margin: 15% auto;
        padding: 20px;
        border-radius: 5px;
        width: 30%;
        text-align: center;
    }

    .close {
        float: right;
        font-size: 28px;
        cursor: pointer;
    }

    .modal-buttons {
        margin-top: 20px;
    }

    .confirm-btn,
    .cancel-btn {
        padding: 10px 20px;
        margin: 5px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .confirm-btn {
        background-color: red;
        color: white;
    }

    .cancel-btn {
        background-color: rgb(49, 49, 49);
        color: white;
    }
</style>

<?php
require_once 'includes/classes/Database.php';
$db = new Database();
?>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fa fa-link"></i> FaceLink</h2>
        <p style="text-align:center;font-size:14px;">Controller UI</p>

        <!-- Alert system -->
        <?php if (!empty($_SESSION['alerts'])): ?>
            <div class="alerts">
                <?php foreach ($_SESSION['alerts'] as $index => $alert): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>">
                        <form method="post" style="display:inline;" autocomplete="off">
                            <button type="submit" name="dismissAlert" value="<?php echo $index; ?>" class="fa fa-check-circle" style="width:auto;padding:0;border:none;background:none;color:yellowgreen;font-size:20px;font-weight:bold;cursor:pointer;"></button>
                        </form>
                        <strong><?php echo strtoupper($alert['type']); ?>:</strong>
                        <?php echo htmlspecialchars($alert['message']); ?>
                        <br>
                        <small style="color: whitesmoke;">(<?php echo $alert['timestamp']; ?>)</small>
                    </div>
                    <br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr>

        <!-- Navigation -->
        <ul>
            <?php foreach ($tabs as $tab_name => $data): if ($data['internal'] != true) { ?>
                    <li class="<?php echo ($tab === $tab_name) ? 'active' : ''; ?>">
                        <a href="?tab=<?php echo $tab_name; ?>">
                            <i class="fa <?php echo $data['icon']; ?>"></i> <?php echo $data['title']; ?>
                        </a>
                    </li>
            <?php }
            endforeach; ?>
            <li><a href='#' onclick="openLogoutConfirm()"><i class='fa fa-sign-out'></i> Logout</a></li>
        </ul>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLogoutConfirm()">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button onclick="window.location.href='logout.php'" class="confirm-btn">Yes, Logout</button>
                <button onclick="closeLogoutConfirm()" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Modal Script -->
    <script>
        function openLogoutConfirm() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeLogoutConfirm() {
            document.getElementById("logoutModal").style.display = "none";
        }
    </script>


    <!-- Main Content -->
    <div class="main-content">
        <?php
        $tab_file = __DIR__ . "/includes/tabs/{$tab}.php";
        if (file_exists($tab_file)) {
            include $tab_file;
        } else {
            echo "<h2>Requested tab not found!</h2>";
        }
        ?>
    </div>
</div>