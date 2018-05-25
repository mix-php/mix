<?php

namespace apps\index\controllers;

use mix\http\Controller;

class ProfileController extends Controller
{

    public $layout = 'main';

    public function actionIndex()
    {
        $data = [
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return $this->render('index', $data);
    }

}
