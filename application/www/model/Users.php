<?php

/**
 * 模型
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\model;

use sys\Pdo;

class Users
{

    // 减少积分 (手动事务)
    public function minusCreditsManual($uid, $number)
    {
        Pdo::beginTransaction();
        try {
            Pdo::execute(
                'UPDATE `users` SET credits = credits - :number WHERE uid = :uid',
                [
                    'uid' => $uid,
                    'number' => $number,
                ]
            );
            Pdo::execute(
                'INSERT INTO `credits`(`uid`, `number`) VALUES(:uid, :number)',
                [
                    'uid' => $uid,
                    'number' => -$number,
                ]
            );
            // 提交事务
            Pdo::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Pdo::rollBack();
            throw $e;
        }
    }

    // 减少积分 (自动事务)
    public function minusCreditsAuto($uid, $number)
    {
        // 变量转数组
        $data = compact('uid', 'number');
        // 执行事务
        Pdo::transaction(function () use ($data) {
            Pdo::execute(
                'UPDATE `users` SET credits = credits - :number WHERE uid = :uid',
                [
                    'uid' => $data['uid'],
                    'number' => $data['number'],
                ]
            );
            Pdo::execute(
                'INSERT INTO `credits`(`uid`, `number`) VALUES(:uid, :number)',
                [
                    'uid' => $data['uid'],
                    'number' => -$data['number'],
                ]
            );
        }, true);
    }

    // 新增用户
    public function add($userName, $phone, $credits)
    {
        return Pdo::execute(
            'INSERT INTO `users`(`user_name`, `phone`, `credits`) VALUES(:userName, :phone, :credits)',
            [
                'userName' => $userName,
                'phone' => $phone,
                'credits' => $credits,
            ]
        );
    }

}
