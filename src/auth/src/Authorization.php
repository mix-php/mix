<?php

namespace Mix\Auth;

/**
 * Class Authorization
 * @package Mix\Auth
 * @author liu,jian <coder.keda@gmail.com>
 */
class Authorization
{

    /**
     * jwt
     * @var JWT
     */
    public $jwt;

    /**
     * Authorization constructor.
     * @param JWT $jwt
     */
    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * 获取有效荷载
     * @param TokenExtractorInterface $tokenExtractor
     * @return array
     */
    public function getPayload(TokenExtractorInterface $tokenExtractor)
    {
        $token = $tokenExtractor->extractToken();
        return $this->jwt->parse($token);
    }

    /**
     * 创建token
     * @param array $payload
     * @return string
     */
    public function createToken(array $payload)
    {
        return $this->jwt->create($payload);
    }

}
