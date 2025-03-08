<?php
session_start();
require_once 'includes/classes/Database.php';
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['login'])) {
        $username = strtolower(htmlspecialchars($_POST['username']));
        $password = htmlspecialchars($_POST['password']);

        if (htmlspecialchars($_POST['dark_theme']) == "on") {
            $_SESSION['theme'] = "dark"; // Dark theme
        } else {
            // $_SESSION['theme'] = "light"; // Default theme
        }

        $user = $db->getUserRow($username);

        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: login.php?msg=userfail');
            exit("No user or incorrect password");
        }

        $_SESSION['user_id'] = $user['id'];
        session_regenerate_id(true);
        header("Location: index.php");
        exit("Login successful");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FaceLink Controller UI</title>
    <?php
    if(isset($_GET['msg']))
    {
        if($_GET['msg'] == 'userfail')
        {
            echo "<b style='color: yellow;'>Login Failed <br> User is invalid or password not correct</b>";
        }
    }
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        body {
            font-family: Helvetica, sans-serif;
            background-color: rgba(32, 127, 235, 0.75);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            color: rgba(32, 127, 235, 0.75);
            margin-bottom: 20px;
        }

        .input-field {
            width: 95%;
            padding: 14px;
            text-align: center;
            font-size: 18px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: rgba(32, 127, 235, 0.75);
            font-size: 18px;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="icon">
            <i class="fas fa-lock"></i>
        </div>

        <h1><i class="fa fa-link"></i> FaceLink</h1>
        <h1>Controller UI</h1>

        <form method="post" autocomplete="off">
            <label for="username"><i class="fa fa-user"></i> Username</label>
            <input type="text" name="username" id="username" class="input-field" placeholder="Username" required>
            <br><br>
            <label for="password"><i class="fa fa-key"></i> Password</label>
            <input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
            <br><br>
            <div class="theme-toggle">
                <i class="fa fa-moon"></i> Use dark theme
                <label class="switch">
                    <input type="checkbox" name="dark_theme" id="dark_theme" checked>
                    <span class="slider round"></span>
                </label>
            </div>
            <br>
            <button type="submit" class="submit-btn" name="login"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
    </div>
</body>

</html>