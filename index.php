<?php
require_once 'auth.php';
require_once 'loan.php';
require_once 'render.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'register') {
            registerUser($_POST['name'], $_POST['email'], $_POST['password']);
            header('Location: index.php');
        } elseif ($_POST['action'] === 'login') {
            if (loginUser($_POST['email'], $_POST['password'])) {
                header('Location: index.php');
            } else {
                renderPage("<p>Login failed. Try again.</p>");
            }
        } elseif ($_POST['action'] === 'addLoan') {
            addLoan(
                $_POST['debtorId'],
                $_POST['debtorIsCo'],
                $_POST['creditorId'],
                $_POST['creditorIsCo'],
                $_POST['amount'],
                $_POST['name']
            );
            header('Location: index.php');
        }
    }
} elseif (isset($_SESSION['user_id'])) {
    $loans = getLoans($_SESSION['user_id']);
    $loanList = "<ul>";
    foreach ($loans as $loan) {
        $loanList .= "<li>{$loan['name']} - {$loan['amount']} (Debtor: {$loan['debtor']}, Creditor: {$loan['creditor']})</li>";
    }
    $loanList .= "</ul>";
    $content = "<h1>Welcome</h1>
    <h2>Your Loans</h2>
    $loanList
    <h2>Add Loan</h2>
    <form method='post'>
        <input type='hidden' name='action' value='addLoan'>
        Debtor ID: <input type='text' name='debtorId'><br>
        Debtor Is CoUser: <input type='checkbox' name='debtorIsCo' value='1'><br>
        Creditor ID: <input type='text' name='creditorId'><br>
        Creditor Is CoUser: <input type='checkbox' name='creditorIsCo' value='1'><br>
        Amount: <input type='number' name='amount'><br>
        Loan Name: <input type='text' name='name'><br>
        <button type='submit'>Add Loan</button>
    </form>";
    renderPage($content);
} else {
    $content = "<h1>Loan Manager</h1>
    <h2>Register</h2>
    <form method='post'>
        <input type='hidden' name='action' value='register'>
        Name: <input type='text' name='name'><br>
        Email: <input type='email' name='email'><br>
        Password: <input type='password' name='password'><br>
        <button type='submit'>Register</button>
    </form>
    <h2>Login</h2>
    <form method='post'>
        <input type='hidden' name='action' value='login'>
        Email: <input type='email' name='email'><br>
        Password: <input type='password' name='password'><br>
        <button type='submit'>Login</button>
    </form>";
    renderPage($content);
}
?>
