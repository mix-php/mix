> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Init

Help execute static initialization, usually for preloading singletons

帮助执行静态初始化，通常用于预加载单例

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
