package main

import (
	"database/sql"
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
