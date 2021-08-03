<?php

namespace App\Service;

use Mix\Grpc\Context;
use Php\Micro\Grpc\Greeter\Request;
use Php\Micro\Grpc\Greeter\Response;

/**
 * Class Say
 * @package App\Service
 */
class Say implements \Php\Micro\Grpc\Greeter\SayInterface
{

    /**
     * @param Context $context
     * @param Request $request
     * @return Response
     */
    public function Hello(Context $context, Request $request): Response
    {
        $response = new Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}
