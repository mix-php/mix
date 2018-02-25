<?php

namespace apps\index\models;

use mix\base\Model;

/**
 * Index 表单模型类
 * 这是一个表单模型的范例，一个表单模型对应一个控制器，表单模型内调用数据模型操作数据库
 * 一个数据模型对应一个数据表，数据表是公用的，所以数据模型应该在公共模块
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexForm extends Model
{

    public $a;
    public $b;
    public $c;
    public $d;
    public $e;
    public $f;
    public $g;
    public $h;
    public $i;
    public $j;
    public $k;
    public $l;
    public $m;
    public $n;
    public $r;

    // 规则
    public function rules()
    {
        return [
            ['a', 'integer', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['b', 'double', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['c', 'alpha', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['d', 'alphaNumeric', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['e', 'string', 'length' => 10, 'minLength' => 3, 'maxLength' => 5, 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
            ['f', 'email', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['g', 'phone'],
            ['h', 'url', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
            ['i', 'in', 'range' => ['A', 'B'], 'strict' => true],
            ['j', 'date', 'format' => 'Y-m-d'],
            ['k', 'compare', 'compareAttribute' => 'a'],
            ['l', 'match', 'pattern' => '/^[\w]{1,30}$/'],
            ['m', 'call', 'callback' => [$this, 'check']],
            ['n', 'file', 'mimes' => ['audio/mp3', 'video/mp4'], 'maxSize' => 1024 * 1],
            ['r', 'image', 'mimes' => ['image/gif', 'image/jpeg', 'image/png'], 'maxSize' => 1024 * 1],
        ];
    }

    // 自定义验证
    public function check($attributeValue)
    {
        return true;
    }

    // 场景
    public function scenarios()
    {
        return [
            'test' => ['required' => ['a', 'b', 'c', 'd'], 'optional' => ['e']],
        ];
    }

    // 属性消息
    public function attributeMessages()
    {
        return [
            //'a' => '只能为数字.',
            //'b' => '只能为小数.',
            //'c' => '只能为字母.',
            //'d' => '只能为字母与数字.',
            //'e' => '长度只能为1~15位.',
        ];
    }

    // 属性标签
    public function attributeLabels()
    {
        return [
            //'a' => '参数A',
            //'b' => '参数B',
            //'c' => '参数C',
            //'d' => '参数D',
            //'e' => '参数E',
        ];
    }

    // 操作数据库
    public function save()
    {
        $tableModel = new \apps\common\models\TableModel();
        $tableModel->insert($this);
    }

}
