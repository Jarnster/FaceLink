<?php
require_once 'includes/init.php';

if (!is_controller_user_valid()) {
    header('Location: login.php');
    exit;
}
?>

<title>FaceLink - Controller UI</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/style.css">
<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<?php
if (isset($_SESSION['theme'])) {
    if ($_SESSION['theme'] == 'dark') {
        echo '<link rel="stylesheet" href="assets/themes/' . $_SESSION['theme'] . '.css">';
    }
}
?>