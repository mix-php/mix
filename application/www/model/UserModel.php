<?php

/**
 * UserModel类
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\model;

use mix\base\Model;

class UserModel extends Model
{

    // 规则
    public function rules()
    {
        return [
            ['uid', 'call', 'callback' => [$this, 'check']],
        ];
        //return [
        //    ['a', 'integer', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['b', 'double', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['c', 'alpha', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['d', 'alphaNumeric', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['e', 'string', 'length' => 10, 'minLength' => 3, 'maxLength' => 5, 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
        //    ['h', 'email', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['i', 'phone'],
        //    ['j', 'url', 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        //    ['f', 'in', 'range' => ['A', 'B']],
        //    ['g', 'date', 'format' => 'Y-m-d'],
        //    ['k', 'compare', 'compareAttribute' => 'a'],
        //    ['l', 'match', 'pattern' => '/^[\w]{1,30}$/'],
        //    ['m', 'call', 'callback' => [$this, 'check']],
        //];
    }

    // 自定义验证
    public function check()
    {
        return false;
    }

    // 场景
    public function scenarios()
    {
        return [
            'test' => ['required' => ['uid', 'uuid']],
            //'test' => ['required' => ['a', 'b', 'c', 'd'], 'optional' => ['e']],
        ];
    }

    // 属性消息
    public function attributeMessages()
    {
        return [
            //'uid' => '只能为无符号整数.',
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
            //'uid' => '用户UID',
            //'uuid' => '用户UUID',
            //'b' => '参数B',
            //'c' => '参数C',
            //'d' => '参数D',
            //'e' => '参数E',
        ];
    }

}
