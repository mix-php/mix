## Mix Database

Simple database for use in multiple execution environments, with support for FPM, Swoole, Workerman, and optional
connection pool (coroutine)

可在各种环境使用的轻量数据库，支持 FPM、Swoole、WorkerMan，可选的连接池 (协程)

## 技术交流

知乎：https://www.zhihu.com/people/onanying    
官方QQ群：[284806582](https://shang.qq.com/wpa/qunwpa?idkey=b3a8618d3977cda4fed2363a666b081a31d89e3d31ab164497f53b72cf49968a)
, [825122875](http://shang.qq.com/wpa/qunwpa?idkey=d2908b0c7095fc7ec63a2391fa4b39a8c5cb16952f6cfc3f2ce4c9726edeaf20)
敲门暗号：vega

## Installation

```
composer require mix/database
```

## Quick start

```php
$db = new Mix\Database\Database('mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test', 'root', '***');
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

原生

```php
$db->raw('SELECT * FROM users')->queryAll();
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
$insertId = $db->insert('users', $data)->getLastInsertId();
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

## 查询 Select

### where

#### AND

```php
$db->table('users')
    ->where('id = ? AND name = ?', 1, 'foo')
    ->get();
```

```php
$db->table('users')
    ->where('id = ?', 1)
    ->where('name = ?', 'foo')
    ->get();
```

#### OR

```php
$db->table('users')
    ->where('id = ? OR id = ?', 1, 2)
    ->get();
```

```php
$db->table('users')
    ->where('id = ?', 1)
    ->or('id = ?', 2)
    ->get();
```

#### IN

```php
$db->table('users')
    ->where('id IN (?)', [1, 2])
    ->get();
```

```php
$db->table('users')
    ->where('id NOT IN (?)', [1, 2])
    ->get();
```

### Select 

```php
$db->table('users')
    ->select('id, name')
    ->get();
```

```php
$db->table('users')
    ->select('id', 'name')
    ->get();
```

```php
$db->table('users')
    ->select('name AS n')
    ->get();
```

### Order

```php
$db->table('users')
    ->order('id', 'desc')
    ->get();
```

```php
$db->table('users')
    ->order('id', 'desc')
    ->order('name', 'aes')
    ->get();
```

### Limit

```php
$db->table('users')
    ->offset(10)
    ->limit(5)
    ->get();
```

### Group & Having

```php
$db->table('news')
    ->select('id, COUNT(*) AS total')
    ->group('id')
    ->having('COUNT(*) > ?', 10)
    ->get();
```

### Join

```php
$db = new Mix\Database\Database('mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test', 'root', '***');
$db->table('news AS n')
    ->select('n.*, u.name')
    ->join('users AS u', 'n.uid = u.id')
    ->get();
```

### 获取结果集

`table` 触发

|  方法名称   | 描述  |
|  ----  | ----  |
| get(): array  | 获取多行 |
| first(): mixed  | 获取第一行 |
| value(string $field): mixed  | 获取第一行某个字段 |

`raw` 触发

|  方法名称   | 描述  |
|  ----  | ----  |
| get(): array  | 获取多行 |
| first(): mixed  | 获取第一行 |
| value(string $field): mixed  | 获取第一行某个字段 |

## 更新 Update

## 删除 Delete

## 事物 Transaction

## 调试 Debug

## 日志 Logger

## License

Apache License Version 2.0, http://www.apache.org/licenses/
