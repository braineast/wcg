<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 10/22/2014
 * Time: 1:11 PM
 */

namespace frontend\models\promotion;


use Yii;
use frontend\models\SignupForm as CommonSignupForm;
use yii\base\Exception;

class SignupForm extends CommonSignupForm
{
    public $mobileVerifyCode;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['mobileVerifyCode', 'required', 'message'=>'请输入短信验证码。'];
        $rules[] = ['mobileVerifyCode', 'verifyCode'];
        return $rules;
    }

    public function verifyCode($attribute)
    {
        $code = Yii::$app->session->get('code');
        if ($code)
        {
            $code = json_decode($code, true);
            if ($code['code'] != $this->$attribute || $code['phone'] != $this->mobile)
            {
                $this->addError($attribute, '验证码错误，请重新输入');
            }
        }
        else $this->addError($attribute, '请点击免费获取按钮获取验证码');
    }

    public function actionSendmobilecode()
    {
        try
        {
            $url = sprintf("%s/sendCode/phone-%s", \Yii::$app->params['api']['wcg']['baseUrl'], $this->mobile);
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
        }
        catch(Exception $e)
        {
        }
    }
}