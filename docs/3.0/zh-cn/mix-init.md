## Mix Init

Help execute static initialization of the class, usually for preloading singletons

帮助执行类的静态初始化，通常用于预加载单例

## Installation

```
composer require mix/init
```

## Quick start

定义类的静态初始化方法

```php
class Example
{
    public static function init()
    {
        // do something
    }
}
```

从目录执行

> 可以通过定制方法名称，达到在不同的场景初始化的目的

```php
Mix\Init\Finder::in('/path/classdir')->exec('init');
```
