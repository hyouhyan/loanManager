<?php
function renderPage($content) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Loan Manager</title>
</head>
<body>
    <nav>
        <a href='index.php'>Home</a> |
        <a href='logout.php'>Logout</a>
    </nav>
    <hr>
    $content
</body>
</html>";
}
?>
