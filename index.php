<?php
session_start();
require 'database.php';

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

<!DOCTYPE html>
<html>
<head>
    <title>Money Manager</title>
</head>
<body>
    <h1>Balance Summary</h1>
    <table>
        <thead>
            <tr>
                <th>Contact</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($balances as $balance): ?>
                <tr>
                    <td><?= htmlspecialchars($balance['name']) ?></td>
                    <td><?= htmlspecialchars($balance['balance']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
