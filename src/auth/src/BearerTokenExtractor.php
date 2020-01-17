<?php

namespace Mix\Auth;

use Mix\Auth\Exception\ExtractTokenException;
use Psr\Http\Message\MessageInterface;

/**
 * Class BearerTokenExtractor
 * @package Mix\Auth
 * @author liu,jian <coder.keda@gmail.com>
 */
class BearerTokenExtractor implements TokenExtractorInterface
{

    /**
     * @var MessageInterface
     */
    public $request;

    /**
     * BearerTokenExtractor constructor.
     * @param MessageInterface $request
     */
    public function __construct(MessageInterface $request)
    {
        $this->request = $request;
    }

    /**
     * 提取token
     * @return string
     */
    public function extractToken()
    {
        $authorization = $this->request->getHeaderLine('authorization');
        if (strpos($authorization, 'Bearer ') !== 0) {
            throw new ExtractTokenException('Failed to extract token.');
        }
        return (string)substr($authorization, 7);
    }

}
