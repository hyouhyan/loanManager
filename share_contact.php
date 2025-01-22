<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$contactId = $_GET['contact_id'];

// ランダムなコードを生成
$shareCode = generateRandomCode();

// contacts テーブルにコードを保存
$stmt = $db->prepare("UPDATE contacts SET share_code = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$shareCode, $contactId, $userId]);

// 共有URLを表示
$baseUrl = "http://127.0.0.1";
$shareUrl = "{$baseUrl}/share/{$shareCode}";

require 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Share Contact</h1>

    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h5 class="card-title">共有リンクが生成されました！</h5>
            <p class="card-text">以下のURLを共有することで、貸し借りの詳細を他の人と共有できます。</p>
            <div class="alert alert-info" role="alert">
                <a href="<?= $shareUrl ?>" target="_blank" class="text-decoration-none"><?= $shareUrl ?></a>
            </div>
            <p class="text-muted">リンクは一意であり、他の人がアクセス可能です。</p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">戻る</a>
    </div>
</div>

<?php require 'footer.php'; ?>
