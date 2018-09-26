<?php

namespace apps\common\models;

use mix\facades\PDO;

/**
 * 数据表模型类
 * 这是一个数据模型范例 (关系型数据库)
 * 一个数据模型对应一个数据表，数据表是公用的，所以数据模型应该在公共模块
 * 数据模型是使用组件操作数据库，所以不需要继承任何基类
 * @author 刘健 <coder.liu@qq.com>
 */
class TableModel
{

    const TABLE = 'table';

    // 操作数据库
    public function insert($data)
    {
        $success = PDO::insert(self::TABLE, $data)->execute();
        return $success ? PDO::getLastInsertId() : false;
    }

    // 获取全部记录
    public function getAll()
    {
        return PDO::createCommand("SELECT * FROM `" . self::TABLE . "`")->queryAll();
    }

}