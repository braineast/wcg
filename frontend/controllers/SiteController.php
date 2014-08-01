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
        Yii::$app->user->setReturnUrl('/account/transactions');
        $this->layout = 'wcg';
        $model = new LoginForm();
//        if ($openid)
//        {
//            if ($wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
//            {
//                if (Yii::$app->getUser()->isGuest)
//                {
//                    if ($model->load(Yii::$app->request->post())) {
//                        $url = sprintf("%s/login/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode(['username'=>$model->username, 'password'=>md5($model->password), 'login_ip'=>Yii::$app->request->userIP])));
//                        $ch = curl_init($url);
//                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                        $result = curl_exec($ch);
//                        curl_close($ch);
//                        $result = Json::decode($result, true);
//                        if ($result['result'] == 0 && $result['errors']['code'] == 0)
//                        {
//                            $userData = $result['data'];
//                            if (WCGUser::find()->where('wcg_uid=:wcgUid', [':wcgUid'=>$userData['id']])->one()) $this->redirect('/site/notice?type=system&subject=系统提示&message=您要绑定的旺财谷账号已经被其他微信号绑定！请谨慎保管理财账户，谢谢！');
//                            $signup = new SignupForm();
//                            $signup->username = $userData['username'];
//                            $signup->email = $userData['email'];
//                            $signup->mobile = $userData['phone'];
//                            $signup->password = $model->password;
//                            $signup->repeatpassword = $model->password;
//                            $user = \frontend\models\User::create($signup->attributes);
//                            if ($user)
//                            {
//                                WCGUser::bind(['id'=>$user->id, 'wcg_uid'=>$userData['id']]);
//                                WechatUser::create(['user_id'=>$user->id, 'open_id'=>$openid]);
//                                $wcgUser = WCGUser::fetch($user->id);
//                                Yii::$app->getUser()->login($user, 3600 * 24 * 365);
//                                if ($wcgUser && !$wcgUser->hasCnpnrAccount()) return $this->redirect('site/cnpnr');
//                                return $this->redirect('/site/notice?type=open');
//                                return $this->goHome();
//                            }
//                        }
//                        else  return $this->render('wcg/login', ['model' => $model,'openid'=>$openid]);
//                        return $this->goBack();
//                    } else {
//                        return $this->render('wcg/login', [
//                            'model' => $model,'openid'=>$openid
//                        ]);
//                    }
//                }
//                else
//                    $this->redirect('/site/notice?type=system&subject=系统提示&message=该微信账号已经绑定旺财谷平台用户，请不要重复绑定，谢谢！');
//            }
//        }
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
                    if ($this->isWechat() && $openid && WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
                    {
                        if (!Yii::$app->getUser()->isGuest) $this->redirect('/site/notice?type=system&subject=系统提示&message=该微信账号已经绑定旺财谷平台用户，请不要重复绑定，谢谢！');
                    }
                    if ($openid && !WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
                        WechatUser::create(['user_id'=>$wcgUser->getAttribute('user_id'), 'open_id'=>$openid]);
                    //该用户在旺财谷登录成功，并已经绑定了微信账号，那么，本地登录
                    $user = User::find()->where('id=:id', [':id'=>$wcgUser->getAttribute('user_id')])->one();
                    if ($user) Yii::$app->user->login($user);
                    Yii::$app->user->login($user);
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
                    Yii::$app->getUser()->login($user, 3600 * 24 * 365);
                    if ($wcgUser && !$wcgUser->hasCnpnrAccount()) return $this->redirect('site/cnpnr');
                    return $this->redirect('/site/notice?type=open');
                }
            }
            else {
                $model->addError('password', Yii::t('yii', 'Incorrect username or password.'));
                return $this->render('wcg/login', ['model' => $model,'openid'=>$openid]);
            }
            return $this->goBack();
        }
        return $this->render('wcg/login', [
            'model' => $model,'openid'=>$openid
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
                    $subject = '恭喜！';
                    $message = sprintf("您的投标已完成，稍后可以查询到您的投标记录，以确认是否抢到！");
                    break;
                case 'default':
                    $subject = '操作已完成';
                    $message = '平台已经完成指定的操作，请稍后查询记录，以确保操作无误，有任何疑问请随时与客服联络！';
                    break;
            }
        }
        return $this->render('notice', ['subject'=>$subject,'message'=>$message]);
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
                if (Yii::$app->getUser()->login($user, 3600 * 24 * 365)) {
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
        }
        return $this->render('products', ['list'=>$list]);
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

    public function actionMyproducts()
    {
        $this->layout = 'wcg';
        if (Yii::$app->getUser()->isGuest) exit;
        if ($wcgUser = WCGUser::fetch())
        {
            $list = [];
//            $url = sprintf("%s/user_deal/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $wcgUser->getAttribute('wcg_uid'));
            $url = sprintf("%s/user_deal/attribute-data-value-77", Yii::$app->params['api']['wcg']['baseUrl']);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = Json::decode($result, true);
            if ($result['result'] == 0 && $result['errors']['code'] == 0)
            {
                $data = $result['data'];
                if ($data && is_array($data))
                {
                    $deal = [];
                    foreach($data as $repaymentOrder)
                    {
                        if (!isset($deal[$repaymentOrder['deal_id']]))
                        {
                            $url = sprintf("%s/deal_show/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $repaymentOrder['deal_id']);
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $dealData = curl_exec($ch);
                            curl_close($ch);
                            $dealData = Json::decode($dealData, true);
                            if ($dealData['result'] == 0 && $dealData['errors']['code'] == 0) $dealData = $dealData['data']['deal'];
                            $plan = self::loanTermCalc(date('Y-m-d', $dealData['full_time']), null, $dealData['deal_end_date']);
                            if ($dealData['expires_type'] == 1) $period = $plan['days'][1]['period']['days'].'天';
                            elseif($dealData['expires_type'] == 2) $period = $plan['days'][1]['period']['m'] + $plan['days'][0]['period']['d'] ? 1 : 0 .'个月';
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
                    $investSummary = [
                        'investAmt'=> isset($investAmt) ? $investAmt : 0.00,
                        'returnedInterestAmt'=> isset($returnedInterestAmt) ? $returnedInterestAmt : 0.00,
                        'interestAmt'=> isset($interestAmt) ? $interestAmt : 0.00,
                    ];
                    $lastList = [];
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
}
