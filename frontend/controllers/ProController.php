<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 10/22/2014
 * Time: 12:52 PM
 */

namespace frontend\controllers;


use frontend\models\promotion\SignupForm;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
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

    public function actionFetchverifycode($mobile)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $signupForm = new SignupForm();
        $signupForm->mobile = $mobile;
        if (ActiveForm::validate($signupForm, 'mobile')) return false;
        try
        {
            $url = sprintf("%s/sendCode/phone-%s", \Yii::$app->params['api']['wcg']['baseUrl'], $mobile);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data, true);
            if ($data)
            {
                if ($data['result'] == 0 && $data['errors']['code'] == 0)
                {
                    $data = $data['data'];
                    $session = Yii::$app->session;
                    $session->set('code', json_encode($data));
                    $session->setTimeout(300);
                }
            }
                return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
}