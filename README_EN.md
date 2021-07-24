> OpenMix 出品：https://openmix.org

<p align="center">
    <br>
    <br>
    <img src="https://openmix.org/static/image/logo_php.png" width="120" alt="MixPHP">
    <br>
    <br>
</p>

<h1 align="center">Mix PHP</h1>

[中文](https://github.com/mix-php/mix) | English

MixPHP is a PHP command-line development framework; HTTP based on `Vega` can support both Swoole and WorkerMan ecosystems at the same time, and can be switched seamlessly; `V3` is a highly decoupled version, the overall code is based on multiple independent Even if users do not use our scaffolding, they can also use these independent modules, and all modules support native development. For example: you can only use mix/vega to work with laravel orm; you can use mix/database and mix/redis in any environment; you can use mix/grpc native code to write gRPC; all modules can be combined like building blocks.

## Standalone module

All core modules can be used independently, and all support native code development.

- [mix/vega](https://github.com/mix-php/vega) CLI mode HTTP network framework written in PHP, supports Swoole, WorkerMan, and is consistent with the gin positioning of the Go ecosystem
- [mix/database](https://github.com/mix-php/database) Lightweight database that can be used in various environments, supports FPM, CLI, Swoole, WorkerMan, optional connection pool (coroutine)
- [mix/redis](https://github.com/mix-php/redis) PHP Redis that can be used in various environments, supports FPM, CLI, Swoole, WorkerMan, optional connection pool (coroutine)
- [mix/redis-subscribe](https://github.com/mix-php/redis-subscribe) Redis native protocol subscription library based on Swoole coroutine
- [mix/grpc](https://github.com/mix-php/grpc) PHP gRPC library based on Swoole coroutine, including protoc code generator, server, client
- [mix/websocket](https://github.com/mix-php/websocket) PHP WebSocket server and client based on Swoole coroutine
- [mix/validate](https://github.com/mix-php/validate) PSR-7-based verification library
- [mix/worker-pool](https://github.com/mix-php/worker-pool) Swoole-based worker pool, coroutine pool
- [mix/event](https://github.com/mix-php/event) Event dispatcher based on PSR-14 standard
- [mix/cli](https://github.com/mix-php/cli) PHP command line interactive commander

## Quick start

Provide ready-made scaffolding, quickly create projects, immediate output.

- [Write an API interface](https://github.com/mix-php/api-skeleton#readme)

```
composer create-project --prefer-dist mix/api-skeleton api
```

- [Write a web page](https://github.com/mix-php/web-skeleton#readme)

```
composer create-project --prefer-dist mix/web-skeleton web
```

- [Write a WebSocket service](https://github.com/mix-php/websocket-skeleton#readme)

```
composer create-project --prefer-dist mix/websocket-skeleton websocket
```

- [Write a gRPC interface](https://github.com/mix-php/grpc-skeleton#readme)

```
composer create-project --prefer-dist mix/grpc-skeleton grpc
```

## Golang framework

OpenMix also has Golang ecological framework

- https://github.com/mix-go/mix

## Old version documentation

- `V1.*` https://www.kancloud.cn/onanying/mixphp1/content
- `V2.0` https://www.kancloud.cn/onanying/mixphp2/content
- `V2.1` https://www.kancloud.cn/onanying/mixphp2-1/content
- `V2.2` https://www.kancloud.cn/onanying/mixphp2-2/content

## License

Apache License Version 2.0, http://www.apache.org/licenses/
