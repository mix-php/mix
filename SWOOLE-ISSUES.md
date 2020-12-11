## Swoole Issues

- [WebScoket] Coroutine\Http\Server shutdown 在 4.4.13 能实现关闭开启的全部连接，但在 4.4.13 ~ 4.4.14 中有 bug，会提示 Http\Response::close(): http response is unavailable 影响到 WebSocket 服务器无法 shutdown
- [WebScoket] Coroutine\Http\Response->close 在 4.4.8 才加入，能实现 websocket 关闭连接
- [WebScoket] Coroutine\Http\Client->send "incorrect mask flag" 在 4.4.13 才修复
- [ALL] Coroutine\Server $reuse_port 在 4.4.4 或更高版本中可用
- [HTTP] Coroutine\Http\Request->rawContent 在 4.4.1 才解决内存溢出问题
- [Micro] 4.5.0 才解决 http_compression = false 的问题 https://github.com/swoole/swoole-src/issues/3256
- [HTTP2] 4.5.0 才解决 open_http2_protocol Server Keep-Alive shutdown 问题 https://github.com/swoole/swoole-src/issues/2837#issuecomment-618308281
- [Signal] 发现 signal 之前执行 fopen 和其他文件操作，因为是串行，会导致 signal 失效，Swoole >= 4.5.3 已经解决该问题
- [Hook] >= 4.5.4 SWOOLE_HOOK_ALL 被修改为默认包含了 SWOOLE_HOOK_CURL
