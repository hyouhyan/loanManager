<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// 全ての連絡先を取得
$stmt = $db->prepare("SELECT id, name FROM contacts WHERE user_id = ? AND owner = ?");
$stmt->execute([$userId, $userId]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactId = $_POST['contact_id'];
    $amount = $_POST['amount'];
    $description = !empty($_POST['description']) ? $_POST['description'] : 'No description';  // デフォルト値
    $date = date('Y-m-d H:i:s');

    $stmt = $db->prepare("INSERT INTO transactions (user_id, contact_id, description, amount, date, owner) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $contactId, $description, $amount, $date, $userId]);

    header('Location: index.php');
    exit;
}
?>

<h1 class="text-center">Add Transaction</h1>
<form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
        <label for="contact_id" class="form-label">Select Contact</label>
        <select name="contact_id" class="form-select" required>
            <option value="" disabled selected>Select a contact</option>
            <?php foreach ($contacts as $contact): ?>
                <option value="<?= htmlspecialchars($contact['id']) ?>">
                    <?= htmlspecialchars($contact['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <input type="text" name="description" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
        <small class="text-muted">Enter a positive amount if you lent money, or a negative amount if you borrowed money.</small>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-success">Add Transaction</button>
    </div>
</form>

<?php require 'footer.php'; ?>
