<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// ユーザーが所有する連絡先を取得
$stmt = $db->prepare("
    SELECT c.id, c.name, SUM(t.amount) AS balance
    FROM contacts c
    LEFT JOIN transactions t ON c.id = t.contact_id AND t.user_id = ?
    WHERE c.owner = ?
    GROUP BY c.id
");
$stmt->execute([$userId, $userId]);
$balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="text-center">借金一覧</h1>

<div class="text-end mb-3">
    <a href="add_transaction.php" class="btn btn-primary">借金追加</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>取引先</th>
            <th>貸借総額</th>
            <th>最終取引</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($balances as $balance): ?>
            <tr>
                <td>
                    <a href="contact_transactions.php?contact_id=<?= htmlspecialchars($balance['id']) ?>">
                        <?= htmlspecialchars($balance['name']) ?>
                    </a>
                </td>
                <?php if (htmlspecialchars($balance['balance'] ?? 0) < 0): ?>
                    <td class="bg-light-red"><?= htmlspecialchars($balance['balance'] ?? 0) ?> 円</td>  <!-- 負の金額は赤 -->
                <?php elseif (htmlspecialchars($balance['balance'] ?? 0) > 0): ?>
                    <td class="bg-light-green"><?= htmlspecialchars($balance['balance'] ?? 0) ?> 円</td>  <!-- 正の金額は緑 -->
                <?php else: ?>
                    <td><?= htmlspecialchars($balance['balance'] ?? 0) ?> 円</td>  <!-- 0円の場合はそのまま表示 -->
                <?php endif; ?>


                <?php
                // 最新の取引内容を取得
                $stmt = $db->prepare("
                    SELECT description, amount, date
                    FROM transactions
                    WHERE contact_id = ? AND user_id = ? AND owner = ?
                    ORDER BY date DESC LIMIT 1
                ");
                $stmt->execute([$balance['id'], $userId, $userId]);
                $latestTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <td>
                    <?php if ($latestTransaction): ?>
                        <?= htmlspecialchars($latestTransaction['description']) ?> (<?= htmlspecialchars($latestTransaction['amount']) ?> 円)
                    <?php else: ?>
                        No transactions
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
