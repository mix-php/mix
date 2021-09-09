<?php

namespace Mix\Vega;

use Mix\Http\Message\Cookie;
use Mix\Http\Message\Response;
use Mix\Http\Message\Stream\StringStream;
use Mix\Vega\Exception\RuntimeException;
use Mix\View\Exception\ViewException;
use Mix\View\Renderer;

/**
 * Trait Writer
 * @package Mix\Vega
 */
trait Writer
{

    /**
     * @var Response
     */
    public $response;

    /**
     * @var Renderer
     */
    public $renderer;

    /**
     * @param int $code
     * @param string $format
     * @param ...$values
     * @return bool
     */
    public function string(int $code, string $format, ...$values): bool
    {
        $this->response->withStatus($code);
        $body = new StringStream(sprintf($format, ...$values));
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param string $name
     * @param array $data
     * @return bool
     * @throws ViewException
     */
    public function HTML(int $code, string $name, array $data = []): bool
    {
        $body = new StringStream($this->renderer->render($name, $data));
        $this->response->withContentType('text/html', 'utf-8');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param $data
     * @return bool
     */
    public function JSON(int $code, $data): bool
    {
        $body = new StringStream(static::jsonMarshal($data));
        $this->response->withContentType('application/json', 'utf-8');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param $data
     * @return bool
     */
    public function JSONP(int $code, $data): bool
    {
        $callback = $this->defaultQuery('callback', '');
        if ($callback == '') {
            return $this->json($code, $data);
        }

        $json = static::jsonMarshal($data);
        $javascript = sprintf('%s(%s)', $callback, $json);
        $body = new StringStream($javascript);
        $this->response->withContentType('application/javascript', 'utf-8');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    abstract public function defaultQuery(string $key, string $default): string;

    /**
     * @param $data
     * @return string
     * @throws RuntimeException
     */
    protected static function jsonMarshal($data): string
    {
        $result = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if ($result === false) {
            throw new RuntimeException(sprintf('json_encode failed: %d %s', json_last_error(), json_last_error_msg()));
        }
        return $result;
    }

    /**
     * @param int $code
     */
    public function status(int $code): void
    {
        $this->response->withStatus($code);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader(string $key, string $value): void
    {
        $this->response->withHeader($key, $value);
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function setCookie(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        $this->response->withAddedCookie($cookie);
    }

    /**
     * @param string $location
     * @param int $code
     */
    public function redirect(string $location, int $code = 302): void
    {
        $this->response->redirect($location, $code);
    }

}
