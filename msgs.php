<?php require_once './API.php'; ?>

<html>
<header>
    <link rel='stylesheet' href='assets/index-style.css'>
    <meta name='refresh' content='5;url=msgs.php'>
    <meta http-equiv="refresh" content="5" />
</header>

<body>
    <div class='message-wrap'>
        <br />
        <?php
        $messages = GetMessages();
        if ($messages != "You are not logged in") {
            foreach ($messages as $message) {
                $username =  $message['username'] == $_SESSION['username'] ? "Me" : $message['username'];
                $HandleText = HandleText($message['message']);
                $videosArr = $HandleText['vid'];
                $imagesArr = $HandleText['img'];
                $messageText = $HandleText['txt'];
                $time = strval(CustomizeDate($message['time']));
                $Rtime = strval($message['time']);
                $ColorMsgMe = $message['username'] == $_SESSION['username'] ? 'my-msg-color' : '';
                echo "<div class='message $ColorMsgMe'>
                <p>
                    <span class='time'  title='$Rtime'>$time</span>
                    <span class='s'>/</span>
                    <span class='username'>$username</span>
                    <span class='s'>:</span>
                    <span class='message-text'>$messageText</span>
                </p>
                </div>";
                if (count($videosArr) > 0) {
                    foreach ($videosArr as $video) {
                        echo "<video controls>
                            <source src='$video' type='video/mp4'>
                            Your browser does not support the video tag.
                        </video>";
                    }
                }
                if (count($imagesArr) > 0) {
                    foreach ($imagesArr as $image) {
                        echo "<img src='$image' alt='$image' />";
                    }
                }
            }
        } else {
            http_response_code(400);
            echo '<center><h1 style="color:red">' . $messages . '</h1></center>';
        }
        ?>
    </div>
</body>

</html>