<?php

namespace App\Http\Helpers;

use Mix\Helper\JsonHelper;
use Mix\Helper\XmlHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\View\View;

/**
 * Class SendHelper
 * @package App\Http\Helpers
 * @author liu,jian <coder.keda@gmail.com>
 */
class SendHelper
{

    /**
     * view
     * @param Response $response
     * @param string $name
     * @param array $data
     * @param string $layout
     * @return Response
     */
    public static function view(Response $response, string $name, array $data = [], string $layout = 'main')
    {
        $dir     = app()->basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR . 'views';
        $view    = new View($dir, $layout);
        $content = $view->render($name, $data);
        return static::html($response, $content);
    }

    /**
     * html
     * @param Response $response
     * @param string $content
     * @return Response
     */
    public static function html(Response $response, string $content)
    {
        $body = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/html', 'utf-8')
            ->withBody($body);
    }

    /**
     * json
     * @param Response $response
     * @param array $content
     * @return Response
     */
    public static function json(Response $response, array $content)
    {
        $body = (new StreamFactory())->createStream(JsonHelper::encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body);
    }

    /**
     * xml
     * @param Response $response
     * @param array $content
     * @return Response
     */
    public static function xml(Response $response, array $content)
    {
        $body = (new StreamFactory())->createStream(XmlHelper::encode($content));
        return $response
            ->withContentType('application/xml', 'utf-8')
            ->withBody($body);
    }

}
