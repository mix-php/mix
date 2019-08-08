@echo off
for %%i in (auth,bean,cache,concurrent,console,database,helper,http-message,http-server,http-session,log,pool,redis,server,udp-server,validate,websocket) do (
    echo -- %%i
    cd vendor\mix\%%i
    git.exe checkout %1%
    cd ../../../
)
