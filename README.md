## MixPHP V1 —— 极简混合型开发框架

MixPHP 是一款极简混合型开发框架，参考国内流行框架优点，结合 `Swoole` 扩展，正向研发的多功能MVC框架。

* * * * *

> MixPHP最大的优势：『 即可作小刀，又能当斧头，小刀还能变斧头』

**初级PHPer**：框架的简单易用、文档详细能让你快速搭建出一个产品。
**中级PHPer**：包含场景验证器的模型、独特的查询构建器、包含布局与属性的视图让你快速推进项目，项目更早上线。
**高级PHPer**：基于Swoole的高性能API开发/Console开发，扩展PHP开发边界，压榨每一滴性能。

* * * * *

> 支持的朋友请到 [GitHub](https://github.com/onanying/MixPHP-V1) 加个Star吧，让更多的朋友发现MixPHP。

## 完全开发手册
 
[在线查看手册](https://www.kancloud.cn/onanying/mixphp1/379324) || [下载手册](https://www.kancloud.cn/onanying/mixphp1)

## 极其简单

- 坚持架构简单、实现简单、使用简单的极简理念。
- 很容易学习，稍微看一下文档，跑一下demo就能学会（源代码含demo）；
- 很容易读懂框架核心源码，框架代码量小，架构简单；

## 开发快速

- 支持POST/GET设置的路由，可简单配置出REST风格API；
- 包含场景控制的验证器模型，助力你快速写出高质量代码；
- 不支持ORM的查询构造器，却能让你快速写出CRUD，再复杂的SQL也能优美的控制条件；

## 超高性能

- 短小精悍，比CodeIgniter还要小巧，更接近原生的性能；
- 基于Swoole的Web应用开发，让你的API性能起飞，达到Yaf/Phalcon的性能；
- 通过进程管理的真正MySQL长连接，让你的数据库不再那么忙碌；

## 与众不同的小细节

- 多种功能：支持开发Web、SwooleWeb、Console三种不同类型的应用；
- MixHttpd：基于Swoole，可替代Apache/phpfpm作为应用服务器；
- 自动加载：完全使用Composer，统一且高效；
- 路由：底层全正则实现，性能高，配置简单；
- 模型：模型集成了使用简单但功能强大的验证器；
- 查询构造器：独创的SQL构造方法，可灵活优雅的构造复杂语句；
- 错误处理：404，500错误可以配置为html,json,xml三种响应格式；