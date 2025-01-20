# loanManager Backend

## DB構成

### テーブルuser
```
CREATE TABLE "user" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"email"	TEXT NOT NULL,
	"password"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
)
```

### テーブルcoUser
```
CREATE TABLE "coUser" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"parentId"	INTEGER NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
)
```

### テーブルloan
```
CREATE TABLE "loan" (
	"id"	INTEGER NOT NULL UNIQUE,
	"debtorId"	INTEGER NOT NULL,
	"creditorId"	INTEGER NOT NULL,
	"amount"	INTEGER NOT NULL,
	"name"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
)
```