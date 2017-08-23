<?php

/**
 * 控制器
 * @author 刘健 <code.liu@qq.com>
 */

namespace www\controller;

use mix\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        // \Mix::$app->response->statusCode = 404;
        // \Mix::$app->response->format = \mix\web\Response::FORMAT_JSONP;
        // return ['errcode' => 0, 'errmsg' => 'ok'];

        // return $this->render('index', ['name' => 'xiaoliu', 'sex' => 'nan']);

        // \Mix::$app->session->set('user', ['name' => 'xiaoliu', 'sex' => 'nan']);
        // var_dump(\Mix::$app->session->get());

        // \Mix::$app->cookie->set('user', 'xiaoliu');

        // \Mix::$app->redis->set('user', 'xiaoliu');
        // var_dump(\Mix::$app->redis->get('user'));

        //$rows = \Mix::$app->rdb->createCommand("SELECT * FROM `post` WHERE mobile = :mobile")->bindValue([
        //    ':mobile' => '18600001111'
        //])->queryAll();

        //$insertId = \Mix::$app->rdb->insert('post', ['name' => 'xiaoliu', 'content' => 'hahahaha'])->execute();
        //var_dump(\Mix::$app->rdb->getLastSql());

        //$data = [
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //    ['name' => 'xiaoliu', 'content' => 'hahahaha'],
        //];
        //$affectedRows = \Mix::$app->rdb->batchInsert('post', $data)->execute();
        //var_dump(\Mix::$app->rdb->getLastSql());

        //$affectedRows = \Mix::$app->rdb->update('post', ['name' => 'liuliu', 'id' => 16], [['id', '=', 15]])->execute();
        //var_dump(\Mix::$app->rdb->getLastSql());

        //$affectedRows = \Mix::$app->rdb->delete('post', [['id', '=', 15]])->execute();
        //var_dump(\Mix::$app->rdb->getLastSql());

        //$rows = \Mix::$app->rdb->createCommand("SELECT * FROM `post` WHERE id IN (:id)")->bindValue([
        //    'id' => [15, 16],
        //])->queryAll();
        //var_dump(\Mix::$app->rdb->getLastSql());
        //return $rows;

        //$model = new \www\model\UserModel();
        //$model->attributes = \Mix::$app->request->get() + \Mix::$app->request->post();
        //$model->setScenario('test');
        //if (!$model->validate()) {
        //    return ['code' => 1, 'message' => '参数格式效验失败', 'data' => $model->errors];
        //}
        //$model->uid->saveAs(\Mix::$app->getPublicPath() . 'uploads/2017/08/' . $model->uid->getRandomName());
        //return ['code' => 0, 'message' => 'OK', 'data' => $model->attributes];

        //$rows = \Mix::$app->rdb->createCommand([
        //    ["SELECT *"],
        //    ["FROM `post`"],
        //    ["WHERE id = :id", 'values' => ['id' => 12], 'where' => true],
        //])->queryAll();
        //
        //\Mix::$app->rdb->queryBuilder(["SELECT *"]);
        //\Mix::$app->rdb->queryBuilder(["FROM `post`"]);
        //\Mix::$app->rdb->queryBuilder(["WHERE id = :id", 'values' => ['id' => 15], 'where' => true]);
        //$rows = \Mix::$app->rdb->createCommand()->queryAll();
        //return \Mix::$app->rdb->getLastSql();

        //$filename = \Mix::$app->getPublicPath() . 'img001.jpg';
        //$thumbname = str_replace('.', '.thumb.', $filename);
        //\mix\web\Image::open($filename)->crop(200, 200, \mix\web\Image::CROP_TOP)->save();
        //return \mix\web\Image::open($filename)->getSize();
    }

    public function actionPhpinfo()
    {
        phpinfo();
    }

}
