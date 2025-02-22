<?php
try {
    // データベース接続
    $db = new PDO('sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/db/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザーテーブル
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )
    ");

    // 連絡先テーブル
    $db->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            share_code TEXT,
            last_lent_date TEXT,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        )
    ");

    // last_lent_dateカラムが存在しない場合は追加
    $stmt = $db->prepare("PRAGMA table_info(contacts)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $lastLentDateExists = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'last_lent_date') {
            $lastLentDateExists = true;
            break;
        }
    }
    if (!$lastLentDateExists) {
        $db->exec("ALTER TABLE contacts ADD COLUMN last_lent_date TEXT");
    }

    // トランザクションテーブル
    $db->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            contact_id INTEGER NOT NULL,
            description TEXT NOT NULL,
            amount REAL NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE CASCADE
        )
    ");
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}


// ランダムコード生成関数
function generateRandomCode($length = 11) {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}