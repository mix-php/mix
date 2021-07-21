> OpenMix 出品：[https://openmix.org](https://openmix.org/mix-php)

# Mix Event

Event dispatcher based on PSR-14 standard

基于 PSR-14 标准的事件调度库

## Installation

```
composer require mix/event
```

## 定义一个事件

事件可以为任意类，我们以 SQL 执行事件调度来举例

```php
class DatabaseEvent
{
    public $time = 0;
    public $sql = '';
    public $bindings = [];
}
```

## 定义一个监听器

监听器是用户编写处理事件逻辑代码的地方，`events` 方法返回一个要监听的事件类的数组，当这些事件触发时，会调用 `process` 方法

```php
use Mix\Event\ListenerInterface;

class DatabaseListener implements ListenerInterface
{

    public function events(): array
    {
        // 要监听的事件数组，可监听多个事件
        return [
            DatabaseEvent::class,
        ];
    }

    public function process(object $event):void
    {
        // 事件触发后，会执行该方法
    }

}
```

## 创建调度器

创建调度器，并传入监听器

```php
$dispatcher = new Mix\Event\EventDispatcher(new DatabaseListener());
```

## 触发事件

在事件产生的位置触发事件，当后面需要对该事件扩展业务逻辑时，只需在监听器中增加代码即可，达到不污染正常业务流程的目的

```php
$event = new DatabaseEvent();
$event->time = 10;
$event->sql = 'select * from users';
$dispatcher->dispatch($event);
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
