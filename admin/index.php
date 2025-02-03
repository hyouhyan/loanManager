<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/db/database.php';
require $_SERVER['DOCUMENT_ROOT'].'/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

// 管理者以外はアクセス不可
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user['username'] !== 'admin') {
    header('Location: /');
    exit;
}

// SQLiteの全テーブル名を取得
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

?>

<h1 class="text-center">管理画面</h1>

<?php foreach ($tables as $table): ?>
    <h2 class="mt-4"><?= htmlspecialchars($table) ?></h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <?php
                    $columns = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($columns as $column) {
                        echo "<th>" . htmlspecialchars($column['name']) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $db->query("SELECT * FROM $table");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<a href="/" class="btn btn-secondary mt-4">戻る</a>

<?php require $_SERVER['DOCUMENT_ROOT'].'/footer.php'; ?>
