<?php

namespace Http\Helpers;

use Mix\Helper\JsonHelper;
use Mix\Helper\XmlHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\Factory\StreamFactory;

/**
 * Class SendHelper
 * @package Http\Helpers
 * @author liu,jian <coder.keda@gmail.com>
 */
class SendHelper
{

    /**
     * html
     * @param string $content
     * @param Response $response
     * @return Response
     */
    public static function html(string $content, Response $response)
    {
        $body = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/html', 'utf-8')
            ->withBody($body);
    }

    /**
     * json
     * @param array $content
     * @param Response $response
     * @return Response
     */
    public static function json(array $content, Response $response)
    {
        $body = (new StreamFactory())->createStream(JsonHelper::encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body);
    }

    /**
     * xml
     * @param array $content
     * @param Response $response
     * @return Response
     */
    public static function xml(array $content, Response $response)
    {
        $body = (new StreamFactory())->createStream(XmlHelper::encode($content));
        return $response
            ->withContentType('application/xml', 'utf-8')
            ->withBody($body);
    }

}
