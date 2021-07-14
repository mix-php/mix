> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Database

Simple database for use in multiple execution environments, with support for FPM, CLI, Swoole, WorkerMan, and optional
connection pool (coroutine)

可在各种环境中使用的轻量数据库，支持 FPM、CLI、Swoole、WorkerMan，可选的连接池 (协程)

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a)
, [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)
敲门暗号：db

## Installation

```
composer require mix/database
```

## Quick start

```php
$db = new Mix\Database\Database('mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test', 'username', 'password');
```

创建

```php
$db->insert('users', [
    'name' => 'foo',
    'balance' => 0,
]);
```

查询

```php
$db->table('users')->where('id = ?', 1)->first();
```

更新

```php
$db->table('users')->where('id = ?', 1)->update('name', 'foo1');
```

删除

```php
$db->table('users')->where('id = ?', 1)->delete();
```

## 启动连接池 Pool

在 `Swoole` 协程环境中，启动连接池

```php
$maxOpen = 50;        // 最大开启连接数
$maxIdle = 20;        // 最大闲置连接数
$maxLifetime = 3600;  // 连接的最长生命周期
$waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
$db->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
```

连接池统计

```php
$db->poolStats(); // array, fields: total, idle, active
```

## 创建 Insert

创建

```php
$data = [
    'name' => 'foo',
    'balance' => 0,
];
$db->insert('users', $data);
```

获取 InsertId

```php
$data = [
    'name' => 'foo',
    'balance' => 0,
];
$insertId = $db->insert('users', $data)->lastInsertId();
```

替换创建

```php
$data = [
    'name' => 'foo',
    'balance' => 0,
];
$db->insert('users', $data, 'REPLACE INTO');
```

批量创建

```php
$data = [
    [
        'name' => 'foo',
        'balance' => 0,
    ],
    [
        'name' => 'foo1',
        'balance' => 0,
    ]
];
$db->batchInsert('users', $data);
```

使用函数创建

```php
$data = [
    'name' => 'foo',
    'balance' => 0,
    'add_time' => new Mix\Database\Expr('CURRENT_TIMESTAMP()'),
];
$db->insert('users', $data);
```

## 查询 Select

### 获取结果

|  方法名称   | 描述  |
|  ----  | ----  |
| get(): array  | 获取多行 |
| first(): array or object  | 获取第一行 |
| value(string $field): mixed  | 获取第一行某个字段 |
| statement(): \PDOStatement  | 获取原始结果集 |

### Where

#### AND

```php
$db->table('users')->where('id = ? AND name = ?', 1, 'foo')->get();
```

```php
$db->table('users')->where('id = ?', 1)->where('name = ?', 'foo')->get();
```

#### OR

```php
$db->table('users')->where('id = ? OR id = ?', 1, 2)->get();
```

```php
$db->table('users')->where('id = ?', 1)->or('id = ?', 2)->get();
```

#### IN

```php
$db->table('users')->where('id IN (?)', [1, 2])->get();
```

```php
$db->table('users')->where('id NOT IN (?)', [1, 2])->get();
```

### Select

```php
$db->table('users')->select('id, name')->get();
```

```php
$db->table('users')->select('id', 'name')->get();
```

```php
$db->table('users')->select('name AS n')->get();
```

### Order

```php
$db->table('users')->order('id', 'desc')->get();
```

```php
$db->table('users')->order('id', 'desc')->order('name', 'asc')->get();
```

### Limit

```php
$db->table('users')->limit(5)->get();
```

```php
$db->table('users')->offset(10)->limit(5)->get();
```

### Group & Having

```php
$db->table('news')->select('uid, COUNT(*) AS total')->group('uid')->having('COUNT(*) > ?', 0)->get();
```

```php
$db->table('news')->select('uid, COUNT(*) AS total')->group('uid')->having('COUNT(*) > ? AND COUNT(*) < ?', 0, 10)->get();
```

### Join

```php
$db->table('news AS n')->select('n.*, u.name')->join('users AS u', 'n.uid = u.id')->get();
```

```php
$db->table('news AS n')->select('n.*, u.name')->leftJoin('users AS u', 'n.uid = u.id AND u.balance > ?', 10)->get();
```

## 更新 Update

更新单个字段

```php
$db->table('users')->where('id = ?', 1)->update('name', 'foo1');
```

获取影响行数

```php
$rowsAffected = $db->table('users')->where('id = ?', 1)->update('name', 'foo1')->rowCount();
```

更新多个字段

```php
$data = [
    'name' => 'foo1',
    'balance' => 100,
];
$db->table('users')->where('id = ?', 1)->updates($data);
```

使用表达式更新

```php
$db->table('users')->where('id = ?', 1)->update('balance', new Mix\Database\Expr('balance + ?', 1));
```

```php
$data = [
    'balance' => new Mix\Database\Expr('balance + ?', 1),
];
$db->table('users')->where('id = ?', 1)->updates($data);
```

使用函数更新

```php
$db->table('users')->where('id = ?', 1)->update('add_time', new Mix\Database\Expr('CURRENT_TIMESTAMP()'));
```

```php
$data = [
    'add_time' => new Mix\Database\Expr('CURRENT_TIMESTAMP()'),
];
$db->table('users')->where('id = ?', 1)->updates($data);
```

## 删除 Delete

删除

```php
$db->table('users')->where('id = ?', 1)->delete();
```

获取影响行数

```php
$rowsAffected = $db->table('users')->where('id = ?', 1)->delete()->rowCount();
```

## 原生 Raw

```php
$db->raw('SELECT * FROM users WHERE id = ?', 1)->first();
```

```php
$db->exec('DELETE FROM users WHERE id = ?', 1)->rowCount();
```

## 事务 Transaction

手动事务

```php
$tx = $db->beginTransaction();
try {
    $data = [
        'name' => 'foo',
        'balance' => 0,
    ];
    $tx->insert('users', $data);
    $tx->commit();
} catch (\Throwable $ex) {
    $tx->rollback();
    throw $ex;
}
```

自动事务，执行异常自动回滚并抛出异常

```php
$db->transaction(function (Mix\Database\Transaction $tx) {
    $data = [
        'name' => 'foo',
        'balance' => 0,
    ];
    $tx->insert('users', $data);
});
```

## 调试 Debug

```php
$db->debug(function (Mix\Database\ConnectionInterface $conn) {
        var_dump($conn->queryLog()); // array, fields: time, sql, bindings
    })
    ->table('users')
    ->where('id = ?', 1)
    ->get();
```

## 日志 Logger

日志记录器，配置后可打印全部SQL信息

```php
$db->setLogger($logger);
```

`$logger` 需实现 `Mix\Database\LoggerInterface`

```php
interface LoggerInterface
{
    public function trace(float $time, string $sql, array $bindings, int $rowCount, ?\Throwable $exception);
}
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
