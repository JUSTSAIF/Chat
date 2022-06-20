<?php
require_once './API.php';
$isLoggedIn = false;

if ($isLoggedIn) {
    header('Location: index.php');
    exit;
}

$msg = '';
if (isset($_POST['captcha']))
    if ($_POST['captcha'] == $_SESSION['captcha']) {
        $login = Login($_POST['username'], $_POST['password']);
        if ($login == "success") {
            header('Location: index.php');
            exit;
        } else {
            $msg = $login;
        }
    } else {
        $msg = '<span style="color:red">Failed Captcha !</span>';
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/login-style.css">
    <link rel="icon" href="assets/ico.ico">
</head>

<body>
    <a class="aboutBu" href="About.php">About</a>
    <br /><br /><br /><br />
    <center>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h1 class="title no-select"> ~ Welcome ~ </h1>
            <input type="text" placeholder="Username [1-8 Char]" name="username" required>
            <input type="password" placeholder="Password [6-25 Char]" name="password" required>
            <input type="hidden" name="flag" value="1" />
            <br />
            <img class="cap" src="captcha.php">
            <input type="text" name="captcha" placeholder="captcha" style="width: 155px;" required>
            <input type="submit" value="Login" name="submit" />
            <br />
            <?php echo $msg; ?>
        </form>
        <br /><br />
        <p class="no-select">
            <span style="color:#DA0003;">__________________ Rules __________________</span>
            <br /><br /><br />
            <span style="color:#7400FF;">Be Human !</span>
        </p>
    </center>
</body>

</html>