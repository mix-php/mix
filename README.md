## 欢迎使用 MixPHP V1

`MixPHP` 是一个极简高性能PHP开发框架，参考国内流行的 `Yii2` `ThinkPHP5` `CodeIgniter` 三大框架的优点，结合 `Swoole` 扩展的特性，简化整合并加以创新，以简单、快速、规范、本土化为理念而正向研发的一款MVC框架。

## 完全开发手册

[![MixPHP V1 完全开发手册](https://cover.kancloud.cn/onanying/mixphp1!small)](https://www.kancloud.cn/onanying/mixphp1/379324)     
[在线查看手册](https://www.kancloud.cn/onanying/mixphp1/379324) || [下载手册](https://www.kancloud.cn/onanying/mixphp1)

## 极其简单

- 很容易学习，稍微看一下文档，跑一下demo就能学会（源代码含demo）；
- 文档完善，有非常详细的开发文档；
- 很容易读懂框架核心源码，框架代码量小，架构简单；

## 开发快速

- 支持POST/GET设置的路由，可简单配置出REST风格API；
- 包含场景控制的验证器模型，助力你快速写出高质量代码；
- 不支持ORM的查询构造器，却能让你快速写出CRUD，再复杂的SQL也能优美的控制条件；

## 超高性能

- 短小精悍，比CodeIgniter还要小巧，更接近原生的性能；
- 基于Swoole的HTTP应用服务，让你的API性能起飞，达到Yaf/Phalcon的性能；
- 通过进程管理的真正MySQL长连接，让你的数据库不再那么忙碌；

## 与众不同

- HTTP应用服务：基于Swoole，可替代Apache/phpfpm，适用于高性能API开发；
- 错误处理：404，500错误可以配置为html,json,xml三种响应格式；
- 自动加载：完全使用Composer，统一且高效；
- 路由：底层全正则实现，性能高，配置简单；
- 模型：模型集成了使用简单但功能强大的验证器；
- 查询构造器：独创的SQL构造方法，可灵活优雅的构造复杂语句；
- ~~控制台命令：封装了多线程、消息队列，适用于后台任务处理、消息队列 "消费者" 开发；~~
- ~~定时任务服务：动态多进程定时任务服务器，负责定时执行框架内的控制台命令；~~