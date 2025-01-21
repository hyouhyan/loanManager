<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $userId = $_SESSION['user_id'];

    $stmt = $db->prepare("INSERT INTO contacts (user_id, name) VALUES (?, ?)");
    $stmt->execute([$userId, $name]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Contact</title>
</head>
<body>
    <h1>Add Contact</h1>
    <form method="POST">
        <label for="name">Contact Name:</label>
        <input type="text" name="name" required>
        <button type="submit">Add</button>
    </form>
</body>
</html>
