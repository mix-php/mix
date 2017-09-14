<br>

<p align="center">
<img src="https://git.kancloud.cn/repos/onanying/mixphp1/raw/ba9d4f9d235c24da0a7b8d8a8aa53a57b8e83331/images/logo.png?access-token=1899f10823ef02e1745183298b8c71d3" alt="MixPHP V1">
</p>

<p align="center">高性能 • 极简 • 夸架构</p>

<p align="center">『 拥有 Yii2/TP5/CI3 传统框架的开发效率，又拥有 Swoole 扩展的高性能 』</p>

## 核心特征

夸架构：同一份代码可在Apache/Nginx+phpfpm、Nginx+Swoole两类架构中运行；

高性能：代码长驻内存，达到超过Yaf/phalcon这类C扩展框架的性能；

长连接：按进程保持的长连接，支持Mysql/Redis；

服务器：框架自带MixHttpd替代Apache/phpfpm作为高性能HTTP服务器；

自动加载：完全使用Composer，统一且高效；

路由：底层全正则实现，性能高，配置简单；

模型：模型集成了使用简单但功能强大的验证器，支持多场景控制；

查询构造器：独创的SQL构造方法，可灵活优雅的构造复杂语句；

视图：使用PHP做引擎，支持布局、属性；

## 框架定位

MixPHP 参考了多个流行框架特性，目的是要让用户更简单的开发应用。

MixPHP 做到了架构简单、使用简单、框架代码量小，能轻易改造与扩展。

MixPHP 基于 Swoole 的Web开发，轻松获取超高性能，[ [对比测试](http://www.jianshu.com/p/0ce1a9885e01) ] 。

为了达到开发效率与性能兼顾，MixPHP实现了夸架构，

项目前期可采用简单的LNMP架构，当出现性能瓶颈时再切换为Swoole架构，

即便是在LNMP架构下，MixPHP的性能也毫不逊色于传统框架，

夸架构还带来了开发上的便利，可在LNMP中开发，在Swoole中生产部署，无需担心热更新问题。

基于 Swoole 的开发已经成为高级 PHPer 必备技能之一，MixPHP 让 Swoole 的使用简单化，

用户可专心于业务开发，而无需了解 Swoole 复杂的生命周期、变量作用域、进程模型等问题。

SwooleWeb + MixHttpd 是 HTTP 领域的开发，未来 MixPHP 还会增加 TCP/UDP 领域的支持。


## 开发手册
 
[在线查看手册](https://www.kancloud.cn/onanying/mixphp1/379324) | [下载手册](https://www.kancloud.cn/onanying/mixphp1)

## 技术交流

官方QQ群：284806582

## License

MixPHP 框架基于 MIT license 进行开源。
