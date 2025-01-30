<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/db/database.php';
require $_SERVER['DOCUMENT_ROOT'].'/header.php';

// ユーザーIDを取得
$userId = $_SESSION['user_id'] ?? '';

if (empty($userId)) {
    echo "<div class='alert alert-danger'>You must be logged in to edit your information.</div>";
    exit;
}

// ユーザー情報を取得
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit;
}

// 編集フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!empty($username) && !empty($password) && $password === $confirmPassword) {
        // パスワードをハッシュ化
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $hashedPassword, $userId]);

        echo "<div class='alert alert-success'>User information updated successfully.</div>";
        $user['username'] = $username;
    } else {
        echo "<div class='alert alert-danger'>Please fill in all fields and make sure passwords match.</div>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">ユーザー情報編集</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="username" class="form-label">ユーザーネーム</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">パスワード再入力</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">変更</button>
        <a href="index.php" class="btn btn-secondary">キャンセル</a>
    </form>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'].'footer.php'; ?>
