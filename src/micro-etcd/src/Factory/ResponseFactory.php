<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Request;
use Mix\Micro\Etcd\Service\Response;
use Mix\Micro\Etcd\Service\Value;

/**
 * Class ResponseFactory
 * @package Mix\Micro\Etcd\Factory
 */
class ResponseFactory
{

    /**
     * Create response
     * @param \ReflectionClass $class
     * @return Response
     */
    public function createResponse(\ReflectionClass $class): Response
    {
        $name     = basename(str_replace('\\', '/', $class->getName()));
        $type     = $class->getName();
        $response = new Response($name, $type);

        $methods = get_class_methods($type);
        foreach ($methods as $method) {
            if (strpos($method, 'get') !== 0) {
                continue;
            }

            $reflectClass  = new \ReflectionClass($type);
            $reflectMethod = $reflectClass->getMethod($method);
            $doc           = $reflectMethod->getDocComment();
            $start         = strpos($doc, '<code>');
            $end           = strpos($doc, '</code>');
            $code          = substr($doc, $start + 6, $end - $start - 6);

            $slice        = explode(' ', $code);
            $propertyType = array_shift($slice);
            $propertyName = array_shift($slice);

            $value = new Value($propertyName, $propertyType);
            $response->withValue($value);
        }

        return $response;
    }

}
