<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Request;
use Mix\Micro\Etcd\Service\Value;

/**
 * Class RequestFactory
 * @package Mix\Micro\Etcd\Factory
 */
class RequestFactory
{

    /**
     * Create request
     * @param \ReflectionParameter $parameter
     * @return Request
     */
    public function createRequest(\ReflectionParameter $parameter): Request
    {
        $name    = basename(str_replace('\\', '/', $parameter->getClass()->getName()));
        $type    = $parameter->getClass()->getName();
        $request = new Request($name, $type);

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
            $request->withValue($value);
        }

        return $request;
    }

}
