<?php
session_start();
require 'database.php';
require 'header.php';

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

<h1 class="text-center">Add Contact</h1>
<form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
        <label for="name" class="form-label">Contact Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-success">Add</button>
    </div>
</form>

<?php require 'footer.php'; ?>
