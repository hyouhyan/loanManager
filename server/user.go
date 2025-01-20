package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"log"
)

func ShowUser(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "SELECT * FROM user"
	rows, _ := DbConnection.Query(cmd)
	defer rows.Close()

	var pp []User
	for rows.Next() {
		var p User
		err := rows.Scan(&p.id, &p.name, &p.email, &p.password)
		if err != nil {
			log.Panicln(err)
		}
		pp = append(pp, p)
	}
	for _, p := range pp {
		fmt.Fprintln(w, p.id, p.name, p.email, p.password)
	}
}

func ShowCoUser(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "SELECT * FROM coUser"
	rows, _ := DbConnection.Query(cmd)
	defer rows.Close()

	var pp []CoUser
	for rows.Next() {
		var p CoUser
		err := rows.Scan(&p.id, &p.name, &p.parentId)
		if err != nil {
			log.Panicln(err)
		}
		pp = append(pp, p)
	}
	for _, p := range pp {
		fmt.Fprintln(w, p.id, p.name, p.parentId)
	}
}

type RequestBodyNewUser struct {
	Name     string `json:"name"`
	Email    string `json:"email"`
	Password string `json:"password"`
}

func AddUser(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "POSTメソッドのみ受け付けています", http.StatusMethodNotAllowed)
		return
	}

	// リクエストボディの読み取り
	var requestBody RequestBodyNewUser
	err := json.NewDecoder(r.Body).Decode(&requestBody)
	if err != nil {
		http.Error(w, "リクエストの解析に失敗しました", http.StatusBadRequest)
		return
	}

	// データ検証
	if requestBody.Name == "" || requestBody.Email == "" || requestBody.Password == "" {
		http.Error(w, "なんか足りません", http.StatusBadRequest)
		return
	}

	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "INSERT INTO user (name, email, password) VALUES (?, ?, ?)"
	_, err = DbConnection.Exec(cmd, requestBody.Name, requestBody.Email, requestBody.Password)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}

	http.Error(w, "ユーザーを追加しました", http.StatusOK)
}

type RequestBodyNewCoUser struct {
	Name     string `json:"name"`
	ParentId int    `json:"parentId"`
}

func AddCoUser(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "POSTメソッドのみ受け付けています", http.StatusMethodNotAllowed)
		return
	}

	// リクエストボディの読み取り
	var requestBody RequestBodyNewCoUser
	err := json.NewDecoder(r.Body).Decode(&requestBody)
	if err != nil {
		http.Error(w, "リクエストの解析に失敗しました", http.StatusBadRequest)
		return
	}

	// データ検証
	if requestBody.Name == "" || requestBody.ParentId == 0 {
		http.Error(w, "なんか足りません", http.StatusBadRequest)
		return
	}

	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "INSERT INTO coUser (name, parentId) VALUES (?, ?)"
	_, err = DbConnection.Exec(cmd, requestBody.Name, requestBody.ParentId)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}

	http.Error(w, "Coユーザーを追加しました", http.StatusOK)
}

type RequestBodyDeleteUser struct {
	Email    string `json:"email"`
	Password string `json:"password"`
}

func DeleteUser(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "POSTメソッドのみ受け付けています", http.StatusMethodNotAllowed)
		return
	}

	// リクエストボディの読み取り
	var requestBody RequestBodyDeleteUser
	err := json.NewDecoder(r.Body).Decode(&requestBody)
	if err != nil {
		http.Error(w, "リクエストの解析に失敗しました", http.StatusBadRequest)
		return
	}

	// データ検証
	if requestBody.Email == "" || requestBody.Password == "" {
		http.Error(w, "なんか足りません", http.StatusBadRequest)
		return
	}

	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	cmd := "DELETE FROM user WHERE email = ? AND password = ?"
	_, err = DbConnection.Exec(cmd, requestBody.Email, requestBody.Password)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}

	http.Error(w, "ユーザーを削除しました", http.StatusOK)
}

type RequestBodyDeleteCoUser struct {
	Id       int    `json:"id"`
	Email    string `json:"email"`
	Password string `json:"password"`
}

func DeleteCoUser(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "POSTメソッドのみ受け付けています", http.StatusMethodNotAllowed)
		return
	}

	// リクエストボディの読み取り
	var requestBody RequestBodyDeleteCoUser
	err := json.NewDecoder(r.Body).Decode(&requestBody)
	if err != nil {
		http.Error(w, "リクエストの解析に失敗しました", http.StatusBadRequest)
		return
	}

	// データ検証
	if requestBody.Email == "" || requestBody.Password == "" {
		http.Error(w, "なんか足りません", http.StatusBadRequest)
		return
	}

	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}
	defer DbConnection.Close()

	// email と password が一致するか確認
	cmd := "SELECT * FROM user WHERE email = ? AND password = ?"
	rows, _ := DbConnection.Query(cmd, requestBody.Email, requestBody.Password)
	// 一致しない場合
	if !rows.Next() {
		http.Error(w, "email または password が違います", http.StatusBadRequest)
		return
	}
	defer rows.Close()
	// ユーザーの id を取得
	var user User
	err = rows.Scan(&user.id, &user.name, &user.email, &user.password)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}
	rows.Close()

	// ユーザーが CoUser に存在するか確認
	cmd = "SELECT * FROM coUser WHERE id = ?"
	rows, _ = DbConnection.Query(cmd, requestBody.Id)
	// 存在しない場合
	if !rows.Next() {
		http.Error(w, "Coユーザーが存在しません", http.StatusBadRequest)
		return
	}
	defer rows.Close()
	// CoUser の親 id が一致するか確認
	var couser CoUser
	err = rows.Scan(&couser.id, &couser.name, &couser.parentId)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}
	if user.id != couser.parentId {
		http.Error(w, "Coユーザーの親 id が違います", http.StatusBadRequest)
		return
	}
	rows.Close()

	cmd = "DELETE FROM coUser WHERE id = ?"
	_, err = DbConnection.Exec(cmd, requestBody.Id)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}

	http.Error(w, "Coユーザーを削除しました", http.StatusOK)
}
