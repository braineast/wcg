<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/29/2014
 * Time: 4:26 PM
 */

namespace frontend\models;

use yii\web\Controller as YiiController;


class Controller extends YiiController{
    public function isWechat()
    {
        return strstr(\Yii::$app->request->getUserAgent(), 'MicroMessenger') ? true : false;
    }
}