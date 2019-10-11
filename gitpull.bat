@echo off
for %%i in (auth,bean,cache,concurrent,console,database,event,helper,http-message,http-server,log,pool,redis,route,server,session,udp-server,validate,view,websocket) do (
    echo -- %%i
    cd src\%%i
    git.exe pull -v --progress "origin"
    cd ../../
)
