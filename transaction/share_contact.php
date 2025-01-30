<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/db/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /user/login.php');
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
    $newShareCode = generateRandomCode();
    $stmt = $db->prepare("UPDATE contacts SET share_code = ? WHERE id = ?");
    $stmt->execute([$newShareCode, $contactId]);
    $contact['share_code'] = $newShareCode; // 更新されたコードを反映
    // 再生成後に共有URLを再表示
    $shareCode = $newShareCode;
}

// 共有URLを表示
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
$shareUrl = "{$baseUrl}/share?code={$shareCode}";

require $_SERVER['DOCUMENT_ROOT'].'/header.php';
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">共有リンク生成</h1>

    <div class="card shadow-sm">
        <div class="card-body text-center">
            <h5 class="card-title">共有リンクが生成されました！</h5>
            <p class="card-text">以下のURLを共有することで、貸し借りの詳細を他の人と共有できます。</p>
            <div class="input-group">
                <input type="text" class="form-control" value="<?= htmlspecialchars($shareUrl) ?>" readonly>
                <button class="btn btn-primary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl) ?>')">Copy</button>
            </div>
            <p class="text-muted">リンクは一意であり、他の人がアクセス可能です。</p>

            <!-- 再生成ボタン (モーダルを開く) -->
            <button type="button" class="btn btn-danger mt-4" data-bs-toggle="modal" data-bs-target="#confirmModal">
                再生成
            </button>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/index.php" class="btn btn-primary">戻る</a>
    </div>
</div>

<!-- 確認モーダル -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">共有リンクの再生成</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            <div class="modal-body">
                新しい共有リンクを生成します。<br>
                <strong>古いリンクは使えなくなります。</strong><br>
                よろしいですか？
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <form method="POST">
                    <button type="submit" name="regenerate" class="btn btn-danger">はい、再生成する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'].'/footer.php'; ?>
