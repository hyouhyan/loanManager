package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"log"
)

func ShowLoan(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "SELECT * FROM loan"
	rows, _ := DbConnection.Query(cmd)
	defer rows.Close()

	var pp []Loan
	for rows.Next() {
		var p Loan
		err := rows.Scan(&p.id, &p.debtorId, &p.debtorIsCo, &p.creditorId, &p.creditorIsCo, &p.amount, &p.name)
		if err != nil {
			log.Panicln(err)
		}
		pp = append(pp, p)
	}
	for _, p := range pp {
		fmt.Fprintln(w, p.id, p.debtorId, p.debtorIsCo, p.creditorId, p.creditorIsCo, p.amount, p.name)
	}
}

// 誰かからお金を借りる
type RequestBodyBorrow struct {
	Email        string `json:"email"`
	Password     string `json:"password"`
	CreditorId   int    `json:"creditorId"`
	CreditorIsCo bool   `json:"creditorIsCo"`
	Amount       int    `json:"amount"`
	Name         string `json:"name"`
}

func Borrow(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	// リクエストボディをパース
	decoder := json.NewDecoder(r.Body)
	var req RequestBodyBorrow
	err = decoder.Decode(&req)
	if err != nil {
		log.Panic(err)
	}

	// ユーザー認証
	cmd := "SELECT * FROM user WHERE email = ? AND password = ?"
	rows, _ := DbConnection.Query(cmd, req.Email, req.Password)
	defer rows.Close()

	if !rows.Next() {
		http.Error(w, "EmailかPasswordが違います", http.StatusUnauthorized)
		return
	}

	// 自分の情報を取得
	var user User
	err = rows.Scan(&user.id, &user.name, &user.email, &user.password)
	if err != nil {
		log.Panic(err)
	}
	rows.Close()

	// お金を借りる
	cmd = "INSERT INTO loan (debtorId, debtorIsCo, creditorId, creditorIsCo, amount, name) VALUES (?, ?, ?, ?, ?, ?)"
	_, err = DbConnection.Exec(cmd, user.id, false, req.CreditorId, req.CreditorIsCo, req.Amount, req.Name)
	if err != nil {
		log.Panic(err)
	}

	fmt.Fprintln(w, "Borrowed successfully")
}

// 誰にお金を貸す
type RequestBodyRent struct {
	Email      string `json:"email"`
	Password   string `json:"password"`
	DebtorId   int    `json:"debtorId"`
	DebtorIsCo bool   `json:"debtorIsCo"`
	Amount     int    `json:"amount"`
	Name       string `json:"name"`
}

func Rent(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	// リクエストボディをパース
	decoder := json.NewDecoder(r.Body)
	var req RequestBodyRent
	err = decoder.Decode(&req)
	if err != nil {
		log.Panic(err)
	}

	// ユーザー認証
	cmd := "SELECT * FROM user WHERE email = ? AND password = ?"
	rows, _ := DbConnection.Query(cmd, req.Email, req.Password)
	defer rows.Close()

	if !rows.Next() {
		http.Error(w, "EmailかPasswordが違います", http.StatusUnauthorized)
		return
	}

	// 自分の情報を取得
	var user User
	err = rows.Scan(&user.id, &user.name, &user.email, &user.password)
	if err != nil {
		log.Panic(err)
	}
	rows.Close()

	// お金を貸す
	cmd = "INSERT INTO loan (debtorId, debtorIsCo, creditorId, creditorIsCo, amount, name) VALUES (?, ?, ?, ?, ?, ?)"
	_, err = DbConnection.Exec(cmd, req.DebtorId, req.DebtorIsCo, user.id, false, req.Amount, req.Name)
	if err != nil {
		log.Panic(err)
	}

	fmt.Fprintln(w, "Rented successfully")
}

type RequestBodyLoanDelete struct {
	Email    string `json:"email"`
	Password string `json:"password"`
	LoanId   int    `json:"loanId"`
}

func DeleteLoan(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	// リクエストボディをパース
	decoder := json.NewDecoder(r.Body)
	var req RequestBodyLoanDelete
	err = decoder.Decode(&req)
	if err != nil {
		log.Panic(err)
	}

	// ユーザー認証
	cmd := "SELECT * FROM user WHERE email = ? AND password = ?"
	rows, _ := DbConnection.Query(cmd, req.Email, req.Password)
	defer rows.Close()

	if !rows.Next() {
		http.Error(w, "EmailかPasswordが違います", http.StatusUnauthorized)
		return
	}

	// 自分の情報を取得
	var user User
	err = rows.Scan(&user.id, &user.name, &user.email, &user.password)
	if err != nil {
		log.Panic(err)
	}
	rows.Close()

	// idからローン情報を取得
	cmd = "SELECT * FROM loan WHERE id = ?"
	rows, _ = DbConnection.Query(cmd, req.LoanId)
	defer rows.Close()

	if !rows.Next() {
		http.Error(w, "指定されたローンが見つかりません", http.StatusNotFound)
		return
	}

	var loan Loan
	err = rows.Scan(&loan.id, &loan.debtorId, &loan.debtorIsCo, &loan.creditorId, &loan.creditorIsCo, &loan.amount, &loan.name)
	if err != nil {
		log.Panic(err)
	}
	rows.Close()

	// ローンに自分が関わっているか確認
	if loan.debtorId != user.id && loan.creditorId != user.id {
		http.Error(w, "指定されたローンに関わっていません", http.StatusUnauthorized)
		return
	}

	// ローンを削除
	cmd = "DELETE FROM loan WHERE id = ?"
	_, err = DbConnection.Exec(cmd, req.LoanId)
	if err != nil {
		log.Panic(err)
	}

	fmt.Fprintln(w, "Deleted successfully")
}
