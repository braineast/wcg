<?php
namespace frontend\controllers;

use common\models\User;
use frontend\models\api\ChinaPNR;
use frontend\models\TenderForm;
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
use yii\helpers\Html;

use frontend\models\wcg\User as WCGUser;

/**
 * Site controller
 */
class SiteController extends Controller
{
    const DEAL_PERIOD_TYPE_DAY = 'D';
    const DEAL_PERIOD_TYPE_MONTH = 'M';
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
        return $this->redirect('/site/bind');
        return $this->render('index');
    }

    public function actionLogin($openid = null)
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

    public function actionBind($openid = null)
    {
        $wechatUser = null;
        $wcgUser = null;
        $user = null;
        if (Yii::$app->user->getReturnUrl() == Yii::$app->getHomeUrl()) Yii::$app->user->setReturnUrl('/account/transactions');
        if ($openid)
        {
            if ($wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
            {
                if ($wcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>$wechatUser->getAttribute('user_id')])->one())
                {
                    if ($user = \frontend\models\User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one())
                    {
                        Yii::$app->user->login($user);
                        return $this->goBack();
                    }
                }
            }
        }

        $this->layout = 'wcg';
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if (!$model->username)
                {
                    $error[Html::getInputId($model, 'password')] = [Yii::t('yii', '用户名不允许为空。')];
                }elseif (!$model->password) $error[Html::getInputId($model, 'password')] = [Yii::t('yii', '密码不允许为空。')];
                else $error[Html::getInputId($model, 'password')] = [];
                return $error;
            }
            //于旺财谷WEB接口登录
            $url = sprintf("%s/login/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode(['username'=>$model->username, 'password'=>md5($model->password), 'login_ip'=>Yii::$app->request->userIP])));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = Json::decode($result, true);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($result['result'] != 0 || $result['errors']['code'] != 0)
                {
                    $error[Html::getInputId($model, 'password')] = [Yii::t('yii', 'Incorrect username or password.')];
                    return $error;
                }else return [];
            }
            if ($result['result'] == 0 && $result['errors']['code'] == 0)
            {
                $userData = $result['data'];
                if ($wcgUser = WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one())
                {
                    $user = \frontend\models\User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one();
                    if ($openid)
                    {
                        if ($wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid]))
                        {
                            if ($_compareWcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>$wechatUser->getAttribute('user_id')])->one())
                            {
                                if ($wcgUser->getAttribute('wcg_uid') == $_compareWcgUser->getAttribute('wcg_uid'))
                                {
                                    Yii::$app->user->login(\frontend\models\User::find()->where('id=:userId', [':userId'=>$wcgUser->getAttribute('user_id')])->one());
                                    return $this->goBack();
                                }
                                else
                                    $model->addError('password', '您的微信账号已绑定了一个旺财谷平台账户，请正确登录账户信息。');
                            }
                        }
                        else WechatUser::create(['open_id'=>$openid, 'user_id'=>$wcgUser->getAttribute('user_id')]);
                    }
                    if ($user)
                    {
                        Yii::$app->user->login($user);
                        return $this->goBack();
                    }
                }
                else
                {
                    $signup = new SignupForm();
                    $signup->username = $userData['username'];
                    $signup->email = $userData['email'];
                    $signup->mobile = $userData['phone'];
                    $signup->password = $model->password;
                    $signup->repeatpassword = $model->password;
                    if ($user = \frontend\models\User::create($signup->attributes))
                    {
                        $wcgUser = WCGUser::bind(['id'=>$user->getAttribute('id'), 'wcg_uid'=>$userData['id']]);
                    }
                }
                $wcgUser = WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one();
                if ($wcgUser)
                {
                    if ($user = \frontend\models\User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one())
                    {
                        Yii::$app->user->login($user);
                    }
                }
                if ($wcgUser)
                {
                    if ($openid && !$wechatUser)
                    {
                        WechatUser::create(['open_id'=>$openid, 'user_id'=>$wcgUser->getAttribute('user_id')]);
                    }
                }
                if ($openid)
                {
                    $_wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one();
                    if ($_wechatUser)
                    {
                        if ($_wcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>$_wechatUser->getAttribute('user_id')])->one())
                        {
                            if ($_wcgUser->getAttribute('wcg_uid') != $result['data']['id'])
                                return $this->redirect('/site/notice?type=refuse&subject=系统提示&message=您好，请使用微信账号绑定的旺财谷用户进行登录。');
                        }
                    }
                    else
                    {
                        if ($wcgUser = WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one())
                        {
                            WechatUser::create(['user_id'=>$wcgUser->getAttribute('user_id'), 'open_id'=>$openid]);
                            //该用户在旺财谷登录成功，并已经绑定了微信账号，那么，本地登录
                            $user = User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one();
                            if ($user) Yii::$app->user->login($user, 3600 * 24 * 365 * 10);
                            WCGUser::fetch($user->id);
                            return $this->goBack();
                        }
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
                            if ($this->isWechat() && $openid) WechatUser::create(['user_id'=>$user->id, 'open_id'=>$openid]);
                            $wcgUser = WCGUser::fetch($user->id);
                            Yii::$app->getUser()->login($user, 3600 * 24 * 365 * 10);
                            if ($wcgUser && !$wcgUser->hasCnpnrAccount()) return $this->redirect('site/cnpnr');
                            return $this->redirect('/site/notice?type=open');
                        }
                    }
                }
                //该用户在旺财谷登录成功，并已经绑定了旺财谷账号和微信账号，那么，本地登录
                $wcgUser = WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one();
                if ($wcgUser) $user = User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one();
                if ($user) Yii::$app->user->login($user, 3600 * 24 * 365 * 10);
                return $this->goBack();
            }
            else {
                $model->addError('password', Yii::t('yii', 'Incorrect username or password.'));
            }
        }
        return $this->render('wcg/login', [
            'model' => $model,'openid'=>$openid ? $openid : false
        ]);
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
        $this->layout = 'wcg';
        return $this->render('about');
    }

    public function actionSafe()
    {
        $this->layout = 'wcg';
        return $this->render('safe');
    }

    public function actionNotice($type = null, $subject = null, $message = null)
    {
        $this->layout = 'wcg';
        if (!$message)
        {
            switch($type)
            {
                case 'open':
                    $subject = sprintf("恭喜！");
                    $message = sprintf("您已经完成了平台账户绑定。");
                    break;
                case 'deposit':
                    $subject = sprintf("恭喜！");
                    $message = sprintf("您的充值已经成功。");
                    break;
                case 'tender':
                    $subject = $subject ? $subject : '恭喜！';
                    $message = $message ? $message : sprintf("您的投标已完成，您可以查询您的交易记录进行确认，也可以继续投资！");
                    break;
                case 'default':
                    $subject = '操作已完成';
                    $message = '平台已经完成指定的操作，请稍后查询记录，以确保操作无误，有任何疑问请随时与客服联络！';
                    break;
            }
        }
        return $this->render('notice', ['type'=>$type,'subject'=>$subject,'message'=>$message]);
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
                if (Yii::$app->getUser()->login($user, 3600 * 24 * 365 * 10)) {
                    if ($openid) WechatUser::create(['user_id'=>$user->id, 'open_id'=>$openid]);
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

    public function actionTender()
    {
        //获取投标订单号
        $uid = 2;
        $deal_id = 19;
        $money = 1000.00;
        $data = ['uid'=>$uid, 'deal_id'=>$deal_id, 'money'=>$money];
        $url = sprintf("%s/deal_order/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(json_encode($data)));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump(json_decode($result, true));
    }

    public function actionGetdealbrief($dealId)
    {
        $url = sprintf("%s/deal_jiben/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $dealId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $_data = curl_exec($ch);
        $_data = Json::decode($_data, true);
        curl_close($ch);
        if ($_data['result'] == 0 && $_data['errors']['code'] == 0) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $_data['data'];
        }
        return [];
    }

    public function actionProducts()
    {
        $this->layout = 'wcg';
        $url = sprintf("%s/deal_list/attribute-data-value-wcg", Yii::$app->params['api']['wcg']['baseUrl']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = Json::decode($result, true);
        $list = [];
        if ($result['result'] == 0 && $result['errors']['code'] == 0)
        {
            $list = $result['data'];
            $topDeals = [];
            if ($list && !is_array($list)) $list = (array)$list;
            foreach($list as $k => $deal)
            {
                $interval = null;
                if ($deal['deal_status'] == 1) $interval = $this->_getDateTimeDiff($deal['start_date']);
                $deal['interval'] = $interval;
                $list[$k] = $deal;
                if ($interval) $topDeals[$k] = $interval;
            }
            $dealList = [];
            foreach($list as $deal)
            {
                if ($deal['deal_status'] == 2) $dealList[] = $deal;
            }
            if ($topDeals && asort($topDeals))
                foreach($topDeals as $k => $v) $dealList[] = $list[$k];
            foreach($list as $k => $deal)
            {
                if (!array_key_exists($k, $topDeals) && $deal['deal_status'] != 2)
                {
                    $dealList[] = $deal;
                }
            }
            $list = $dealList;
        }
        return $this->render('products', ['list'=>$list]);
    }

    public function actionProduct($id)
    {
        if (Yii::$app->user->isGuest) return $this->redirect('/site/bind');
        $wcgUser = \frontend\models\wcg\User::fetch();
        $model = new TenderForm();
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            return $this->redirect($model->tender());
        }
        $loanTypes = [1 => '等额本息', 2=>'付息还本', 3=>'到期本息'];
        $this->layout = 'wcg';
        //获取标的详情
        $url = sprintf("%s/deal_show/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $id);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        $_data = $result['result'] == 0 && $result['errors']['code'] == 0 ? $result['data'] : null;
        $deal = $_data['deal'];
        $timeStamp = $deal['deal_status'] < 3 || $deal['deal_status'] == 4 ? $deal['start_date'] : $deal['full_time'];
        $plan = self::loanTermCalc(date('Y-m-d', $timeStamp), null, $deal['deal_end_date']);
        if ($deal['expires_type'] == 1) $period = $plan['days'][1]['period']['days'].'天';
        elseif($deal['expires_type'] == 2) $period = $plan['days'][1]['period']['m'] + ($plan['days'][1]['period']['d'] ? 1 : 0) .'个月';
        $deal['period'] = $period;
        $deal['loan_type'] = $loanTypes[$deal['refund_method']];
        $interval = null;
        if ($deal['deal_status'] == 1) $interval = $this->_getDateTimeDiff($deal['start_date']);
        if ($deal['deal_status'] == 2) $interval = $this->_getDateTimeDiff($deal['end_date']);
        $deal['interval'] = $interval;
        $deal['security'] = explode("<br />", $deal['fxkz']);
        foreach($deal['security'] as $k => $v)
        {
            unset($deal['security'][$k]);
            $v = trim($v);
            if ($v) $deal['security'][$k] = $v;
        }
        return $this->render('product_details', ['deal'=>$deal, 'refunds'=>$_data['refund_record'], 'dealOrders'=>$_data['deal_order'], 'model'=>$model, 'user'=>$wcgUser]);
    }

    public function actionCnpnr()
    {
        if (Yii::$app->user->isGuest) return $this->redirect('/site/bind');
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

    public function actionMyproducts()
    {
        if (Yii::$app->user->isGuest) return $this->redirect('/site/bind');
        $this->layout = 'wcg';
        if ($wcgUser = WCGUser::fetch())
        {
            $list = [];
            $url = sprintf("%s/user_deal/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $wcgUser->getAttribute('wcg_uid'));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = Json::decode($result, true);
            $lastList = [];
            $investSummary = [];
            if ($result['result'] == 0 && $result['errors']['code'] == 0)
            {
                $data = $result['data'];
                if ($data && is_array($data))
                {
                    $deal = [];
                    foreach($data as $repaymentOrder)
                    {
if ($repaymentOrder['status'] != 3) 
{
                        if (!isset($deal[$repaymentOrder['deal_id']]))
                        {
                            $url = sprintf("%s/deal_show/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $repaymentOrder['deal_id']);
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $dealData = curl_exec($ch);
                            curl_close($ch);
                            try
                            {
                                $dealData = Json::decode($dealData, true);
                            }
                            catch(\Exception $e) {
                                $dealData = ['result'=>0, 'errors'=>['code'=>0, 'message'=>'系统错误！'], 'data'=>['deal'=>['title'=>'', 'full_time'=>time()]]];
                            }
                            if ($dealData['result'] == 0 && $dealData['errors']['code'] == 0)
                            {
                                $dealData = $dealData['data']['deal'];
                                $plan = self::loanTermCalc(date('Y-m-d', $dealData['full_time']), null, $dealData['deal_end_date']);
                                if ($dealData['expires_type'] == 1) $period = $plan['days'][1]['period']['days'].'天';
                                elseif($dealData['expires_type'] == 2) $period = $plan['days'][1]['period']['m'] + $plan['days'][1]['period']['d'] ? 1 : 0 .'个月';
                            }
                        }
                        //已赚利息
                        $returnedInterestAmt = isset($returnedInterestAmt) ? ($returnedInterestAmt + ($repaymentOrder['status'] == 2 ? $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'] :0.00)) : ($repaymentOrder['status'] == 2 ? $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'] :0.00);
                        //待收利息
                        $interestAmt = isset($interestAmt) ? ($interestAmt + ($repaymentOrder['status'] == 1 ? $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'] : 0.00)) : ($repaymentOrder['status'] == 1 ? $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'] :0.00);
                        //投资金额
                        $investAmt = isset($investAmt) ? $investAmt + $repaymentOrder['benjin'] : $repaymentOrder['benjin'];

                        $list[$repaymentOrder['deal_id']]['info'] = ['title'=>$dealData['title'], 'rate'=>$dealData['syl'], 'period'=>$period];
                        //投资金额
                        $list[$repaymentOrder['deal_id']]['invest_amt'] = isset($list[$repaymentOrder['deal_id']]['invest_amt']) ? $list[$repaymentOrder['deal_id']]['invest_amt'] + $repaymentOrder['benjin'] : $repaymentOrder['benjin'];
                        $list[$repaymentOrder['deal_id']]['interest_amt'] = isset($list[$repaymentOrder['deal_id']]['interest_amt']) ? $list[$repaymentOrder['deal_id']]['interest_amt'] + $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'] : $repaymentOrder['lixi'] + $repaymentOrder['weiyuejin'] + $repaymentOrder['overdue'];
                        $list[$repaymentOrder['deal_id']]['deal_time'] = isset($list[$repaymentOrder['deal_id']]['deal_time']) ? max($list[$repaymentOrder['deal_id']]['deal_time'], $repaymentOrder['deal_time']) : $repaymentOrder['deal_time'];
}
                    }
                    $investSummary = [
                        'investAmt'=> isset($investAmt) ? $investAmt : 0.00,
                        'returnedInterestAmt'=> isset($returnedInterestAmt) ? $returnedInterestAmt : 0.00,
                        'interestAmt'=> isset($interestAmt) ? $interestAmt : 0.00,
                    ];
                    $lastItem = null;
                    foreach($list as $item)
                    {
                        $lastList[$item['deal_time']][] = $item;
                    }
                    krsort($lastList);

                }
            }

        }
        return $this->render('myproducts', ['list'=>$lastList, 'summary'=>$investSummary]);
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
    /**
     * @param string $tenderCompletedDate The deal load full timestamp in string, sucn as 2014-08-08.
     * @param Char $periodType Whether 'D' or 'M', Day or Month
     * @param Integer $period Day number, or Month number
     * @param Integer $dueDate The deal's due date, this is timestamp in integer
     * @param Boolean or Integer (0|1) $amortized 该借款是否属于分期偿付
     */
    public static function loanTermCalc($tenderCompletedDate=null, $period=null, $dueDate=null, $periodType=self::DEAL_PERIOD_TYPE_DAY, $amortized = true)
    {
        $ret = null;
        $tz = new \DateTimeZone('Asia/Shanghai');
        try {
            if (!$period && !$dueDate) throw new \Exception('Error: The deal\'s period and due date is null.');
            $periodType = $periodType ? strtoupper($periodType) : null;
            if (!$periodType) throw new \Exception('Error: The period type not defined.');
            if ($periodType != self::DEAL_PERIOD_TYPE_DAY && $periodType != self::DEAL_PERIOD_TYPE_MONTH) throw new \Exception('Error: The period type must be \'d\' for day, or \'m\' for month.');
            if ($dueDate)
            {
                $dt = new \DateTime();
                $dt->setTimestamp($dueDate);
                $dt->setTimezone($tz);
                $dueDate=$dt;
            }
            if ($tenderCompletedDate)
            {
                $dt = new \DateTime($tenderCompletedDate, $tz);
                $tenderCompletedDate = $dt;
            }
            if ($tenderCompletedDate)
            {
                if (!$dueDate)
                {
                    $dueDate = new \DateTime();
                    $dueDate->setTimestamp($tenderCompletedDate->format('U'));
                    $dueDate->setTimezone($tz);
                    $dueDate->add(new \DateInterval(sprintf("P%s%s", $period, $periodType)));
                }
                $period = $tenderCompletedDate->diff($dueDate);
                if (!$period->invert)
                {
                    if ($amortized)
                    {
                        $monthNumber = 0;
                        if ($period->y) $monthNumber += $period->y * 12;
                        $monthNumber += $period->m;
                        if ($monthNumber)
                        {
                            $formatStr = $tenderCompletedDate->format('Ymd') == $tenderCompletedDate->format('Ymt') ? 'Y-m-t' : 'Y-m-d';
                            for($i=1;$i<=$monthNumber;$i++)
                            {
                                if ($i == 1) $lastDT = $tenderCompletedDate;
                                $lastU = $lastDT->format('U');
                                $lastDT->add(new \DateInterval('P1M'));
                                $nextU = $lastDT->format('U');
                                $last = new \DateTime();
                                $last->setTimestamp($lastU);
                                $last->setTimezone($tz);
                                $next = new \DateTime();
                                $next->setTimestamp($nextU);
                                $next->setTimezone($tz);
                                $dt1 = new \DateTime($last->format($formatStr), $tz);
                                $dt2 = new \DateTime($next->format($formatStr), $tz);
                                if ($dt2->format('Ym') == $dueDate->format('Ym') && $tenderCompletedDate->format('Ymd') == $tenderCompletedDate->format('Ymt') ) $dt2 = $dueDate;
                                $ret['days'][$i] = ['date'=>$dt2->format('Y-m-d'), 'length'=>$dt1->diff($dt2)->days, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                            }
                        }
                        if ($period->d)
                        {
                            if (isset($ret['days']) && $ret['days'])
                            {
                                $ret['days'][count($ret['days'])+1] = ['date'=>$dueDate->format('Y-m-d'), 'length'=>$period->d, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                            }
                        }
                    }
                    else
                    {
                        $ret['days'][1] = ['date'=>$dueDate->format('Y-m-d'), 'length'=>$period->days, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                    }
                }
            }
            else
            {
                if (!$dueDate)
                {
                    $dueDate = new \DateTime();
                    $dueDate->setTimezone($tz);
                    $dueDate->add(new \DateInterval(sprintf("P%s%s", $period, $periodType)));
                }
                $now = new \DateTime();
                $now->setTimezone($tz);
                $period = $now->diff($dueDate);
                $ret = ['period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
            }
            if ($ret && isset($ret['days']) && $ret['days']) $ret['count'] = count($ret['days']);
            return $ret;
        }
        catch(\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function _getDateTimeDiff($timePoint)
    {
        $timeZone = new \DateTimeZone(Yii::$app->timeZone);
        $now = new \DateTime('now', $timeZone);
        $timePoint = new \DateTime(date('Y-m-d H:i:s', $timePoint), $timeZone);
        return $now->diff($timePoint);
    }
}
