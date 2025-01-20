package main

import (
	"fmt"
	"net/http"

	_ "github.com/mattn/go-sqlite3"
)

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

func Hoge(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, "hoge")
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
