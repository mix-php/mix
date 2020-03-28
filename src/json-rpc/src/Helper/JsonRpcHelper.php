<?php

namespace Mix\JsonRpc\Helper;

use Mix\JsonRpc\Constants;
use Mix\JsonRpc\Exception\ParseException;
use Mix\JsonRpc\Message\Error;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Swoole\Coroutine\Channel;

/**
 * Class JsonRpcHelper
 * @package Mix\JsonRpc\Helper
 */
class JsonRpcHelper
{

    /**
     * 解析请求
     * 在 Gateway 使用时传入的是 array
     * @param string|array $payload
     * @return array [bool $single, Request[] $requests]
     * @throws ParseException
     */
    public static function parseRequests($payload)
    {
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                is_array($value) and $payload[$key] = (object)$value;
            }
        }
        if (is_string($payload)) {
            $payload = static::decode($payload);
        }
        if (is_null($payload)) {
            throw new ParseException('Parse request failed');
        }
        $requests = [];
        $single   = false;
        if (!is_array($payload)) {
            $single  = true;
            $payload = [$payload];
        }
        foreach ($payload as $value) {
            $request          = new Request();
            $request->jsonrpc = $value->jsonrpc ?? null;
            $request->id      = $value->id ?? null;
            $request->method  = $value->method ?? null;
            $request->params  = $value->params ?? null;
            $requests[]       = $request;
        }
        return [$single, $requests];
    }

    /**
     * 验证请求
     * @param Request $request
     * @return bool
     */
    public static function validRequest(Request $request)
    {
        if (!is_string($request->method)) {
            return false;
        }
        if (!(is_scalar($request->id) || is_null($request->id))) {
            return false;
        }
        if (!is_array($request->params)) {
            return false;
        }
        return true;
    }

    /**
     * 解析请求
     * @param string $payload
     * @return Response[]
     * @throws ParseException
     */
    public static function parseResponses(string $payload)
    {
        $payload = static::decode($payload);
        if (is_null($payload)) {
            throw new ParseException('Parse responses failed.');
        }
        $responses = [];
        if (!is_array($payload)) {
            $payload = [$payload];
        }
        foreach ($payload as $value) {
            $response          = new Response();
            $response->jsonrpc = $value->jsonrpc ?? null;
            $response->id      = $value->id ?? null;
            $response->method  = $value->method ?? null;
            $response->params  = $value->params ?? null;
            $error             = $value->error ?? null;
            if (!is_null($error)) {
                $code           = $error->code ?? 0;
                $message        = is_string($error) ? $error : ($error->message ?? '');
                $error          = new Error();
                $error->code    = $code;
                $error->message = $message;
            }
            $response->error  = $error;
            $response->result = $value->result ?? null;
            $responses[]      = $response;
        }
        return $responses;
    }

    /**
     * 生成响应内容
     * @param bool $single
     * @param Response ...$responses
     * @return string
     */
    public static function content(bool $single, Response ...$responses)
    {
        if ($single) {
            $jsonStr = static::encode(array_pop($responses)) . Constants::EOF;
        } else {
            $jsonStr = static::encode($responses) . Constants::EOF;
        }
        return $jsonStr;
    }

    /**
     * Encode
     * @param $value
     * @return false|string
     */
    public static function encode($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Decode
     * 不可使用 $assoc = true，会导致 {} 在多次解编码后变 []
     * @param $value
     * @return false|array
     */
    public static function decode($value)
    {
        return json_decode($value);
    }

}
