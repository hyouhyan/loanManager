<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$contactId = $_GET['contact_id'] ?? null;

if (!$contactId) {
    header('Location: index.php');
    exit;
}

// 指定されたコンタクトの情報を取得
$stmt = $db->prepare("SELECT name FROM contacts WHERE id = ? AND user_id = ?");
$stmt->execute([$contactId, $userId]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    header('Location: index.php');
    exit;
}

// 貸し借り一覧を取得
$stmt = $db->prepare("
    SELECT description, amount, date
    FROM transactions
    WHERE user_id = ? AND contact_id = ?
    ORDER BY date DESC
");
$stmt->execute([$userId, $contactId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 貸し借りの総額を計算
$stmt = $db->prepare("
    SELECT SUM(amount) AS total_balance
    FROM transactions
    WHERE contact_id = ?
");
$stmt->execute([$contactId]);
$totalBalance = $stmt->fetchColumn();

// 完済処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_balance'])) {
    $stmt = $db->prepare("
        INSERT INTO transactions (user_id, contact_id, description, amount, date, owner)
        VALUES (?, ?, '全額清算', ?, DATE('now'), ?)
    ");
    $stmt->execute([$userId, $contactId, -$totalBalance, $userId]);

    header("Location: contact_transaction.php?contact_id=$contactId");
    exit;
}
?>

<h1 class="text-center"><?= htmlspecialchars($contact['name']) ?> との取引</h1>
<div class="text-center my-4">
    <h3>
        貸借総額: 
        <span class="<?= $totalBalance > 0 ? 'text-success' : ($totalBalance < 0 ? 'text-danger' : '') ?>">
            <?= htmlspecialchars($totalBalance) ?> 
        </span>円
    </h3>
    <a class="btn btn-secondary" href="share_contact.php?contact_id=<?= $contactId ?>">
        <i class="bi bi-share-fill"></i>
        共有
    </a>
    <a class="btn btn-secondary" href="edit_contact.php?id=<?= $contactId ?>">
        <i class="bi bi-pencil-square"></i>
        編集
    </a>
    <!-- 完済ボタン -->
    <?php if ($totalBalance != 0): ?>
        <form method="POST" class="d-inline">
            <button type="submit" name="clear_balance" class="btn btn-danger">
                <i class="bi bi-x-circle-fill"></i> 全て完済する
            </button>
        </form>
    <?php endif; ?>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>説明</th>
            <th>金額</th>
            <th>日付</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?= htmlspecialchars($transaction['description']) ?></td>
                <td class="<?= $transaction['amount'] < 0 ? 'bg-light-red' : 'bg-light-green' ?>"><?= htmlspecialchars($transaction['amount']) ?></td>
                <td><?= htmlspecialchars($transaction['date']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php" class="btn btn-secondary">戻る</a>

<?php require 'footer.php'; ?>
