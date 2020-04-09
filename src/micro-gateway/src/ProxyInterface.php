<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Gateway\Exception\ProxyException;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Interface ProxyInterface
 * @package Mix\Micro\Gateway
 */
interface ProxyInterface
{

    /**
     * Get handle pattern
     * @return string
     */
    public function pattern();

    /**
     * Get service
     *
     * @param RegistryInterface $registry
     * @param ServerRequest $request
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(RegistryInterface $registry, ServerRequest $request);

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws ProxyException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response);

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Throwable $exception, Response $response);

    /**
     * 500 处理,
     * @param Response $response
     * @param \Throwable $exception
     * @return void
     */
    public function show500(\Throwable $exception, Response $response);

    /**
     * Close
     */
    public function close();

}
