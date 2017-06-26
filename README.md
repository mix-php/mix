## 欢迎使用 ExpressPHP V2

ExpressPHP V2 是一个高性能极简Web开发MVC框架，融合 YII2 的开发效率、ThinkPHP5 的现代化，CodeIgniter 的轻量级，拥有基于 Swoole/Http/Server 的高性能 SwooleHttpServer，基于 Swoole/process 的多进程多线程 QueueConsumerServer，同时兼顾了开发效率、高性能与低学习成本。

## 我是一个这样的框架

- 参考了 `YII2` 整体架构；
- 像 `ThinkPHP5` 一样自动加载、PSR规范、Composer等新特性；
- 像 `CodeIgniter` 一样小巧、高性能；
- 架构非常简单，代码量小，能很容易读懂框架核心源码；
- 很容易学习，因为大量使用方法与以上三个框架完全一至；
- 文档完善，有非常详细的开发文档，且代码内置了demo；

## 与众不同的新特性

- SwooleHttpServer：不需要 Apache/phpfpm 的 http 服务器，性能提升巨大，适用于开发API；
- QueueConsumerServer：多进程多线程的消息队列处理服务器，适用于消息队列 "消费者" 开发；
