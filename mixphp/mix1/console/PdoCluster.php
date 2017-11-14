<?php

namespace mix\console;

use mix\base\Component;

/**
 * PdoCluster组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PdoCluster extends \mix\rdb\PdoCluster
{

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->close();
    }

}
