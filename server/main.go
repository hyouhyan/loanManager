package main

import (
	"database/sql"
	"fmt"
	"net/http"

	"log"

	_ "github.com/mattn/go-sqlite3"
)

type HogeHandler struct{}
type ShowUserHandler struct{}

func (h *ShowUserHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	DbConnection, err := sql.Open("sqlite3", "./loanManager.db")
	if err != nil {
		log.Fatal(err)
	}

	cmd := "SELECT * FROM user"
	rows, _ := DbConnection.Query(cmd)
	defer rows.Close()

	var pp []User
	for rows.Next() {
		var p User
		err := rows.Scan(&p.id, &p.name, &p.email, &p.password) //アドレスを引数に渡すろstructにデータを入れてくれる
		if err != nil {
			log.Fatalln(err)
		}
		pp = append(pp, p)
	}
	for _, p := range pp {
		fmt.Fprintln(w, p.id, p.name, p.email, p.password)
	}
}

func (h *HogeHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, "hoge")
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
	hoge := HogeHandler{}
	showuser := ShowUserHandler{}

	server := http.Server{
		Addr:    ":8080",
		Handler: nil, // DefaultServeMux を使用
	}

	// DefaultServeMux にハンドラを付与
	http.Handle("/show/user", &showuser)
	http.Handle("/hoge", &hoge)

	fmt.Println("Listening on http://localhost:8080")
	server.ListenAndServe()
}
