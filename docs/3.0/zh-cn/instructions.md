# 编程须知

## 协程单例实例化

我们通常采用以下方法实现单例模式，这种方式在同步模式下是没有任何问题的，但是在协程模式下，当 `Database` 对象构造函数中包含 `IO` 处理，比如：连接数据库等代码时，在 `self::$instance` 没有被赋值的时候，协程会切换到其他请求去执行，当其他请求也需要使用该单例时，正好因为 `self::$instance == null` 而再一次触发实例化，因此会导致实例化很多次，造成内存与连接的浪费。因此：**在协程环境中单例模式是不可靠的，请提前预加载对象，不要在并发请求中实例化**

```php
class DB
{
    private static $instance;
    
    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database($dsn, $username, $password);
        }
        return self::$instance;
    }
}
```

我们提供了 [mix/init](zh-cn/mix-init.md) 库帮助自动化预加载，你只需要在骨架目录 `src/Container` 定义的单例 class 增加一个 `init` 静态方法，在入口文件中有对应代码会在 `server` 启动前先执行该初始化方法提前预加载对象，由于实例化是在并发请求之前就已经完成，因此就避免了上面的问题。

- `src/Container` 数据库单例

```php
class DB
{

    /**
     * @var Database
     */
    private static $instance;

    public static function init(): void
    {
        self::$instance = new Database($dsn, $username, $password);
    }

    /**
     * @return Database
     */
    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            static::init();
        }
        return self::$instance;
    }
}
```

- 入口文件默认已经包含以下代码

```php
StaticInit::finder(__DIR__ . '/../src/Container')->exec('init');
```
