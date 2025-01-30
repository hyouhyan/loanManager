<?php
session_start();
require 'database.php';
require 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<h1 class="text-center">ログイン</h1>
<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST" class="transaction-container mx-auto">
    <div class="mb-3">
        <label for="username" class="form-label">ユーザーネーム</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">パスワード</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">ログイン</button>
    </div>
</form>

<?php require 'footer.php'; ?>
