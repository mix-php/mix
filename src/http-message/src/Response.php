<?php

namespace Mix\Http\Message;

use Psr\Http\Message\ResponseInterface;
use function Swow\Http\packResponse;

/**
 * Class Response
 * @package Mix\Http\Message
 */
class Response extends Message implements ResponseInterface
{

    /**
     * @var \Swoole\Http\Response|\Workerman\Connection\TcpConnection|\Swow\Http\Server\Connection
     */
    protected $rawResponse;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $reasonPhrase = '';

    /**
     * @var Cookie[]
     */
    protected $cookies = [];

    /**
     * @var bool
     */
    protected $sended = false;

    /**
     * Response constructor.
     * @param int $code
     * @param string $reasonPhrase
     */
    public function __construct(int $code = 200, string $reasonPhrase = '')
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * Get raw response
     * @return \Swoole\Http\Response|\Workerman\Connection\TcpConnection|\Swow\Http\Server\Connection|null
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * With raw response
     * @param \Swoole\Http\Response|\Workerman\Connection\TcpConnection|\Swow\Http\Server\Connection $response
     * @return $this
     */
    public function withRawResponse(object $response)
    {
        $this->rawResponse = $response;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSwoole(): bool
    {
        if ($this->rawResponse instanceof \Swoole\Http\Response) {
            return true;
        }
        return false;
    }

    public function isSwow(): bool
    {
        if ($this->rawResponse instanceof \Swow\Http\Server\Connection) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isWorkerMan(): bool
    {
        if ($this->rawResponse instanceof \Workerman\Connection\TcpConnection) {
            return true;
        }
        return false;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * 返回cookies
     * @return Cookie[]
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * 设置cookies
     * @param Cookie[] $cookies
     * @return static
     */
    public function withCookies(array $cookies)
    {
        $this->cookies = [];
        foreach ($cookies as $cookie) {
            $this->withAddedCookie($cookie);
        }
        return $this;
    }

    /**
     * 设置cookie
     * @param $name
     * @param $value
     * @return static
     */
    public function withAddedCookie(Cookie $cookie)
    {
        $this->cookies[] = $cookie;
        return $this;
    }

    /**
     * 设置ContentType
     * @param string $type
     * @param string $charset
     * @return static
     */
    public function withContentType(string $type, string $charset = '')
    {
        $value = $type;
        if ($charset) {
            $value = "{$type}; charset={$charset}";
        }
        return $this->withHeader('Content-Type', $value);
    }

    /**
     * 重定向
     * @param string $location
     * @param int $code
     * @return static
     */
    public function redirect(string $location, int $code = 302)
    {
        return $this->withHeader('Location', $location)->withStatus($code);
    }

    /**
     * 发送响应体，并结束当前请求
     * @return bool
     */
    public function send(): bool
    {
        // 已经发送过的不处理
        if ($this->sended) {
            return false;
        }

        // websocket upgrade 不处理
        if ($this->getStatusCode() == 101) {
            return true;
        }

        if ($this->isSwoole()) {
            return $this->swooleSend();
        } else if ($this->isWorkerMan()) {
            return $this->workerManSend();
        } elseif($this->isSwow()) {
            return $this->swowSend();
        } else {
            return $this->fpmSend();
        }
    }

    /**
     * 发送文件
     * @param string $filename
     * @return bool
     */
    public function sendfile(string $filename): bool
    {
        if ($this->isSwoole()) {
            return $this->swooleSendFile($filename);
        } else if ($this->isWorkerMan()) {
            return $this->workerManSendFile($filename);
        } else if (PHP_SAPI == 'cli-server') {
            // 支持cli-server静态文件
            return $GLOBALS['__sendfile__'] = true;
        } else {
            throw new \RuntimeException('Sendfile can be used only in Swoole, Workerman, PHP CLI-Server');
        }
    }

    /**
     * @return bool
     */
    protected function swooleSend(): bool
    {
        $headers = $this->getHeadersLine();
        foreach ($headers as $key => $value) {
            $this->rawResponse->header($key, $value);
        }

        $cookies = $this->getCookies();
        foreach ($cookies as $cookie) {
            $this->rawResponse->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }

        $status = $this->getStatusCode();
        $this->rawResponse->status($status);
        $body = $this->getBody();
        $content = $body ? $body->getContents() : '';
        $result = $this->rawResponse->end($content);

        $this->sended = true;
        return $result;
    }

    protected function swowSend(): bool
    {
        $headers = $this->getHeaders();
        $body = $this->getBody()->getContents();
        if ($this->rawResponse->getKeepAlive() !== null) {
            $headers['Connection'] = $this->rawResponse->getKeepAlive() ? 'Keep-Alive' : 'Closed';
        }
        if (! $this->hasHeader('Content-Length')) {
            $headers['Content-Length'] = strlen($body);
        }
        $result = $this->rawResponse->write([packResponse(\Swow\Http\Status::OK, $headers), $body]);
        $this->sended = true;
        return $this->sended;
    }

    /**
     * @return bool
     */
    protected function workerManSend(): bool
    {
        $headers = $this->getHeadersLine();
        $cookies = $this->getCookies();

        $status = $this->getStatusCode();
        $body = $this->getBody();
        $content = $body ? $body->getContents() : '';

        // add Date header
        static $timer = null;
        static $cache = '';
        if (is_null($timer)) {
            $func = function () use (&$cache) {
                $cache = gmdate('D, d M Y H:i:s') . ' GMT';
            };
            $func();
            $timer = \Workerman\Timer::add(1, $func);
        }
        $headers['Date'] = $cache;

        $response = new \Workerman\Protocols\Http\Response($status, $headers, $content);
        foreach ($cookies as $cookie) {
            $response->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }
        $result = $this->rawResponse->send($response);

        $this->sended = true;
        return $result;
    }

    /**
     * @return bool
     */
    protected function fpmSend(): bool
    {
        $headers = $this->getHeadersLine();
        $cookies = $this->getCookies();
        $status = $this->getStatusCode();
        $body = $this->getBody();
        $content = $body ? $body->getContents() : '';

        foreach ($headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        foreach ($cookies as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }

        $httpStatus = new \Lukasoppermann\Httpstatus\Httpstatus();
        $httpStatus->setLanguage('en');
        header(sprintf('HTTP/1.1 %d %s', $status, $httpStatus->getReasonPhrase($status)));
        echo $content;

        $this->sended = true;
        return true;
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function swooleSendFile(string $filename): bool
    {
        $headers = $this->getHeadersLine();
        foreach ($headers as $key => $value) {
            $this->rawResponse->header($key, $value);
        }

        // 添加 Last-Modified
        if (!isset($headers['Last-Modified']) && !isset($headers['last-modified'])) {
            if ($mtime = filemtime($filename)) {
                $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
                $this->rawResponse->header('Last-Modified', $lastModified);
            }
        }

        $result = $this->rawResponse->sendfile($filename);

        $this->sended = true;
        return $result;
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function workerManSendFile(string $filename): bool
    {
        $headers = $this->getHeadersLine();

        $response = (new \Workerman\Protocols\Http\Response(200, $headers))->withFile($filename);
        $result = $this->rawResponse->send($response);

        $this->sended = true;
        return (bool)$result;
    }

}
