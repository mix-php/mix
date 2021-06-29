<?php

namespace Mix\Vega;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Trait Input
 * @package Mix\Vega
 */
trait Input
{

    /**
     * @var Response
     */
    public $response;

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * /user/{id} --> GET /user/10000
     * $ctx->param('id') == '10000'
     * @param string $key
     * @return string
     */
    public function param(string $key): string
    {
        return $this->request->getRouteParams()[$key] ?? '';
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
        return $this->request->getQueryParams()[$key] ?? '';
    }

    /**
     * GET /?name=Manu&lastname=
     * $ctx->GetQuery("name") == 'Manu'
     * $ctx->GetQuery("lastname") == ''
     * $ctx->GetQuery("id") == null
     * @param string $key
     * @return mixed|null
     */
    public function getQuery(string $key)
    {
        return $this->request->getQueryParams()[$key] ?? null;
    }

    /**
     * @param string $key
     * @return string
     */
    public function postForm(string $key): string
    {
        return $this->request->getParsedBody()[$key] ?? '';
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultPostForm(string $key, string $default): string
    {
        return $this->request->getParsedBody()[$key] ?? $default;
    }

    /**
     * POST Body: name=Manu&lastname=
     * $ctx->getPostForm("name") == 'Manu'
     * $ctx->getPostForm("lastname") == ''
     * $ctx->getPostForm("id") == null
     * @param string $key
     * @return mixed|null
     */
    public function getPostForm(string $key)
    {
        return $this->request->getParsedBody()[$key] ?? null;
    }

    /**
     * @param string $name
     * @return UploadedFileInterface
     * @throws Exception
     */
    public function formFile(string $name): UploadedFileInterface
    {
        $files = $this->request->getUploadedFiles();
        if (empty($files)) {
            throw new Exception('No files have been uploaded');
        }
        return array_shift($files);
    }

    /**
     * @return UploadedFileInterface[]
     */
    public function multipartForm()
    {
        return $this->request->getUploadedFiles();
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
     * @throws Exception
     */
    public function mustGetJSON(): object
    {
        $result = $this->getJSON();
        if (is_null($result)) {
            throw new Exception('The body is not a valid JSON format');
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
     * @return string
     */
    public function clientIP(): string
    {
    }

    /**
     * @return string
     */
    public function remoteIP(): string
    {
    }

    /**
     * @return string
     */
    public function contentType(): string
    {
    }

    /**
     * @param string $key
     * @return string
     */
    public function header(string $key): string
    {
    }

    /**
     * @param string $name
     * @return string
     */
    public function cookie(string $name): string
    {
    }

    /**
     * @return UriInterface
     */
    public function uri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * @return string
     */
    public function rawData(): string
    {
        $body = $this->request->getBody();
        return $body ? $body->getContents() : '';
    }

}
