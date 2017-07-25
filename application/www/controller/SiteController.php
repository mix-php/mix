<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use express\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        // \Express::$app->response->statusCode = 404;
        // \Express::$app->response->format = \express\web\Response::FORMAT_JSONP;
        // return ['errcode' => 0, 'errmsg' => 'ok'];

        // return $this->render('index', ['name' => 'xiaoliu', 'sex' => 'nan']);

        // \Express::$app->session->set('user', ['name' => 'xiaoliu', 'sex' => 'nan']);
        // var_dump(\Express::$app->session->get());

        // \Express::$app->cookie->set('user', 'xiaoliu');

        // \Express::$app->redis->set('user', 'xiaoliu');
        // var_dump(\Express::$app->redis->get('user'));

        //$rows = \Express::$app->rdb->createCommand("SELECT * FROM `post` WHERE mobile = :mobile")->bindValue([
        //    ':mobile' => '18600001111'
        //])->queryAll();

        //$insertId = \Express::$app->rdb->insert('post', ['name' => 'xiaoliu', 'content' => 'hahahaha'])->execute();
        //var_dump(\Express::$app->rdb->getLastSql());

        //$data = [
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //];
        //$affectedRows = \Express::$app->rdb->batchInsert('post', $data)->execute();
        //var_dump(\Express::$app->rdb->getLastSql());

        $affectedRows = \Express::$app->rdb->update('post', ['name' => 'liuliu', 'id' => 16], [['id', '=', 15]])->execute();
        var_dump(\Express::$app->rdb->getLastSql());

        return $affectedRows;
    }

    public function actionPhpinfo()
    {
        phpinfo();
    }

}
