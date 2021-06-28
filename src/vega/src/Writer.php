<?php

namespace Mix\Vega;

use Mix\Http\Message\Stream\StringStream;

/**
 * Trait Writer
 * @package Mix\Vega
 */
trait Writer
{

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
     * @param $data
     * @return bool
     */
    public function json(int $code, $data): bool
    {
        $body = new StringStream(static::jsonMarshal($data));
        $this->response->withContentType('application/json');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param $data
     * @return bool
     */
    public function jsonp(int $code, $data): bool
    {
        $callback = $this->defaultQuery('callback', '');
        if ($callback == '') {
            return $this->json($code, $data);
        }

        $json = static::jsonMarshal($data);
        $javascript = sprintf('%s(%s)', $callback, $json);
        $body = new StringStream($javascript);
        $this->response->withContentType('application/javascript');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultQuery(string $key, string $default): string
    {
        $value = $this->request->getQueryParams()[$key] ?? '';
        if ($value) {
            return $value;
        }
        return $default;
    }

    /**
     * @param $data
     * @return string
     */
    protected static function jsonMarshal($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
