<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class UriFactory
 * @package Mix\Http\Message\Factory
 */
class UriFactory implements UriFactoryInterface
{

    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }

}
