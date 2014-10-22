<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 10/22/2014
 * Time: 12:52 PM
 */

namespace frontend\controllers;


use frontend\models\promotion\SignupForm;
use yii\web\Controller;
use Yii;

class ProController extends Controller
{
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $user = $model->signup();
            if ($user) {
                if (Yii::$app->getUser()->login($user, 3600 * 24 * 365 * 10)) {
                    return $this->redirect('site/cnpnr');
                }
            }
        }

        $this->layout = 'wcg';
        return $this->render('/pro/signup', ['model'=>$model]);
    }
}