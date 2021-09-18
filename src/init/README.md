> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Init

Help execute static initialization of the class, usually for preloading singletons

帮助执行类的静态初始化，通常用于预加载单例

## Installation

```
composer require mix/init
```

## Quick start

```php
Mix\Init\Finder::in('/path/classdir')->exec();
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
