<?php

namespace Mix\JsonRpc;

use Mix\Concurrent\Sync\WaitGroup;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\ServerRequest;
use Mix\JsonRpc\Event\ProcessedEvent;
use Mix\JsonRpc\Factory\ResponseFactory;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Channel;

/**
 * Class Server
 * @package Mix\JsonRpc
 */
class Server implements \Mix\Http\Server\HandlerInterface, \Mix\Server\HandlerInterface
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var \Mix\Server\Server
     */
    protected $server;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array [[$service, $namespace, $suffix],...]
     */
    protected $services = [];

    /**
     * @var callable[]
     */
    protected $callables = [];

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->reusePort = $reusePort;
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
    }

    /**
     * 获取全部 service 名称, 通过类名
     *
     * Class                                              Service           Method
     * [namespace]Foo[suffix]::Bar                        foo               Foo.Bar
     * [namespace]Foo/Bar[suffix]::Baz                    foo               Bar.Baz
     * [namespace]Foo/Bar/Baz[suffix]::Cat                foo.bar           Baz.Cat
     * [namespace]V1/Foo[suffix]::Bar                     v1.foo            Foo.Bar
     * [namespace]V1/Foo/Bar[suffix]::Baz                 v1.foo.bar        Bar.Baz
     * [namespace]V1/Foo/Bar/Baz[suffix]::Cat             v1.foo.bar        Baz.Cat
     *
     * @return string[]
     */
    public function services()
    {
        $services = [];
        foreach ($this->services as $item) {
            list($class, $namespace, $suffix) = $item;

            $namespace = substr($namespace, -1, 1) == '\\' ? $namespace : $namespace . '\\';
            $name      = str_replace($namespace, '', $class);

            $suffixLength = strlen($suffix);
            $name         = ($suffixLength > 0 and substr($name, -$suffixLength, $suffixLength) == $suffix) ? substr($name, 0, -$suffixLength) : $name;

            $slice   = array_filter(explode('\\', strtolower($name)));
            $version = '';
            if (isset($slice[0]) && stripos($slice[0], 'v') === 0) {
                $version = array_shift($slice) . '.';
            }
            switch (count($slice)) {
                case 0:
                    $name = '';
                    break;
                case 1:
                case 2:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    $name = implode('.', $slice);
            }
            $services[] = $version . $name;
        }
        return $services;
    }

    /**
     * Register
     * 相同 Class 名，即便命名空间不同也会被覆盖
     * @param string $class
     * @param string $namespace
     * @param string $suffix
     */
    public function register(string $class, string $namespace = '', $suffix = '')
    {
        array_push($this->services, [$class, $namespace, $suffix]);

        $name         = basename(str_replace('\\', '/', $class));
        $suffixLength = strlen($suffix);
        $name         = ($suffixLength > 0 and substr($name, -$suffixLength, $suffixLength) == $suffix) ? substr($name, 0, -$suffixLength) : $name;

        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            $this->callables[sprintf('%s.%s', $name, $method)] = [$class, $method];
        }
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server = $this->server = new \Mix\Server\Server($this->host, $this->port, false, $this->reusePort);
        $server->set([
                'open_eof_check' => true,
                'package_eof'    => Constants::EOF,
            ] + $this->options);
        $server->start($this);
    }

    /**
     * 连接处理
     * @param Connection $conn
     * @throws \Throwable
     */
    public function handle(Connection $conn)
    {
        // 消息发送
        $sendChan = new Channel();
        xdefer(function () use ($sendChan) {
            $sendChan->close();
        });
        xgo(function () use ($sendChan, $conn) {
            while (true) {
                $data = $sendChan->pop();
                if (!$data) {
                    return;
                }
                try {
                    $conn->send($data);
                } catch (\Throwable $e) {
                    $conn->close();
                    throw $e;
                }
            }
        });
        // 消息读取
        while (true) {
            try {
                $data = $conn->recv();
                $this->callTCP($sendChan, $data);
            } catch (\Throwable $e) {
                // 忽略服务器主动断开连接异常
                if ($e instanceof ReceiveException) {
                    return;
                }
                // 抛出异常
                throw $e;
            }
        }
    }

    /**
     * 调用TCP
     * @param Channel $sendChan
     * @param string $content
     */
    protected function callTCP(Channel $sendChan, string $content)
    {
        /**
         * 解析
         * @var Request[] $requests
         * @var bool $single
         */
        try {
            list($single, $requests) = JsonRpcHelper::parseRequestsFromTCP($content);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $sendChan->push(JsonRpcHelper::content(true, $response));
            return;
        }
        // 处理
        $responses = $this->process(...$requests);
        // 发送
        $sendChan->push(JsonRpcHelper::content($single, ...$responses));
    }

    /**
     * 调用HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $httpResponse
     * @param string $content
     */
    protected function callHTTP(ServerRequest $request, \Mix\Http\Message\Response $httpResponse, string $content)
    {
        /**
         * 解析
         * @var Request[] $requests
         * @var bool $single
         */
        try {
            list($single, $requests) = JsonRpcHelper::parseRequestsFromHTTP($request, $content);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $body     = (new StreamFactory)->createStream(JsonRpcHelper::content(true, $response));
            $httpResponse->withBody($body)
                ->withContentType('application/json')
                ->withStatus(200)
                ->end();
            return;
        }
        // 处理
        $responses = $this->process(...$requests);
        // 发送
        $body = (new StreamFactory)->createStream(JsonRpcHelper::content($single, ...$responses));
        $httpResponse->withBody($body)
            ->withContentType('application/json')
            ->withStatus(200)
            ->end();
    }

    /**
     * 处理
     * @param Request ...$requests
     * @return array
     */
    protected function process(Request ...$requests)
    {
        $waitGroup = WaitGroup::new();
        $waitGroup->add(count($requests));
        $responses = [];
        foreach ($requests as $request) {
            xgo(function () use ($request, &$responses, $waitGroup) {
                xdefer(function () use ($waitGroup) {
                    $waitGroup->done();
                });
                // 执行
                $microtime = static::microtime();
                try {
                    // 验证
                    if (!JsonRpcHelper::validRequest($request)) {
                        throw new \RuntimeException('Invalid Request', -32600);
                    }
                    if (!isset($this->callables[$request->method])) {
                        throw new \RuntimeException(sprintf('Method %s not found', $request->method), -32601);
                    }
                    // 执行
                    list($class, $method) = $this->callables[$request->method];
                    $callable    = [new $class($request), $method];
                    $params      = is_array($request->params) ? $request->params : [$request->params];
                    $result      = call_user_func($callable, ...$params);
                    $result      = is_scalar($result) ? [$result] : $result;
                    $responses[] = (new ResponseFactory)->createResultResponse($result, $request->id);
                    // event
                    $event          = new ProcessedEvent();
                    $event->time    = round((static::microtime() - $microtime) * 1000, 2);
                    $event->request = $request;
                    $this->dispatch($event);
                } catch (\Throwable $ex) {
                    $message     = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
                    $code        = $ex->getCode();
                    $responses[] = (new ResponseFactory)->createErrorResponse($code, $ex->getMessage(), $request->id);
                    // event
                    $event          = new ProcessedEvent();
                    $event->time    = round((static::microtime() - $microtime) * 1000, 2);
                    $event->request = $request;
                    $event->error   = sprintf('[%d] %s', $code, $message);
                    $this->dispatch($event);
                }
            });
        }
        $waitGroup->wait();
        return $responses;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $response
     */
    public function handleHTTP(ServerRequest $request, \Mix\Http\Message\Response $response)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            $response->withStatus(500)->end();
            return;
        }
        $content = $request->getBody()->getContents();
        $this->callHTTP($request, $response, $content);
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Dispatch
     * @param object $event
     */
    protected function dispatch(object $event)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $this->dispatcher->dispatch($event);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server and $this->server->shutdown();
    }

}
