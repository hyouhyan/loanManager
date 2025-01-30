<?php
session_start();
require '/db/database.php';
require '/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /user/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 全ての連絡先を取得
$stmt = $db->prepare("SELECT id, name FROM contacts WHERE user_id = ?");
$stmt->execute([$userId]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 今日の日付を取得
$today = date('Y-m-d');

// URLの`contact_id`パラメーターを取得
$selectedContactId = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactIds = $_POST['contact_ids']; // 複数の相手を受け取る
    $amount = $_POST['amount'];
    $description = !empty($_POST['description']) ? $_POST['description'] : 'No description'; // デフォルト値
    $date = !empty($_POST['date']) ? $_POST['date'] : $today; // 指定された日付または今日の日付を使用
    
    // 金額が正の数かチェック
    if ($amount <= 0) {
        echo "<p class='text-danger'>金額は正の数でなければなりません。</p>";
    } else {
        // 取引タイプを設定（貸す/借りる）
        if ($_POST['transaction_type'] === 'lend') {
            $amount = abs($amount); // 貸す場合は正の金額
        } elseif ($_POST['transaction_type'] === 'borrow') {
            $amount = -abs($amount); // 借りる場合は負の金額
        }

        // 各相手に対してトランザクションを追加
        foreach ($contactIds as $contactId) {
            $stmt = $db->prepare("INSERT INTO transactions (user_id, contact_id, description, amount, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $contactId, $description, $amount, $date]);
        }

        // 成功後、index.phpにリダイレクト
        header('Location: /index.php');
        exit;
    }
}
?>

<h1 class="text-center">取引追加</h1>
<form method="POST" class="transaction-container mx-auto">
    <div class="mb-3">
        <label for="contact_ids" class="form-label">相手</label>
        <select name="contact_ids[]" class="form-select" id="contact-select" required>
            <option value="" disabled <?= is_null($selectedContactId) ? 'selected' : '' ?>>相手を選択</option>
            <?php foreach ($contacts as $contact): ?>
                <option value="<?= htmlspecialchars($contact['id']) ?>" 
                    <?= $contact['id'] == $selectedContactId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($contact['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="multi-select-toggle">
            <label class="form-check-label" for="multi-select-toggle">複数人を選択する</label>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="amount" class="form-label">金額</label>
        <input type="number" step="1" name="amount" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">説明</label>
        <input type="text" name="description" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="date" class="form-label">日付</label>
        <!-- デフォルトで今日の日付を表示 -->
        <input type="date" name="date" class="form-control" value="<?= $today ?>" required>
    </div>

    <div class="mb-3 text-end">
        <button type="submit" name="transaction_type" value="lend" class="btn btn-success">貸す</button>
        <button type="submit" name="transaction_type" value="borrow" class="btn btn-danger">借りる</button>
    </div>

    <a href="/index.php" class="btn btn-secondary">戻る</a>
</form>

<script>
    // JavaScriptでチェックボックスの状態を監視
    document.getElementById('multi-select-toggle').addEventListener('change', function () {
        const select = document.getElementById('contact-select');
        if (this.checked) {
            select.setAttribute('multiple', 'multiple'); // 複数選択を許可
            select.removeAttribute('required'); // 必須属性を解除
        } else {
            select.removeAttribute('multiple'); // 複数選択を解除
            select.setAttribute('required', 'required'); // 必須属性を追加
        }
    });
</script>

<?php require '/footer.php'; ?>
