<?php

namespace App\Http\Models;

use App\Http\Forms\UserForm;
use Mix\Database\Pool\ConnectionPool;

/**
 * Class UserModel
 * @package App\Http\Models
 * @author liu,jian <coder.keda@gmail.com>
 */
class UserModel
{

    /**
     * @var ConnectionPool
     */
    public $pool;

    /**
     * UserModel constructor.
     */
    public function __construct()
    {
        $this->pool = context()->get('dbPool');
    }

    /**
     * 新增用户
     * @param UserForm $model
     * @return bool|string
     */
    public function add(UserForm $form)
    {
        $db       = $this->pool->getConnection();
        $status   = $db->insert('user', [
            'name'  => $form->name,
            'age'   => $form->age,
            'email' => $form->email,
        ])->execute();
        $insertId = $status ? $db->getLastInsertId() : false;
        $db->release();
        return $insertId;
    }

}
