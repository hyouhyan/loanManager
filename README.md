# LoanManager
[2024年度 後期 Webプログラミング及び演習](https://github.com/hyouhyan/edu_2024-WebProgramming) の最終課題として作成  
PHPとSQLite3を使用した借金管理・共有アプリ

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
