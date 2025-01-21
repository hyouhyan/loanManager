<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// ユーザーごとの貸し借り情報を取得
$stmt = $db->prepare("
    SELECT c.id, c.name, SUM(t.amount) AS balance
    FROM contacts c
    LEFT JOIN transactions t ON c.id = t.contact_id AND t.user_id = ?
    GROUP BY c.id
");
$stmt->execute([$userId]);
$balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="text-center">Balance Summary</h1>

<div class="text-end mb-3">
    <a href="add_transaction.php" class="btn btn-primary">Add Transaction</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Contact</th>
            <th>Balance</th>
            <th>Latest Transaction</th>
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
                <td><?= htmlspecialchars($balance['balance']) ?></td>

                <?php
                // 最新の取引内容を取得
                $stmt = $db->prepare("
                    SELECT description, amount, date
                    FROM transactions
                    WHERE contact_id = ? AND user_id = ?
                    ORDER BY date DESC LIMIT 1
                ");
                $stmt->execute([$balance['id'], $userId]);
                $latestTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <td>
                    <?php if ($latestTransaction): ?>
                        <?= htmlspecialchars($latestTransaction['description']) ?> (<?= htmlspecialchars($latestTransaction['amount']) ?>) on <?= htmlspecialchars($latestTransaction['date']) ?>
                    <?php else: ?>
                        No transactions
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
