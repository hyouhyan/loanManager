<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/db/database.php';
require $_SERVER['DOCUMENT_ROOT'].'/header.php';

$contactId = $_GET['id'] ?? '';

if (empty($contactId)) {
    echo "<div class='alert alert-danger'>Invalid contact ID.</div>";
    exit;
}

// contact 情報を取得
$stmt = $db->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$contactId]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    echo "<div class='alert alert-danger'>Contact not found.</div>";
    exit;
}

// 編集フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';

    if (!empty($name)) {
        $stmt = $db->prepare("UPDATE contacts SET name = ? WHERE id = ?");
        $stmt->execute([$name, $contactId]);

        echo "<div class='alert alert-success'>Contact updated successfully.</div>";
        $contact['name'] = $name;
        header('Location: /index.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">取引先 編集</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($contact['name']) ?>" required>
        </div>
        <a href="/contact/delete_contact.php?id=<?= htmlspecialchars($contactId) ?>" class="btn btn-danger mb-2">削除</a>
        <br>
        <button type="submit" class="btn btn-primary">変更</button>
        <a href="/index.php" class="btn btn-secondary">キャンセル</a>
        <!-- 削除ボタン -->
    </form>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'].'/footer.php'; ?>
