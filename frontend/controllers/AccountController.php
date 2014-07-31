<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/30/2014
 * Time: 8:02 AM
 */

namespace frontend\controllers;


use frontend\models\account\DepositForm;
use frontend\models\Controller;
use frontend\models\WechatUser;
use frontend\models\wcg\User as WCGUser;
use yii\web\Response;
use yii\widgets\ActiveForm;

use Yii;

class AccountController extends Controller{
    public function actionIndex($openid = null)
    {
        return $this->redirect('/account/transactions');
    }

    public function actionDeposit($openid = null)
    {
        if ($this->isWechat() || true)
        {
            if (!$openid) \Yii::$app->end();
            if (!WechatUser::login($openid)) \Yii::$app->end();
            if ($wcgUser = WCGUser::fetch())
            {
                if (!$wcgUser->hasCnpnrAccount()) $this->redirect(Yii::$app->urlManager->createAbsoluteUrl('/site/cnpnr'));
            }
            $this->layout = 'wcg';
        }
        $model = new DepositForm();

        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            if ($link = $model->deposit()) {
                return $this->redirect($link);
            }
        }

        return $this->render('deposit', ['model'=>$model]);
    }

    public function actionTransactions($openid = null)
    {
        if ($this->isWechat() || true)
        {
            $this->layout = 'wcg';
            if ($openid) WechatUser::login($openid);
        }
        $wcgUser = WCGUser::fetch();
        $summary = [];
        if ($wcgUser)
        {
            $summary = $wcgUser->getAttributes();
        }
        return $this->render('transactions', ['summary'=>$summary]);
    }

    protected function wechatLogin($openid)
    {
        $wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one();
        if ($wechatUser)
        {
        }
        else
        {
            $this->redirect('site/bind');
        }
    }
}