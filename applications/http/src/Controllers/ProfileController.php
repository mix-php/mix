<?php

namespace Http\Controllers;

use Mix\Http\View\ViewTrait;
use Mix\Http\Message\Request;
use Mix\Http\Message\Response;

/**
 * Class ProfileController
 * @package Http\Controllers
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class ProfileController
{

    /**
     * 引用视图特性
     */
    use ViewTrait;

    /**
     * 布局
     * @var string
     */
    public $layout = 'main';

    /**
     * 默认动作
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function actionIndex(Request $request, Response $response)
    {
        $data = [
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return $this->render('index', $data);
    }

}
