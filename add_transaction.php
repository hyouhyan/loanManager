<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lender = $_POST['lender'];
    $borrower = $_POST['borrower'];
    $amount = $_POST['amount'];
    $date = date('Y-m-d');

    $stmt = $db->prepare("INSERT INTO transactions (lender, borrower, amount, date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$lender, $borrower, $amount, $date]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Add Transaction</title>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Add Transaction</h1>
    <form method="POST" action="add_transaction.php">
        <div class="mb-3">
            <label for="lender" class="form-label">Lender</label>
            <input type="text" id="lender" name="lender" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="borrower" class="form-label">Borrower</label>
            <input type="text" id="borrower" name="borrower" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">Add</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
