@echo off
for %%i in (auth,bean,cache,concurrent,console,database,event,helper,http-message,http-server,http-session,log,pool,redis,route,server,udp-server,validate,view,websocket) do (
    echo -- %%i
    cd vendor\mix\%%i
    git.exe checkout %1%
    cd ../../../
)
