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
    SELECT id, description, amount, date
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
        INSERT INTO transactions (user_id, contact_id, description, amount, date)
        VALUES (?, ?, '全額清算', ?, DATE('now'))
    ");
    $stmt->execute([$userId, $contactId, -$totalBalance]);

    header("Location: contact_transactions.php?contact_id=$contactId");
    exit;
}

// 削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_transaction'])) {
    $transactionId = $_POST['transaction_id'];
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, $userId]);
    header("Location: contact_transactions.php?contact_id=$contactId");
    exit;
}

// 編集処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_transaction'])) {
    $transactionId = $_POST['transaction_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $db->prepare("
        UPDATE transactions
        SET description = ?, amount = ?, date = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$description, $amount, $date, $transactionId, $userId]);

    header("Location: contact_transactions.php?contact_id=$contactId");
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
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal">
            <i class="bi bi-x-circle-fill"></i> 全て完済する
        </button>
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
                <td>
                    <!-- 編集ボタン -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal-<?= $transaction['id'] ?>">
                        <i class="bi bi-pencil-square"></i> 編集
                    </button>

                    <!-- 編集モーダル -->
                    <div class="modal fade" id="editModal-<?= $transaction['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel-<?= $transaction['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel-<?= $transaction['id'] ?>">取引の編集</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="description-<?= $transaction['id'] ?>" class="form-label">説明</label>
                                            <input type="text" name="description" id="description-<?= $transaction['id'] ?>" class="form-control" value="<?= htmlspecialchars($transaction['description']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="amount-<?= $transaction['id'] ?>" class="form-label">金額</label>
                                            <input type="number" name="amount" id="amount-<?= $transaction['id'] ?>" class="form-control" value="<?= htmlspecialchars($transaction['amount']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date-<?= $transaction['id'] ?>" class="form-label">日付</label>
                                            <input type="date" name="date" id="date-<?= $transaction['id'] ?>" class="form-control" value="<?= htmlspecialchars($transaction['date']) ?>" required>
                                        </div>
                                        <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-<?= $transaction['id'] ?>">
                                            <i class="bi bi-trash"></i> 取引を削除
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                        <button type="submit" name="edit_transaction" class="btn btn-primary">保存</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- 削除モーダル -->
                    <div class="modal fade" id="deleteModal-<?= $transaction['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel-<?= $transaction['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel-<?= $transaction['id'] ?>">取引の削除</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    本当にこの取引を削除しますか？<br>
                                    <strong>説明:</strong> <?= htmlspecialchars($transaction['description']) ?><br>
                                    <strong>金額:</strong> <?= htmlspecialchars($transaction['amount']) ?><br>
                                    <strong>日付:</strong> <?= htmlspecialchars($transaction['date']) ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                        <button type="submit" name="delete_transaction" class="btn btn-danger">削除する</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php" class="btn btn-secondary">戻る</a>

<!-- 取引追加ボタン -->
<a href="add_transaction.php?contact_id=<?= $contactId ?>" class="add-transaction-btn btn btn-primary">
    <i class="bi bi-plus"></i>
</a>

<!-- 完済モーダル -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">確認</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                貸借総額 <strong><?= htmlspecialchars($totalBalance) ?>円</strong> を全て完済しますか？
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <form method="POST" class="d-inline">
                    <button type="submit" name="clear_balance" class="btn btn-danger">完済する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.add-transaction-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1030;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.add-transaction-btn i {
    font-size: 1.5rem;
}
</style>

<?php require 'footer.php'; ?>
