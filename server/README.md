# loanManager Backend

## 仕様

### user

- /user/add  
	```
	{
		"name":"name",
		"email":"mail@test.org",
		"password":"pass"
	}
	```
- /user/delete  
	```
	{
		"email":"mail@test.org",
		"password":"pass"
	}
	```

### co-user
- /co-user/add  
	```
	{
		"name":"name",
		"parentId":2
	}
	```
- /co-user/delete  
	```
	{
		"email":"mail@test.org",
		"password":"pass",
		"id":7
	}
	```

### loan
- /loan/borrow  
	```
	{
		"email":"mail@test.org",
		"password":"pass",
		"creditorId":2,
		"creditorIsCo":false,
		"amount":100,
		"name":"ジュース代"
	}
	```
- /loan/rent  
	{
		"email":"mail@test.org",
		"password":"pass",
		"debtorId":3,
		"debtorIsCo":false,
		"amount":100,
		"name":"ジュース代"
	}
- /loan/delete  
	```
	{
		"email":"mail@test.org",
		"password":"pass",
		"loanId":6
	}
	```

## DB構成

### テーブルuser
```
CREATE TABLE "user" (
	"id"	INTEGER NOT NULL UNIQUE,
	"name"	TEXT NOT NULL,
	"email"	TEXT NOT NULL UNIQUE,
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
	"debtorIsCo"	INTEGER NOT NULL,
	"creditorId"	INTEGER NOT NULL,
	"creditorIsCo"	INTEGER NOT NULL,
	"amount"	INTEGER NOT NULL,
	"name"	TEXT NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
```