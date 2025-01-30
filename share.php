<?php
// パラメータを保持して/shareにリダイレクト
header('Location: /share?code=' . $_GET['code']);
exit;