<?php

namespace Mix\Route;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;
use Mix\Micro\Route\RouterInterface;
use Mix\Route\Exception\NotFoundException;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Router
 * @package Mix\Route
 * @author liu,jian <coder.keda@gmail.com>
 */
class Router implements \Mix\Http\Server\ServerHandlerInterface, RouterInterface
{

    /**
     * 默认变量规则
     * @var string
     */
    public $defaultPattern = '[\w-]+';

    /**
     * 路由变量规则
     * @var array
     */
    public $patterns = [];

    /**
     * 全局中间件
     * @var array MiddlewareInterface class or object
     */
    public $middleware = [];

    /**
     * 路由规则
     * @var array
     */
    public $rules = [];

    /**
     * 转化后的路由规则
     * @var array
     */
    protected $materials = [];

    /**
     * Router constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 获取 url 规则映射的全部 service 名称
     *
     * Url                  Service
     * /                    index
     * /foo                 foo
     * /foo/bar             foo
     * /foo/bar/baz         foo
     * /foo/bar/baz/cat     foo.bar
     * /v1/foo/bar          v1.foo
     * /v1/foo/bar/baz      v1.foo
     * /v1/foo/bar/baz/cat  v1.foo.bar
     *
     * @return string[][] [name => [pattern,...]]
     */
    public function services()
    {
        $services = [];
        foreach ($this->materials as $material) {
            list($regular, , , $pattern) = $material;
            $slice   = explode(' ', $regular);
            $path    = substr($slice[1], 0, -3);
            $slice   = array_filter(explode('\/', strtolower($path)));
            $version = '';
            if (isset($slice[1]) && stripos($slice[1], 'v') === 0) {
                $version = array_shift($slice) . '.';
            }
            switch (count($slice)) {
                case 0:
                    $name = 'index';
                    break;
                case 1:
                case 2:
                case 3:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    array_pop($slice);
                    $name = implode('.', $slice);
            }
            $services[$version . $name][] = $pattern;
        }
        return $services;
    }

    /**
     * 解析
     * 生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
     */
    public function parse()
    {
        $rules           = $this->merge($this->rules, $this->middleware);
        $this->materials = $this->convert($rules);
    }

    /**
     * 合并中间件、分组
     * @param array $rules
     * @param array $middleware
     * @return array
     */
    protected function merge(array $rules, array $middleware): array
    {
        $data = [];
        foreach ($rules as $pattern => $rule) {
            $rule['middleware'] = $rule['middleware'] ?? [];
            if (($gRules = current($rule)) && is_array($gRules) && !is_callable($gRules, true)) {
                // 分组处理
                foreach ($gRules as $gPattern => $gRule) {
                    $gPattern            = substr_replace($gPattern, $pattern . '/', strpos($gPattern, '/'), 1);
                    $gRule['middleware'] = $gRule['middleware'] ?? [];
                    $gRule['middleware'] = array_merge($middleware, $rule['middleware'], $gRule['middleware']);
                    $data[$gPattern]     = $gRule;
                }
            } else {
                $rule['middleware'] = array_merge($middleware, $rule['middleware']);
                $data[$pattern]     = $rule;
            }
        }
        return $data;
    }

    /**
     * 转换正则
     * @param array $rules
     * @return array
     */
    protected function convert(array $rules): array
    {
        $materials = [];
        foreach ($rules as $pattern => $route) {
            if ($blank = strpos($pattern, ' ')) {
                $method = substr($pattern, 0, $blank);
                $method = "(?:{$method}) ";
                $path   = substr($pattern, $blank + 1);
            } else {
                $method = '(?:GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS) ';
                $path   = $pattern;
            }
            $fragment = explode('/', $path);
            $var      = [];
            foreach ($fragment as $k => $v) {
                preg_match('/{([\w-]+)}/i', $v, $matches);
                if (empty($matches)) {
                    continue;
                }
                list($fname) = $matches;
                $fname = substr($fname, 1, -1);
                if (isset($this->patterns[$fname])) {
                    $fragment[$k] = str_replace('{' . $fname . '}', "({$this->patterns[$fname]})", $fragment[$k]);
                } else {
                    $fragment[$k] = str_replace('{' . $fname . '}', "({$this->defaultPattern})", $fragment[$k]);
                }
                $var[] = $fname;
            }
            $regular     = '/^' . $method . implode('\/', $fragment) . '$/i';
            $materials[] = [$regular, $route, $var, $pattern];
        }
        return $materials;
    }

    /**
     * With pattern
     * @param string $name
     * @param string $regular
     * @return $this
     */
    public function pattern(string $name, string $regular)
    {
        $this->patterns[$name] = $regular;
        return $this;
    }

    /**
     * With rule
     * @param string $pattern
     * @param array $rule [callable, 'middleware'=>[]]
     * @return $this
     */
    public function rule(string $pattern, array $rule)
    {
        $this->rules[$pattern] = $rule;
        return $this;
    }

    /**
     * 匹配
     * @param string $method
     * @param string $pathinfo
     * @return Result
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function match(string $method, string $pathinfo): Result
    {
        // 由于路由歧义，会存在多条路由规则都可匹配的情况
        $result = [];
        foreach ($this->materials as $item) {
            list($regular, $route, $var) = $item;
            if (preg_match($regular, "{$method} {$pathinfo}", $matches)) {
                $params = [];
                // 提取路由查询参数
                foreach ($var as $k => $v) {
                    $params[$v] = $matches[$k + 1];
                }
                // 记录参数
                $result[] = [$route, $params];
            }
        }
        // 筛选有效的结果
        foreach ($result as $item) {
            list($route, $params) = $item;
            $callback = array_shift($route);
            if (is_callable($callback)) {
                // 返回
                return new Result($callback, $route['middleware'], $params);
            }
        }
        throw new NotFoundException('Not Found (#404)');
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     * @throws \Throwable
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        // 路由匹配
        try {
            $result = $this->match($request->getMethod(), $request->getServerParams()['path_info'] ?: '/');
        } catch (NotFoundException $ex) {
            // 404 处理
            $this->error404($ex, $response)->send();
            return;
        }
        // 保存路由参数
        foreach ($result->getParams() as $key => $value) {
            $request->withAttribute($key, $value);
        }
        // 通过中间件执行
        $process    = function (ServerRequest $request, Response $response) use ($result) {
            try {
                // 构造方法内的参数是为了方便继承封装使用
                // 为了支持 \Closure 移除了构造方法传参数，为路由支持 websocket
                $response = call_user_func($result->getCallback(), $request, $response);
            } catch (\Throwable $ex) {
                // 500 处理
                $this->error500($ex, $response)->send();
                // 抛出错误，记录日志
                throw $ex;
            }
            return $response;
        };
        $dispatcher = new MiddlewareDispatcher($result->getMiddleware(), $process, $request, $response);
        $response   = $dispatcher->dispatch();
        /** @var Response $response */
        $response->send();
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error404(\Throwable $exception, Response $response): Response
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404);
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error500(\Throwable $exception, Response $response): Response
    {
        $content = [
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body)
            ->withStatus(500);
    }

}
