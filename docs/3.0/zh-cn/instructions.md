# 编程须知

## 协程单例实例化

我们通常采用以下方法实现单例模式，这种方式在同步模式下是没有任何问题的，但是在协程模式下，当 `Database` 对象构造函数中包含 `IO` 处理，比如：连接数据库等代码时，在 `self::$instance`
没有被赋值的时候，协程会切换到其他请求去执行，当其他请求也需要使用该单例时，正好因为 `self::$instance == null` 而再一次触发实例化，因此会导致实例化很多次，造成内存与连接的浪费。因此：**在协程环境单例模式 `new` 对象的构造函数中如果包含连接、文件读写等触发协程切换的代码，那这个单例是不可靠的，会被重复实例化很多次**

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

### 使用并发锁解决重复实例化问题

我们在骨架中提供了类似 golang 语言 sync.Once 的并发锁工具 `App\Once::class`，只需按以下方法使用就可保证即使在并发请求中也只会执行一次。

- `src/Container` 数据库单例

```php
class DB
{
    private static $instance;
    
    private static $once;

    public static function init(): void
    {
        self::$once = new Once();
    }

    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            self::$once->do(function() {
                self::$instance = new Database($dsn, $username, $password);
            });
        }
        return self::$instance;
    }
}

DB::init();
```

### 需要注意哪些库

在 `mix` 官方提供的骨架中，redis、database 本身就使用处理好了并发因此用户无需处理，只有在当我们使用 `mix/grpc` 的客户端时，由于我们需要常驻复用这些客户端，因此需要使用 `App\Once::class` 来处理：

- 在 `src/Container` 创建一个gRPC客户端单例

```php
class GrpcClients
{
    private static $client1;
    
    private static $client2;
    
    private static $once;

    public static function init(): void
    {
        self::$once = new Once();
    }

    public static function client1(): Client
    {
        if (!isset(self::$client1)) {
            self::$once->do(function() {
                self::$client1 = new \Mix\Grpc\Client('192.168.0.10', 9595);
                self::$client2 = new \Mix\Grpc\Client('192.168.0.11', 9595);
            });
        }
        return self::$client1;
    }
    
    public static function client2(): Client
    {
        if (!isset(self::$client2)) {
           self::$once->do(function() {
                self::$client1 = new \Mix\Grpc\Client('192.168.0.10', 9595);
                self::$client2 = new \Mix\Grpc\Client('192.168.0.11', 9595);
            });
        }
        return self::$client2;
    }
}

GrpcClients::init();
```

客户端使用方法和 `src/Container` 目录内的 redis、database 类似

```
$client1 = GrpcClients::client1();
```
