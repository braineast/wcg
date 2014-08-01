<?php
namespace frontend\controllers;

use common\models\User;
use frontend\models\api\ChinaPNR;
use frontend\models\WechatUser;
use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\widgets\ActiveForm;
use frontend\models\Controller;

use frontend\models\wcg\User as WCGUser;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionBind($openid)
    {
        if ($wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
        {
            exit('您已经绑定了账号');
        }
        else
        {
            $this->layout = 'wcg';
            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post())) {
                $url = sprintf("%s/login/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode(['username'=>$model->username, 'password'=>md5($model->password), 'login_ip'=>Yii::$app->request->userIP])));
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                $result = Json::decode($result, true);
                if ($result['result'] == 0 && $result['errors']['code'] == 0)
                {
                    $userData = $result['data'];
                    if (WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one()) exit('您要绑定的旺财谷账号已经被其他微信号绑定！');
                    $signup = new SignupForm();
                    $signup->username = $userData['username'];
                    $signup->email = $userData['email'];
                    $signup->mobile = $userData['phone'];
                    $signup->password = $model->password;
                    $signup->repeatpassword = $model->password;
                    $user = \frontend\models\User::create($signup->attributes);
                    if ($user)
                    {
                        WCGUser::bind(['id'=>$user->id, 'wcg_uid'=>$userData['id']]);
                        Yii::$app->getUser()->login($user);
                        WechatUser::create(['user_id'=>$user->id, 'open_id'=>$openid]);
                        $wcgUser = WCGUser::fetch();
                        if ($wcgUser && !$wcgUser->hasCnpnrAccount()) return $this->redirect('site/cnpnr');
                        return $this->goHome();
                    }
                }
                else  return $this->render('wcg/login', ['model' => $model,'openid'=>$openid]);
                return $this->goBack();
            } else {
                return $this->render('wcg/login', [
                    'model' => $model,'openid'=>$openid
                ]);
            }
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup($openid = null)
    {
        if ($this->isWechat() && !$openid) Yii::$app->end();
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $user = $model->signup();
            if ($user) {
                if (Yii::$app->getUser()->login($user)) {
                    WechatUser::create(['user_id'=>$user->id, 'open_id'=>$openid]);
                    return $this->redirect('site/cnpnr');
                    return $this->goHome();
                }
            }
        }

        if ($this->isWechat() || true)
        {
            $this->layout = 'wcg';
            return $this->render('/user/signup', ['model'=>$model, 'openid'=>$openid]) ;
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionProducts()
    {
        $this->layout = 'wcg';
        return $this->render('products');
    }

    public function actionCnpnr()
    {
        $wcgUser = WCGUser::fetch();
        if ($wcgUser)
        {
            if (!$wcgUser->hasCnpnrAccount())
            {
                $cnpnr = new ChinaPNR(Yii::$app->request->hostInfo);
                $cnpnr->open();
                $cnpnr->usrid = $wcgUser->userinfo['username'];
                $cnpnr->usrmp = $wcgUser->userinfo['phone'];
                $cnpnr->usremail = $wcgUser->userinfo['email'];
                $cnpnr->merPriv = json_encode(['id'=>Yii::$app->getUser()->getId(),'username'=>$wcgUser->userinfo['username']]);
                $link = $cnpnr->getLink();
                if ($this->isWechat() || true) $this->layout = 'wcg';
                return $this->render('cnpnr/open', ['link'=>$link]);
            }
            return $wcgUser;
        }
        return false;
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
