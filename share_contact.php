<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$contactId = $_GET['contact_id'];

// contact 情報を取得
$stmt = $db->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$contactId]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

// 共有コードの生成（既存コードがなければ生成）
if (empty($contact['share_code'])) {
    // ランダムなコードを生成
    $shareCode = generateRandomCode();
    $stmt = $db->prepare("UPDATE contacts SET share_code = ? WHERE id = ?");
    $stmt->execute([$shareCode, $contactId]);
    $contact['share_code'] = $shareCode; // 更新されたコードを反映
}else{
    $shareCode = $contact['share_code'];
}

// 再生成ボタンが押された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regenerate'])) {
    $newShareCode = substr(md5(uniqid(mt_rand(), true)), 0, 11); // 新しい11桁のランダム英数字
    $stmt = $db->prepare("UPDATE contacts SET share_code = ? WHERE id = ?");
    $stmt->execute([$newShareCode, $contactId]);
    $contact['share_code'] = $newShareCode; // 更新されたコードを反映
}

// 共有URLを表示
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
$shareUrl = "{$baseUrl}/share.php?code={$shareCode}";

require 'header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Share Contact</h1>

    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h5 class="card-title">共有リンクが生成されました！</h5>
            <p class="card-text">以下のURLを共有することで、貸し借りの詳細を他の人と共有できます。</p>
            <div class="input-group">
                <input type="text" class="form-control" value="<?= htmlspecialchars($shareUrl) ?>" readonly>
                <button class="btn btn-primary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl) ?>')">Copy</button>
            </div>
            <p class="text-muted">リンクは一意であり、他の人がアクセス可能です。</p>
            <form method="POST" class="text-center mt-4">
                <button type="submit" name="regenerate" class="btn btn-danger">再生成</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">戻る</a>
    </div>
</div>

<?php require 'footer.php'; ?>
