<?php

namespace Mix\Database\Helper;

/**
 * Class WhereHelper
 * @package Mix\Database\Helper
 * @author liu,jian <coder.keda@gmail.com>
 */
class WhereHelper
{

    /**
     * 是否为多个条件
     * @param $where
     * @return bool
     */
    public static function isMulti($where)
    {
        $multi = true;
        foreach ($where as $item) {
            if (!is_array($item)) {
                $multi = false;
                break;
            }
        }
        return $multi;
    }

}
