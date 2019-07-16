@echo off
for %%i in (auth,bean,cache,concurrent,console,database,helper,http-daemon,http-message,http-server,http-session,log,pool,redis,server,tcp,tcp-daemon,tcp-server,tcp-session,udp,udp-daemon,udp-server,validate,websocket,websocket-daemon,websocket-server) do (
    echo -- %%i
    cd vendor\mix\%%i
    git.exe checkout %1%
    cd ../../../
)
