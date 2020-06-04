# swoolex

[SW-X官方地址](https://www.sw-x.cn/ "SW-X官方地址")

### 介绍

SwooleX，基于 Swoole 原生协程的新时代 PHP 高性能协程全栈框架，内置全套服务端协程封装，常驻内存，不依赖传统的 PHP-FPM，全异步非阻塞 IO 实现，以类似于同步客户端的写法实现异步客户端的使用，没有复杂的异步回调，没有繁琐的 yield, 有类似 Go 语言的协程、支持传统的MVC模式开发，便于刚接触Swoole的PHPer可以快速上手， 局部的注解依赖实现了Ioc、AOP、Route绑定等等，可以用于构建高性能的Web系统、API、中间件、基础服务等等。

测试环境为：4核4G+CentOS7.6环境，指令：ab -c 200 -n 200000 -k http://127.0.0.1:9502/

![](https://www.sw-x.cn/img/ab.png)