<?php

namespace Http\Controllers;

use Mix\View\View;

/**
 * Class ProfileController
 * @package Http\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class ProfileController
{

    /**
     * 布局
     * @var string
     */
    public $layout = 'main';

    /**
     * 默认动作
     * @return string
     */
    public function actionIndex()
    {
        $data = [
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        $view = new View([
            'layout' => 'main',
        ]);
        return $view->render('profile.index', $data);
    }

}
