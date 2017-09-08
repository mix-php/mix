<?php

/**
 * Mix类
 * @author 刘健 <code.liu@qq.com>
 */

class Mix
{

	// 应用池
	public static $apps;

    // 主机名称
    public static $host;

    /**
     * 
     * @param  string $name
     */
    public function __get($name)
    {
    	if($name == 'app'){
    		return $this->apps[$host];
    	}
    	return null;
    }

}
