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
            ['uid', 'double', 'unsigned' => true, 'min' => 20, 'max' => 50, 'length' => 10, 'minLength' => 3, 'maxLength' => 5],
        ];
        //return [
        //    ['a', 'integer', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['b', 'double', 'unsigned' => true, 'min' => 1, 'max' => 1000000, 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['c', 'alpha', 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['d', 'alphaNumeric', 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['e', 'string', 'length' => 40, 'minLength' => 1, 'maxLength' => 15, 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
        //    ['f', 'in', 'range' => ['A', 'B']],
        //    ['g', 'date', 'format' => 'yyyy-mm-dd'],
        //    ['h', 'email', 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['i', 'phone', 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['j', 'url', 'length' => 40, 'minLength' => 1, 'maxLength' => 15],
        //    ['k', 'compare', 'compareAttribute' => 'a'],
        //    ['l', 'match', 'pattern' => '/^[\w]{1,30}$/'],
        //    ['m', 'call', 'callback' => [$this, 'checkFile']],
        //];
    }

    // 场景
    public function scenarios()
    {
        return [
            'test' => ['required' => ['uid', 'a']],
            //'test' => ['required' => ['a', 'b', 'c', 'd'], 'optional' => ['e']],
        ];
    }

    // 属性标签
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
            //'uid' => '用户ID',
            //'a' => '参数A',
            //'b' => '参数B',
            //'c' => '参数C',
            //'d' => '参数D',
            //'e' => '参数E',
        ];
    }

}
