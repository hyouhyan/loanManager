<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactName = $_POST['name'];

    // 連絡先をデータベースに追加
    $stmt = $db->prepare("INSERT INTO contacts (user_id, name) VALUES (?, ?)");
    $stmt->execute([$userId, $contactName]);

    // 成功後、index.phpにリダイレクト
    header('Location: index.php');
    exit;
}
?>

<h1 class="text-center">取引先追加</h1>
<form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
        <label for="name" class="form-label">名前</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">追加</button>
    </div>
</form>

<?php require 'footer.php'; ?>
