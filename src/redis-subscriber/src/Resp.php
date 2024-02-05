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
     * build resp text
     *
     * @param int|string|array<mixed>|null $args
     * @return string the serialized string
     */
    public static function build(mixed $args): string
    {
        if ($args == 'ping') {
            return "PING\r\n";
        }

        switch (true) {
            case is_null($args):
                return "$-1\r\n";
            case is_int($args):
                return ':' . $args . "\r\n";
            case is_string($args):
                return '$' . strlen($args) . "\r\n" . $args . "\r\n";
            case is_array($args):
                $result = '*' . count($args) . "\r\n";
                foreach ($args as $arg) {
                    $result .= static::build($arg);
                }
                return $result;
            default:
                throw new Exception('invalid args');
        }
    }

}
