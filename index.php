<?php
session_start();
require '/db/database.php';
require '/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /user/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// ユーザーが所有する連絡先を取得
$stmt = $db->prepare("
    SELECT c.id, c.name, SUM(t.amount) AS balance
    FROM contacts c
    LEFT JOIN transactions t ON c.id = t.contact_id AND t.user_id = ?
    WHERE c.user_id = ?
    GROUP BY c.id
");
$stmt->execute([$userId, $userId]);
$balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="text-center">借金一覧</h1>

<div class="text-end mb-3">
    <a href="/contact/add_contact.php" class="btn btn-primary">
        <i class="bi bi-person-fill-add"></i> 取引先追加
    </a>
    <a href="/transaction/add_transaction.php" class="btn btn-primary">
        <i class="bi bi-cash-stack"></i> 借金追加
    </a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>取引先</th>
            <th>貸借総額</th>
            <th class="d-none d-md-block">最終取引</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($balances as $balance): ?>
            <tr>
                <td>
                    <a href="/transaction/contact_transactions.php?contact_id=<?= htmlspecialchars($balance['id']) ?>">
                        <?= htmlspecialchars($balance['name']) ?>
                    </a>
                </td>
                <?php if (htmlspecialchars($balance['balance'] ?? 0) < 0): ?>
                    <td class="bg-light-red">  <!-- 負の金額は赤 -->
                <?php elseif (htmlspecialchars($balance['balance'] ?? 0) > 0): ?>
                    <td class="bg-light-green">  <!-- 正の金額は緑 -->
                <?php else: ?>
                    <td>  <!-- 0円の場合はそのまま表示 -->
                <?php endif; ?>
                <?= number_format(htmlspecialchars($balance['balance'] ?? 0)) ?> 円</td>


                <?php
                // 最新の取引内容を取得
                $stmt = $db->prepare("
                    SELECT description, amount, date
                    FROM transactions
                    WHERE contact_id = ? AND user_id = ?
                    ORDER BY date DESC, id DESC LIMIT 1
                ");
                $stmt->execute([$balance['id'], $userId]);
                $latestTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <td class="d-none d-md-block">
                    <?php if ($latestTransaction): ?>
                        <?= htmlspecialchars($latestTransaction['description']) ?> (<?= number_format(htmlspecialchars($latestTransaction['amount'])) ?> 円)
                    <?php else: ?>
                        なし
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require '/footer.php'; ?>
