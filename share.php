<?php
session_start();
require 'database.php';
require 'header.php';

$shareCode = $_GET['code'] ?? '';

if (empty($shareCode)) {
    echo "<div class='alert alert-danger'>Invalid share code.</div>";
    exit;
}

// share_code に基づいて contact を取得
$stmt = $db->prepare("SELECT * FROM contacts WHERE share_code = ?");
$stmt->execute([$shareCode]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    echo "<div class='alert alert-danger'>Invalid share code.</div>";
    exit;
}

// user_id に基づいてオーナー情報を取得
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$contact['user_id']]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

// 貸し借り一覧を取得
$stmt = $db->prepare("
    SELECT description, amount, date
    FROM transactions
    WHERE contact_id = ?
    ORDER BY date DESC
");
$stmt->execute([$contact['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 貸し借りの総額を計算
$stmt = $db->prepare("
    SELECT SUM(amount) AS total_balance
    FROM transactions
    WHERE contact_id = ?
");
$stmt->execute([$contact['id']]);
$totalBalance = $stmt->fetchColumn();

$totalBalance=-1*$totalBalance;
?>

<div class="container mt-5">
    <h1 class="text-center">
        <?= htmlspecialchars($owner['username'] ?? 'Unknown') ?> との取引
    </h1>
    <div class="text-center my-4">
        <h3>
            貸借総額: 
            <span class="<?= $totalBalance > 0 ? 'text-success' : ($totalBalance < 0 ? 'text-danger' : '') ?>">
                <?= htmlspecialchars($totalBalance) ?> 
            </span>円
        </h3>
    </div>
    <table class="table table-striped mt-4">
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
                    <td class="<?= $transaction['amount']*-1 < 0 ? 'table-danger' : 'table-success' ?>">
                        <?= htmlspecialchars($transaction['amount']*-1) ?>
                    </td>
                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require 'footer.php'; ?>
