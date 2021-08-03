<?php

namespace App\Command;

use App\Container\RDS;

/**
 * Class ClearCache
 * @package App\Command
 */
class ClearCache
{

    public function exec(): void
    {
        RDS::instance()->del('foo_cache');
        print 'ok';
    }

}
