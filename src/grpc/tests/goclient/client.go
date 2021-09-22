package main

import (
	"context"
	"fmt"
	"google.golang.org/grpc"
	"time"
)

func main() {
	addr := ":9597"
	ctx, _ := context.WithTimeout(context.Background(), time.Duration(5)*time.Second)
	conn, err := grpc.DialContext(ctx, addr, grpc.WithInsecure(), grpc.WithBlock())
	if err != nil {
		panic(err)
	}
	defer func() {
		_ = conn.Close()
	}()
	cli := NewSayClient(conn)
	req := Request{
		Name: "xiaoming",
	}
	resp, err := cli.Hello(ctx, &req)
	if err != nil {
		panic(err)
	}
	fmt.Print(resp.GetMsg())
}
