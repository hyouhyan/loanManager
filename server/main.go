package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"log"

	_ "github.com/mattn/go-sqlite3"
)

func ShowUser(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Panic(err)
	}

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

func Hoge(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, "hoge")
}

type RequestBody struct {
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
	var requestBody RequestBody
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

	cmd := "INSERT INTO user (name, email, password) VALUES (?, ?, ?)"
	_, err = DbConnection.Exec(cmd, requestBody.Name, requestBody.Email, requestBody.Password)
	if err != nil {
		http.Error(w, "なんかエラーです", http.StatusBadRequest)
		fmt.Println(err)
		return
	}

	http.Error(w, "ユーザーを追加しました", http.StatusOK)
}

type User struct {
	id       int
	name     string
	email    string
	password string
}

type CoUser struct {
	id       int
	name     string
	parentId int
}

type Loan struct {
	id         int
	debtorId   int
	creditorId int
	amount     int
	name       string
}

func main() {
	server := http.Server{
		Addr:    ":8080",
		Handler: nil, // DefaultServeMux を使用
	}

	// DefaultServeMux にハンドラを付与
	http.HandleFunc("/show/user", ShowUser)
	http.HandleFunc("/show/co-user", ShowCoUser)
	http.HandleFunc("/hoge", Hoge)

	http.HandleFunc("/user/add", AddUser)

	fmt.Println("Listening on http://localhost:8080")
	server.ListenAndServe()
}
