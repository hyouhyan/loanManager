# LoanManager
このアプリケーションは、個人間の貸し借りを簡単に管理するためのツールです。取引の登録や編集、削除、相手ごとの貸借履歴の確認ができ、リアルタイムで貸借の総額を計算します。

[2024年度 後期 Webプログラミング及び演習](https://github.com/hyouhyan/edu_2024-WebProgramming) の最終課題として作成  
PHPとSQLite3を使用した借金管理・共有アプリ

## 主な機能
- 取引の登録: 貸し借りの金額、相手、説明、日付を指定して記録可能。
- 履歴の確認: 過去の取引履歴を時系列で表示。
- 貸借の総額計算: 相手ごとの貸借残高を自動計算。
- 取引の編集と削除: 取引内容を後から変更・削除可能。
- シェア機能: 貸借状況を共有するためのリンクを生成。

## 仕様

### 技術スタック
- サーバーサイド：PHP
- データベース：SQLite3
- フロントエンド：HTML, CSS, Bootstrap

# 構成

## でーたべーす(SQLite)

### users

```
CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )
```

### transactions

```
CREATE TABLE transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            contact_id INTEGER NOT NULL,
            description TEXT NOT NULL,
            amount REAL NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE CASCADE
        )
```


### contacts

```
CREATE TABLE contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            share_code TEXT,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        )
```
