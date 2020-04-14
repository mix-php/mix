<?php

namespace Mix\JsonRpc\Helper;

use Mix\Context\Context;
use Mix\JsonRpc\Constants;
use Mix\JsonRpc\Exception\DeserializeException;
use Mix\JsonRpc\Message\Error;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class JsonRpcHelper
 * @package Mix\JsonRpc\Helper
 */
class JsonRpcHelper
{

    /**
     * 解析请求
     * @param string $payload
     * @return Request
     * @throws DeserializeException
     */
    public static function deserializeRequestFromHTTP(string $payload)
    {
        return static::deserializeRequest(static::decode($payload));
    }

    /**
     * 解析请求
     * @param string $payload
     * @return Request
     * @throws DeserializeException
     */
    public static function deserializeRequestFromTCP(string $payload)
    {
        return static::deserializeRequest(static::decode($payload));
    }

    /**
     * 解析请求
     * @param array|object $payload
     * @return Request
     * @throws DeserializeException
     */
    public static function deserializeRequestFromProxy($payload)
    {
        if (is_null($payload) || is_array($payload)) {
            throw new DeserializeException('Parse failed');
        }
        return static::deserializeRequest($payload);
    }

    /**
     * 解析请求
     * @param object $payload
     * @return Request
     */
    protected static function deserializeRequest(object $object)
    {
        $request          = new Request();
        $request->context = new Context();
        $request->jsonrpc = $object->jsonrpc ?? null;
        $request->id      = $object->id ?? null;
        $request->method  = $object->method ?? null;
        $request->params  = $object->params ?? null;
        $request->params  = $object->params ?? null;
        return $request;
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
        if (!(is_array($request->params) || is_object($request->params))) {
            return false;
        }
        return true;
    }

    /**
     * Deserialize
     * @param string $payload
     * @return Response
     * @throws DeserializeException
     */
    public static function deserializeResponse(string $payload)
    {
        $object            = static::decode($payload);
        $response          = new Response();
        $response->jsonrpc = $object->jsonrpc ?? null;
        $response->id      = $object->id ?? null;
        $response->method  = $object->method ?? null;
        $response->params  = $object->params ?? null;
        $error             = $object->error ?? null;
        if (!is_null($error)) {
            $code           = $error->code ?? 0;
            $message        = is_string($error) ? $error : ($error->message ?? '');
            $error          = new Error();
            $error->code    = $code;
            $error->message = $message;
        }
        $response->error  = $error;
        $response->result = $object->result ?? null;
        return $response;
    }

    /**
     * Serialize
     * @param Response $response
     * @return string
     */
    public static function serializeResponse(Response $response)
    {
        return static::encode($response) . Constants::EOF;;
    }

    /**
     * Encode
     * @param $value
     * @return string
     */
    public static function encode($value)
    {
        $result = json_encode($value, JSON_UNESCAPED_UNICODE);
        if ($result === false) {
            return '';
        }
        return $result;
    }

    /**
     * Decode
     * 不可使用 $assoc = true，会导致 {} 在多次解编码后变 []
     * @param $value
     * @return object
     * @throws DeserializeException
     */
    public static function decode($value)
    {
        $result = json_decode($value);
        if (is_null($result) || is_array($result)) {
            throw new DeserializeException('Parse failed.');
        }
        return $result;
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
