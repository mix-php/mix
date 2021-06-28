<?php

namespace Mix\Vega;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Trait Reader
 * @package Mix\Vega
 */
trait Reader
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
     * @param string $key
     * @return string
     */
    public function get(string $key): string
    {
    }

    /**
     * email=mail@example.com  -->  ["mail@example.com", true]
     * email=                  -->  ["", true]
     *                         -->  ["", false]
     * @param string $key
     * @return array
     */
    public function existGet(string $key): array
    {
    }

    /**
     * @param string $key
     * @return string
     */
    public function mustGet(string $key): string
    {
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultGet(string $key, string $default): string
    {
    }

    /**
     * @param string $key
     * @return string
     */
    public function post(string $key): string
    {
    }

    /**
     * email=mail@example.com  -->  ["mail@example.com", true]
     * email=                  -->  ["", true]
     *                         -->  ["", false]
     * @param string $key
     * @return array
     */
    public function existPost(string $key): array
    {
    }

    /**
     * @param string $key
     * @return string
     */
    public function mustPost(string $key): string
    {
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultPost(string $key, string $default): string
    {
    }

    /**
     * @return object
     */
    public function unmarshalJSON(): object
    {
        return static::jsonUnmarshal($this->getRawData());
    }

    /**
     * @param $data
     * @return string
     */
    protected static function jsonUnmarshal($data): object
    {
        return json_decode($data);
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
     * @return string
     */
    public function getRawData(): string
    {
        $body = $this->request->getBody();
        return $body ? $body->getContents() : '';
    }

}
