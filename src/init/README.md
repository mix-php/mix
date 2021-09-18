> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Init

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

```php
Mix\Init\Finder::in('/path/classdir')->exec();
```

指定初始化方法名称

```php
Mix\Init\Finder::in('/path/classdir')->exec('init');
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
