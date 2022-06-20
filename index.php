<?php
require_once './API.php';

if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

// Send Msg 
if (isset($_POST['msg']) && strlen($_POST['msg']) > 0) {
  $_SESSION['msg'] = $_POST['msg'];
  SendMessage($_POST['msg']);
}


?>
<html>
<head>
  <link rel="stylesheet" href="assets/index-style.css">
  <meta charset="UTF-8">
  <title>~ Chat</title>
  <link rel="icon" href="assets/ico.ico">
</head>

<body>
  <a class="aboutBu" href="About.php">About</a>
  <div class='inbox'>
    <div class="online">
      <h3 class="online-text no-select"> ~ Online</h3>
      <ul>
        <iframe height="100%" scrolling="no" frameborder="0" src="./onlines.php"></iframe>
      </ul>
    </div>
    <main>
      <iframe scrolling="no" width="100%" height="100%" frameborder="0" src="./msgs.php"></iframe>
      <footer>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          <input placeholder='Enter a message' type='text' name="msg" autofocus>
          <input type='submit' value='Send'>
        </form>
      </footer>
    </main>
  </div>
</body>

</html>