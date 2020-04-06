<?php

namespace Mix\JsonRpc\Intercept;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class InterceptDispatcher
 * @package Mix\JsonRpc\Intercept
 */
class InterceptDispatcher
{

    /**
     * @var InterceptorInterface[]
     */
    public $interceptors;

    /**
     * InterceptDispatcher constructor.
     * @param array $interceptors
     */
    public function __construct(array $interceptors)
    {
        foreach ($interceptors as $class) {
            $object = $class;
            if (!is_object($class)) {
                $object = new $class(
                    $request,
                    $response
                );
            }
            if (!($object instanceof InterceptorInterface)) {
                throw new TypeException("{$class} type is not '" . InterceptorInterface::class . "'");
            }
            $this->interceptors[] = $object;
        }
    }

    /**
     * Dispatch
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function dispatch(Request $request, Response $response)
    {
        return (new RequestHandler($this->interceptors))->handle($request, $response);
    }

}
