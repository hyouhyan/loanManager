package main

import (
	"database/sql"
	"fmt"
	"net/http"

	"log"

	_ "github.com/mattn/go-sqlite3"
)

type HogeHandler struct{}
type FugaHandler struct{}

func (h *HogeHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
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

func (h *FugaHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, "fuga")
}

type User struct {
	id       int
	name     string
	email    string
	password string
}

func main() {
	hoge := HogeHandler{}
	fuga := FugaHandler{}

	server := http.Server{
		Addr:    ":8080",
		Handler: nil, // DefaultServeMux を使用
	}

	// DefaultServeMux にハンドラを付与
	http.Handle("/hoge", &hoge)
	http.Handle("/fuga", &fuga)

	server.ListenAndServe()
}
