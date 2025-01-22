<?php
require 'database.php';
require 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $password]);
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Username already taken.";
    }
}
?>

<h1 class="text-center">新規登録</h1>
<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
        <label for="username" class="form-label">ユーザーネーム</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">パスワード</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">登録</button>
    </div>
</form>

<?php require 'footer.php'; ?>
