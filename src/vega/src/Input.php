<?php

namespace Mix\Vega;

use Mix\Http\Message\ServerRequest;
use Mix\Vega\Exception\RuntimeException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Trait Input
 * @package Mix\Vega
 */
trait Input
{

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @return ServerRequest
     */
    protected function request(): ServerRequest
    {
        if ($this->request instanceof ServerRequest) {
            return $this->request;
        }
        throw new \RuntimeException('Fast mode does not support this method');
    }

    /**
     * /user/{id} --> GET /user/10000
     * $ctx->param('id') == '10000'
     * @param string $key
     * @return string
     */
    public function param(string $key): string
    {
        return $this->request()->getRouteParams()[$key] ?? '';
    }

    /**
     * GET /user/10000?uid=1234&name=Manu&value=
     * $ctx->query('id') == '10000'
     * $ctx->query('name') == 'Manu'
     * @param string $key
     * @return string
     */
    public function query(string $key): string
    {
        return $this->request()->getQueryParams()[$key] ?? '';
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultQuery(string $key, string $default): string
    {
        return $this->request()->getQueryParams()[$key] ?? $default;
    }

    /**
     * GET /?name=Manu&lastname=
     * $ctx->GetQuery("name") == 'Manu'
     * $ctx->GetQuery("lastname") == ''
     * $ctx->GetQuery("id") == null
     * @param string $key
     * @return string|null
     */
    public function getQuery(string $key)
    {
        return $this->request()->getQueryParams()[$key] ?? null;
    }

    /**
     * @param string $key
     * @return string
     */
    public function postForm(string $key): string
    {
        return $this->request()->getParsedBody()[$key] ?? '';
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultPostForm(string $key, string $default): string
    {
        return $this->request()->getParsedBody()[$key] ?? $default;
    }

    /**
     * POST Body: name=Manu&lastname=
     * $ctx->getPostForm("name") == 'Manu'
     * $ctx->getPostForm("lastname") == ''
     * $ctx->getPostForm("id") == null
     * @param string $key
     * @return string|null
     */
    public function getPostForm(string $key)
    {
        return $this->request()->getParsedBody()[$key] ?? null;
    }

    /**
     * @param string $name
     * @return UploadedFileInterface
     * @throws RuntimeException
     */
    public function formFile(string $name): UploadedFileInterface
    {
        $files = $this->request()->getUploadedFiles();
        if (empty($files)) {
            throw new RuntimeException('No uploaded files were found');
        }
        return array_shift($files);
    }

    /**
     * @return UploadedFileInterface[]
     */
    public function multipartForm()
    {
        return $this->request()->getUploadedFiles();
    }

    /**
     * @return object|null
     */
    public function getJSON()
    {
        $result = static::jsonUnmarshal($this->rawData());
        if (empty($result) || !is_object($result)) {
            return null;
        }
        return $result;
    }

    /**
     * @return object
     * @throws RuntimeException
     */
    public function mustGetJSON(): object
    {
        $result = $this->getJSON();
        if (is_null($result)) {
            throw new RuntimeException('The body is not a valid JSON format');
        }
        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected static function jsonUnmarshal($data)
    {
        return json_decode($data);
    }

    /**
     * X-Forwarded-For X-Real-IP
     * @return string
     */
    public function clientIP(): string
    {
        $rorwarded = $this->request()->getHeaderLine('x-forwarded-for') ?? '';
        $real = $this->request()->getHeaderLine('x-real-ip') ?? '';
        if ($rorwarded == '') {
            return $real;
        }
        list($first) = explode(',', $rorwarded, 2);
        return $first;
    }

    /**
     * @return string
     */
    public function remoteIP(): string
    {
        return $this->request()->getHeaderLine('remote_addr') ?? '';
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->request()->getMethod();
    }

    /**
     * @return string
     */
    public function contentType(): string
    {
        return $this->request()->getHeaderLine('content-type');
    }

    /**
     * @param string $key
     * @return string
     */
    public function header(string $key): string
    {
        return $this->request()->getHeaderLine($key);
    }

    /**
     * @param string $name
     * @return string
     */
    public function cookie(string $name): string
    {
        return $this->request()->getCookieParams()[$name] ?? '';
    }

    /**
     * @return UriInterface
     */
    public function uri(): UriInterface
    {
        return $this->request()->getUri();
    }

    /**
     * @return string
     */
    public function rawData(): string
    {
        $body = $this->request()->getBody();
        return $body ? $body->getContents() : '';
    }

}
