<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=chatDB;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



function isLoggedIn()
{
    if (isset($_SESSION['username']) && isset($_SESSION['lastSeen']) && isset($_SESSION['uid'])) {
        return true;
    } else {
        return false;
    }
}

function UpdateLastSeen()
{
    if (isLoggedIn()) {
        $dateTime = date('Y-m-d H:i:s');
        $uid = $_SESSION['uid'];
        $sql = "UPDATE users SET lastSeen = :lastSeen WHERE id = :uid";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->bindParam(':lastSeen', $dateTime);
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();
        return true;
    }
    return false;
}

function GetOnlineUsers()
{
    if (isLoggedIn()) {
        // get users who lastSeen > 5 minutes ago
        $dateTime = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $sql = "SELECT username,lastSeen FROM users WHERE lastSeen > :lastSeen";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->bindParam(':lastSeen', $dateTime);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }
    return "You are not logged in";
}

function Login($user, $pass)
{
    if (preg_match('/^[a-zA-Z0-9]{1,8}$/', $user)) {
        if (strlen($pass) > 6 && strlen($pass) < 25) {
            $pass = strval(md5($pass));
            // check if username exists
            $sql = "SELECT * FROM users WHERE username = :user";
            $stmt = $GLOBALS['pdo']->prepare($sql);
            $stmt->execute(array(':user' => $user));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dateTime = date('Y-m-d H:i:s');
            if ($result) {
                if ($result['password'] == $pass) {
                    $sql = "UPDATE users SET lastSeen = :lastSeen WHERE username = :user";
                    $stmt = $GLOBALS['pdo']->prepare($sql);
                    $stmt->bindParam(':lastSeen', $dateTime);
                    $stmt->bindParam(':user', $user);
                    $stmt->execute();
                } else {
                    return "Wrong password";
                }
            } else {
                // register new user
                $sql = "INSERT INTO users (username, password, lastSeen) VALUES (:user, :pass, :lastSeen)";
                $stmt = $GLOBALS['pdo']->prepare($sql);
                $stmt->bindParam(':user', $user);
                $stmt->bindParam(':pass', $pass);
                $stmt->bindParam(':lastSeen', $dateTime);
                $stmt->execute();
            }
            // save usr info in session
            $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE username = :user");
            $stmt->execute(array(':user' => $user));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $_SESSION['username'] = $user;
                $_SESSION['lastSeen'] = $dateTime;
                $_SESSION['uid'] =  $result['id'];
            }
            return "success";
        } else {
            return "Password must be between 6 and 25 characters";
        }
    }
    return "Invalid Username";
}

function SendMessage($msg)
{
    // check if user is logged in
    if (isLoggedIn()) {
        UpdateLastSeen();
        $dateTime = date('Y-m-d H:i:s');
        $uid = $_SESSION['uid'];
        $sql = "INSERT INTO messages (message, userId, time) VALUES (:msg, :uid, :time)";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->bindParam(':msg', $msg);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':time', $dateTime);
        $stmt->execute();
    } else {
        return "You are not logged in";
    }
}

function GetMessages()
{
    if (isLoggedIn()) {
        UpdateLastSeen();
        $sql = "SELECT * FROM messages ORDER BY time DESC";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) >= 600) {
            // delete all messages except last *SomeNumber
            $sql = "DELETE FROM messages WHERE id NOT IN (SELECT id FROM (SELECT id FROM messages ORDER BY time DESC LIMIT 150) AS t)";
            $stmt = $GLOBALS['pdo']->prepare($sql);
            $stmt->execute();
        }
        $sql = "SELECT username,id FROM users WHERE id IN (SELECT userId FROM messages)";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_column($users, 'username', 'id');
        $messages = [];
        foreach ($result as $message) {
            $messages[] = [
                'message' => $message['message'],
                'username' => @$users[@$message['userId']],
                'time' => $message['time']
            ];
        }
        return $messages;
    } else {
        return "You are not logged in";
    }
}

function CustomizeDate($date, $lsf = false)
{
    $date_ = date_create($date);
    $now = date_create();
    $diff = date_diff($date_, $now);
    if ($diff->y > 0) {
        return $diff->y . " Y ago";
    } else if ($diff->m > 0) {
        return $diff->m . " Mo ago";
    } else if ($diff->d > 0) {
        return $diff->d . " D ago";
    } else if ($diff->h > 0) {
        return $diff->h . " Hr ago";
    } else if ($diff->i > 0) {
        return $diff->i . " Min ago";
    } else {
        if ($lsf == true) {
            return "";
        }
        return "Just Now";
    }
}

function HandleText($txt)
{
    $images = [];
    $vid = [];
    // handle XSS attacks
    $txt = htmlspecialchars($txt);
    // check if there is a link in the text make it clickable 
    $txt = preg_replace('#(http|https|ftp|ftps)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?#i', '<a href="$1://$2$3" title="$1://$2$3" target="_blank">Link</a>', $txt);
    // enable emoji support
    $txt = preg_replace('/:([a-zA-Z0-9_]+):/s', '<img src="emoji/$1.png" alt="$1" />', $txt);
    return ["txt" => $txt, "img" => $images, "vid" => $vid];
}