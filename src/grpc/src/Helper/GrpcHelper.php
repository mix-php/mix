<?php

namespace Mix\Grpc\Helper;

/**
 * Class GrpcHelper
 * @package Mix\Grpc\Helper
 */
class GrpcHelper
{

    /**
     * Serialize
     * @param \Google\Protobuf\Internal\Message $message
     * @return string
     */
    public static function serialize(\Google\Protobuf\Internal\Message $message): string
    {
        return static::pack($message->serializeToString());
    }

    /**
     * Deserialize
     * @param \Google\Protobuf\Internal\Message $message
     * @param string $data
     * @throws \Exception
     */
    public static function deserialize(\Google\Protobuf\Internal\Message &$message, string $data)
    {
        $message->mergeFromString(static::unpack($data));
    }

    /**
     * Pack
     * @param string $data
     * @return string
     */
    public static function pack(string $data): string
    {
        return pack('CN', 0, strlen($data)) . $data;
    }

    /**
     * Unpack
     * @param string $data
     * @return string
     */
    public static function unpack(string $data): string
    {
        // it's the way to verify the package length
        // 1 + 4 + data
        // $len = unpack('N', substr($data, 1, 4))[1];
        // assert(strlen($data) - 5 === $len);
        return (string)substr($data, 5);
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
