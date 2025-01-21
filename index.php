<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 全トランザクション取得
$stmt = $db->prepare("
    SELECT c.name, SUM(t.amount) AS balance
    FROM transactions t
    JOIN contacts c ON t.contact_id = c.id
    WHERE t.user_id = ?
    GROUP BY t.contact_id
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
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<?php require 'footer.php'; ?>
