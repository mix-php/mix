<?php

declare(strict_types=1);

namespace Mix\Redis\Subscriber;

use Exception;

use function count;
use function is_array;
use function is_int;
use function is_null;
use function is_string;
use function strlen;

class Resp
{
    /**
     * CRLF
     */
    public const CRLF = "\r\n";

    /**
     * build resp text
     *
     * @param int|string|array<mixed>|null $args
     * @return string the serialized string
     */
    public static function build(mixed $args): string
    {
        if ($args == 'ping') {
            return "PING" .  static::CRLF;
        }

        switch (true) {
            case is_null($args):
                return "$-1". static::CRLF;
            case is_int($args):
                return ':' . $args .  static::CRLF;
            case is_string($args):
                return '$' . strlen($args) .  static::CRLF . $args . static::CRLF;
            case is_array($args):
                $result = '*' . count($args) . static::CRLF;
                foreach ($args as $arg) {
                    $result .= static::build($arg);
                }
                return $result;
            default:
                throw new Exception('invalid args');
        }
    }

}
