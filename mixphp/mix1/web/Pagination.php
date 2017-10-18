<?php

namespace mix\web;

/**
 * Pagination类
 * @author 刘健 <coder.liu@qq.com>
 */
class Pagination
{

    // 内容
    public $items;

    // 总记录数
    public $totalItems;

    // 当前页码
    public $currentPage;

    // 每页数量
    public $perPage;

    // 构造
    public function __construct($config = [])
    {
        // 导入配置
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    // 有首页
    public function hasFirst()
    {
        return $this->currentPage == 1 ? false : true;
    }

    // 有上一页
    public function hasBefore()
    {
        return !is_null($this->before());
    }

    // 有下一页
    public function hasNext()
    {
        return !is_null($this->before());
    }

    // 有尾页
    public function hasLast()
    {
        return $this->currentPage == $this->totalPages() ? false : true;
    }

    // 上一页
    public function before()
    {
        $page = $this->currentPage - 1;
        return $page < 1 ? null : $page;
    }

    // 下一页
    public function next()
    {
        $page = $this->currentPage + 1;
        return $page > $this->totalPages() ? null : $page;
    }

    // 总页数
    public function totalPages()
    {
        return (int)ceil($this->totalItems / $this->perPage);
    }

    // 连续出现的页码集合
    public function groups($number = 5)
    {
        $number      = $number > $this->totalPages() ? $this->totalPages() : $number;
        $leftNumber  = $number / 2;
        $leftNumber  = is_integer($leftNumber) ? ($leftNumber - 1) : (int)floor($leftNumber);
        $rightNumber = $number - $leftNumber - 1;
        $leftShort   = ($this->currentPage - $leftNumber) < 1 ? true : false;
        $rightShort  = ($this->currentPage + $rightNumber) > $this->totalPages() ? true : false;
        $center      = (!$leftShort && !$rightShort) ? true : false;
        $data        = [];
        $numberRange = [];
        // 左边短
        if ($leftShort) {
            var_dump('$leftShort');
            $numberRange = range(1, $number);
        }
        // 右边短
        if ($rightShort) {
            var_dump('$rightShort');
            $startNumber = $this->totalPages() - $number;
            $numberRange = range($startNumber, $startNumber + $number);
        }
        // 居中
        if ($center) {
            var_dump('$center');
            $startNumber = $this->currentPage - $leftNumber;
            $numberRange = range($startNumber, $startNumber + $number - 1);
        }
        // 生成数据
        foreach ($numberRange as $value) {
            $data[] = (object)[
                'number'   => $value,
                'selected' => ($value == $this->currentPage) ? true : false,
            ];
        }
        return $data;
    }

}

$pagination = new Pagination([
    'totalItems'  => 1000,
    'currentPage' => 2,
    'perPage'     => 10,
]);
print_r($pagination->groups(10));
