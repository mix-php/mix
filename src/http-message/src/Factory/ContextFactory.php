<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Context\Context;

/**
 * Class ContextFactory
 * @package Mix\Http\Message\Factory
 */
class ContextFactory
{

    /**
     * 创建上下文对象
     * @return Context
     */
    public function createContext()
    {
        return new Context();
    }

}
