<?php

namespace Mix\Micro\Gateway;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Exception\Gateway\ProxyException;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;

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
     * @return int status
     * @throws ProxyException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response);

    /**
     * 404 处理
     * @param \Exception $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Exception $exception, Response $response);

    /**
     * 500 处理,
     * @param Response $response
     * @param \Exception $exception
     * @return void
     */
    public function show500(\Exception $exception, Response $response);

    /**
     * Close
     */
    public function close();

}
