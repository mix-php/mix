<?php

namespace Mix\Auth;

/**
 * Interface TokenExtractorInterface
 * @package Mix\Auth
 * @author liu,jian <coder.keda@gmail.com>
 */
interface TokenExtractorInterface
{

    /**
     * 提取token
     * @return string
     */
    public function extractToken();

}
