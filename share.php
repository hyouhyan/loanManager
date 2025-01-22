<?php
session_start();
require 'database.php';
require 'header.php';

$shareCode = $_GET['code'];

// share_code に基づいて contact を取得
$stmt = $db->prepare("SELECT * FROM contacts WHERE share_code = ?");
$stmt->execute([$shareCode]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    echo "Invalid share code.";
    exit;
}

// 貸し借り一覧を取得
$stmt = $db->prepare("
    SELECT description, amount, date
    FROM transactions
    ORDER BY date DESC
");
$transactions = $stmt->fetchAll();
?>

<h1 class="text-center">Transactions with <?= htmlspecialchars($contact['name']) ?></h1>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
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

<a href="index.php" class="btn btn-secondary">Back</a>

<?php require 'footer.php'; ?>
