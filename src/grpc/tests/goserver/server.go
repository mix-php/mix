package main

import (
	"context"
	"fmt"
	"google.golang.org/grpc"
	"net"
	"os"
	"os/signal"
	"strings"
	"syscall"
)

type SayService struct {
}

func (t *SayService) Hello(c context.Context, r *Request) (*Response, error) {
	resp := Response{
		Msg: fmt.Sprintf("hello, %s", r.GetName()),
	}
	return &resp, nil
}

func main() {
	// listen
	listener, err := net.Listen("tcp", ":9596")
	if err != nil {
		panic(err)
	}

	// signal
	ch := make(chan os.Signal)
	signal.Notify(ch, syscall.SIGHUP, syscall.SIGINT, syscall.SIGTERM)
	go func() {
		<-ch
		if err := listener.Close(); err != nil {
			panic(err)
		}
	}()

	// server
	s := grpc.NewServer()
	RegisterSayServer(s, &SayService{})

	// run
	if err := s.Serve(listener); err != nil && !strings.Contains(err.Error(), "use of closed network connection") {
		panic(err)
	}
}
