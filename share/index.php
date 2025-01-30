<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/db/database.php';
require $_SERVER['DOCUMENT_ROOT'].'/header.php';

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
    ORDER BY date DESC, id DESC
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

<style>
    /* テーブルのデザイン */
    .transaction-table {
        display: table;
        width: 100%;
    }

    .transaction-card {
        display: none;
    }

    /* カードレイアウトのデザイン */
    .card {
        margin-bottom: 1rem;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        font-weight: bold;
    }

    /* 画面幅が768px以下の場合にカードレイアウトを表示し、テーブルを非表示にする */
    @media (max-width: 768px) {
        .transaction-table {
            display: none;
        }

        .transaction-card {
            display: block;
        }
    }
</style>

<div class="container mt-5">
    <h1 class="text-center">
        <?= htmlspecialchars($owner['username'] ?? 'Unknown') ?>と<?php echo htmlspecialchars($contact['name']); ?>の取引
    </h1>
    <div class="text-center my-4">
        <h3>
            貸借総額: 
            <span class="<?= $totalBalance > 0 ? 'text-success' : ($totalBalance < 0 ? 'text-danger' : '') ?>">
                <?= number_format(abs(htmlspecialchars($totalBalance))) ?> 
            </span>円
            <br>
        </h3>
        <!-- どっちがどっちに貸してるか明示的に表示する -->
        <div class="text-muted">
            (
            <?php if ($totalBalance < 0): ?>
                <?= htmlspecialchars($owner['username'] ?? 'Unknown') ?>が<?= htmlspecialchars($contact['name']) ?>に貸しています
            <?php elseif ($totalBalance > 0): ?>
                <?= htmlspecialchars($contact['name']) ?>が<?= htmlspecialchars($owner['username'] ?? 'Unknown') ?>に貸しています
            <?php else: ?>
                チャラになりました
            <?php endif; ?>
            )
        </div>
    </div>
    <table class="table table-striped mt-4 transaction-table">
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
                        <?= number_format(abs(htmlspecialchars($transaction['amount']*-1))) ?> 円
                        <?= $transaction['amount']*-1 > 0 ? '<span class="text-success">(貸し)</span>' : '<span class="text-danger">(借り)</span>' ?>
                    </td>
                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- カード形式 -->
    <div class="transaction-card">
        <?php foreach ($transactions as $transaction): ?>
            <div class="card <?= $transaction['amount']*-1 < 0 ? 'bg-light-red' : 'bg-light-green' ?>">
                <div class="card-header"><?= htmlspecialchars($transaction['description']) ?></div>
                <div class="card-body">
                    <p><strong>金額:</strong> 
                        <span class="<?= $transaction['amount']*-1 < 0 ? 'text-danger' : 'text-success' ?>">
                            <?= number_format(abs(htmlspecialchars($transaction['amount']*-1))) ?> 円
                            <?= $transaction['amount']*-1 > 0 ? '<span class="text-success">(貸し)</span>' : '<span class="text-danger">(借り)</span>' ?>
                        </span>
                    </p>
                    <p><strong>日付:</strong> <?= htmlspecialchars($transaction['date']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'].'/footer.php'; ?>
