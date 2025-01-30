<?php
session_start();
// Destroy the session to log out the user
session_destroy();
header('Location: index.php');
exit();
?>