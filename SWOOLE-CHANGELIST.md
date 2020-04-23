## Swoole Change List

已变更

- [WebScoket] Coroutine\Http\Server shutdown 在 4.4.13 能实现关闭开启的全部连接，但在 4.4.13 ~ 4.4.14 中有 bug，会提示 Http\Response::close(): http response is unavailable 影响到 WebSocket 服务器无法 shutdown
- [WebScoket] Coroutine\Http\Response->close 在 4.4.8 才加入，能实现 websocket 关闭连接
- [WebScoket] Coroutine\Http\Client->send "incorrect mask flag" 在 4.4.13 才修复
- [ALL] Coroutine\Server $reuse_port 在 4.4.4 或更高版本中可用
- [HTTP] Coroutine\Http\Request->rawContent 在 4.4.1 才解决内存溢出问题

待变更

- [HTTP] https://github.com/swoole/swoole-src/issues/3256
- [HTTP2] https://github.com/swoole/swoole-src/issues/2837#issuecomment-618308281
