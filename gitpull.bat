@echo off
for %%i in (auth,bean,cache,concurrent,console,database,helper,http-message,http-server,http-session,log,pool,redis,server,socket,udp-server,validate,websocket,websocket-daemon,websocket-server) do (
    echo -- %%i
    cd vendor\mix\%%i
    git.exe pull -v --progress "origin"
    cd ../../../
)
