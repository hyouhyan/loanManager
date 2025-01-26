<?php
session_start();
require 'database.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['multiple']) && $_POST['multiple'] === '1') {
        // 複数人の場合
        $contactNames = $_POST['names']; // 改行区切りの名前リスト
        $contactList = array_filter(array_map('trim', explode("\n", $contactNames))); // 改行で分割し、空行を削除

        foreach ($contactList as $contactName) {
            // 連絡先をデータベースに追加
            $stmt = $db->prepare("INSERT INTO contacts (user_id, name) VALUES (?, ?)");
            $stmt->execute([$userId, $contactName]);
        }
    } else {
        // 単一の名前の場合
        $contactName = $_POST['name'];

        // 連絡先をデータベースに追加
        $stmt = $db->prepare("INSERT INTO contacts (user_id, name) VALUES (?, ?)");
        $stmt->execute([$userId, $contactName]);
    }

    // 成功後、index.phpにリダイレクト
    header('Location: index.php');
    exit;
}
?>

<h1 class="text-center">取引先追加</h1>
<form method="POST" class="w-50 mx-auto">
    <div class="mb-3">
        <label for="name" class="form-label">名前</label>
        <!-- 単一の名前入力フィールド -->
        <input type="text" name="name" class="form-control" id="singleNameField" required>
    </div>

    <!-- 複数人入力用テキストエリア（初期状態は非表示） -->
    <div class="mb-3" id="multipleNamesField" style="display: none;">
        <small class="text-muted">複数人の場合は改行区切りで入力してください</small>
        <textarea name="names" class="form-control" rows="5"></textarea>
    </div>

    <!-- チェックボックスで切り替え -->
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="multipleToggle" name="multiple" value="1">
        <label class="form-check-label" for="multipleToggle">複数人を追加する</label>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">追加</button>
    </div>
</form>

<script>
    const multipleToggle = document.getElementById('multipleToggle');
    const singleNameField = document.getElementById('singleNameField');
    const multipleNamesField = document.getElementById('multipleNamesField');

    // チェックボックスの状態に応じてフィールドを切り替える
    multipleToggle.addEventListener('change', function () {
        if (this.checked) {
            singleNameField.style.display = 'none';
            singleNameField.required = false; // 必須解除
            multipleNamesField.style.display = 'block';
        } else {
            singleNameField.style.display = 'block';
            singleNameField.required = true; // 必須設定
            multipleNamesField.style.display = 'none';
        }
    });
</script>

<?php require 'footer.php'; ?>
