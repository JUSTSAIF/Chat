<?php require_once './API.php'; ?>

<html>
<header>
    <link rel='stylesheet' href='assets/index-style.css'>
    <meta name='refresh' content='5;url=onlines.php'>
    <meta http-equiv="refresh" content="5" />
</header>

<body>
    <div class='online-ifram'>
        <?php
        $users = GetOnlineUsers();
        if ($users != "You are not logged in") {
            foreach ($users as $user) {
                $username = $user['username'];
                $lastSeen = CustomizeDate($user['lastSeen'], true);
                if ($username == $_SESSION['username']) {
                    echo "<li class='me-online-color'> <p class='username'>$username <span class='tip-online-color'>*Me</span></p></li>";
                } else {
                    echo "<li> <p class='username'>$username </p><span>$lastSeen</span></li>";
                }
            }
        } else {
            http_response_code(400);
            echo '<center><h1 style="color:red">' . $users . '</h1></center>';
        }
        ?>
    </div>
</body>

</html>