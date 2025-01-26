<?php
session_start();
require 'database.php';
require 'header.php';

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

// 削除確認後の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // トランザクションを開始
        $db->beginTransaction();
        try {
            // 紐づけられた transactions を削除
            $stmt = $db->prepare("DELETE FROM transactions WHERE contact_id = ?");
            $stmt->execute([$contactId]);

            // contact を削除
            $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$contactId]);

            // コミット
            $db->commit();
            echo "<div class='alert alert-success'>Contact and related transactions deleted successfully.</div>";
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            // エラー時はロールバック
            $db->rollBack();
            echo "<div class='alert alert-danger'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        // キャンセル時
        header('Location: index.php');
        exit;
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">取引先 削除</h1>
    <p class="text-center">本当に「<?= htmlspecialchars($contact['name']) ?>」とその取引を削除しますか？</p>
    <form method="POST" class="text-center mt-4">
        <button type="submit" name="confirm" value="yes" class="btn btn-danger">はい</button>
        <a href="index.php" class="btn btn-secondary">いいえ</a>
    </form>
</div>

<?php require 'footer.php'; ?>
