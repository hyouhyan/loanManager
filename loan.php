<?php
require_once 'db.php';

function addLoan($debtorId, $debtorIsCo, $creditorId, $creditorIsCo, $amount, $name) {
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO loan (debtorId, debtorIsCo, creditorId, creditorIsCo, amount, name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$debtorId, $debtorIsCo, $creditorId, $creditorIsCo, $amount, $name]);
}

function getLoans($userId) {
    $db = getDbConnection();
    $stmt = $db->prepare(
        "SELECT l.id, l.amount, l.name, d.name AS debtor, c.name AS creditor
        FROM loan l
        LEFT JOIN user d ON l.debtorId = d.id AND l.debtorIsCo = 0
        LEFT JOIN user c ON l.creditorId = c.id AND l.creditorIsCo = 0
        WHERE l.debtorId = ? OR l.creditorId = ?"
    );
    $stmt->execute([$userId, $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>