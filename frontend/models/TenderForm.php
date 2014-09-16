<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/7/2014
 * Time: 12:44 AM
 */

namespace frontend\models;

use frontend\models\wcg\User as WCGUser;
use yii\base\Model;
use Yii;
use yii\helpers\Json;
use frontend\models\api\ChinaPNR;


class TenderForm extends Model
{
    public $dealId;
    public $amount;

    public function rules()
    {
        return [
            [['dealId','amount'], 'required', 'message'=>Yii::t('tender', 'The tender amount is required.')],
            ['amount', 'number', 'min'=>0.01],
            ['amount', 'checkAmount'],
        ];
    }

    public function checkAmount($attribute, $params)
    {
        $dealBrief = $this->getDealBrief();
        $userInfo = $this->getUserInfo();
        $dealOrders = $this->getDealOrders();
        if (isset($dealBrief['xinshou_status']) && $dealBrief['xinshou_status'] == 2)
        {
            if ($this->$attribute != 100.00) $this->addError($attribute, '新手标只允许投资100元。');
        }
        else
        {
            if ($this->$attribute < $dealBrief['start_money']) $this->addError($attribute, '请输入不少于'.$dealBrief['start_money'].'的投资金额。');
            else
            {
                if (($this->$attribute - $dealBrief['start_money']) % $dealBrief['dizeng_money'] > 0)
                    $this->addError($attribute, '投资金额，需要以'.$dealBrief['dizeng_money'].'递增。');
            }
        }
        $newbieBidCount = 0;
        if ($dealOrders)
        {
            foreach($dealOrders as $ord)
            {
                $dealInfo = $this->getDealBrief($ord['deal_id']);
                if (isset($dealInfo['xinshou_status']) && $dealInfo['xinshou_status'] == 2) $newbieBidCount++;
            }
        }
        if ($newbieBidCount >= 3) $this->addError($attribute, '抱歉，新手标每个用户只允许至多投资三次。');
        if (!$userInfo['cnpnr_account']) $this->addError($attribute, '您尚未开通汇付天下资金托管账户，无法进行投资，请先行开户。');
        if ($dealBrief['deal_status'] == 1) $this->addError($attribute, '该标的目前处于准备期，无法投资。');
        if ($dealBrief['deal_status'] == 3) $this->addError($attribute, '该标的已经满标，无法继续投资。');
        if ($dealBrief['deal_status'] == 4) $this->addError($attribute, '该标的已经流标，无法投资。');
        if ($dealBrief['deal_status'] == 5) $this->addError($attribute, '该标的已经处于还款中，无法投资。');
        if ($dealBrief['deal_status'] == 6) $this->addError($attribute, '该标的已经完成，无法投资。');
        if ($dealBrief['balance'] < $this->$attribute) $this->addError($attribute, '该标的可投金额已不足以满足您的投资，请修改投资金额。');
        if ($userInfo['avl_balance'] < $this->$attribute) $this->addError($attribute, '您的账户可用余额不足以进行本次投资，请修改投资金额。');
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function tender()
    {
        if ($this->validate()) {
            //请求订单数据
            //获取用户的ChinaPNR Account id
            if ($wcgUser = WCGUser::fetch())
            {
                $cnpnrAcctId = $wcgUser->getAttribute('cnpnr_account');
                $uid = $wcgUser->getAttribute('wcg_uid');
            }
            if ($order = $this->getOrder($uid))
            {
                //Get deal details data
                $url = sprintf("%s/deal_show/attribute-data-value-%s",Yii::$app->params['api']['wcg']['baseUrl'], $this->dealId);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $dealData = curl_exec($ch);
                curl_close($ch);
                $dealData = Json::decode($dealData, true);
                if ($dealData['result'] == 0 && $dealData['errors']['code'] == 0)
                {
                    $dealData = $dealData['data']['deal'];
                    $borrowerUid = $dealData['uid'];
                    //Get Borrower user info
                    $url = sprintf("%s/user_info/attribute-id-value-%s", \Yii::$app->params['api']['wcg']['baseUrl'], $borrowerUid);
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $userData = curl_exec($ch);
                    $userData = Json::decode($userData, true);
                    curl_close($ch);
                    if ($userData['result'] == 0 && $userData['errors']['code'] == 0)
                    {
                        $userData = $userData['data'];
                        $borrowerCustId = $userData['UsrCustId'];
                        $cnpnr = new ChinaPNR(Yii::$app->request->hostInfo);
                        $cnpnr->tender($cnpnrAcctId);
                        $cnpnr->transAmt = $order['order_money'];
                        $cnpnr->ordId = $order['deal_number'];
                        $cnpnr->ordDate = $order['OrdDate'];
                        $cnpnr->isFreeze = 'Y';
                        $cnpnr->freezeordid = $order['deal_number'] . '000';
                        $cnpnr->maxTenderRate = 0.09;
                        $cnpnr->BorrowerDetails = Json::encode([['BorrowerCustId'=>$borrowerCustId, 'BorrowerAmt'=>$cnpnr->transAmt, 'BorrowerRate'=>'0.99']]);
                        $cnpnr->merPriv = json_encode(['id'=>$wcgUser->getAttribute('wcg_uid'),'username'=>$order['username'],'cnpnr_acct'=>$cnpnrAcctId]);
                        return $cnpnr->getLink();
                    }
                }
            }
        }

        return null;
    }

    public function attributeLabels()
    {
        return [
            'dealId' => Yii::t('tender', 'Deal id'),
            'amount' => Yii::t('tender', 'Amount'),
        ];
    }

    public function getUserInfo()
    {
        $user = WCGUser::fetch();
        if ($user) return $user;
        return false;
    }

    public function getDealBrief($dealId = null)
    {
        $url = sprintf("%s/deal_show/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $dealId ? $dealId : $this->dealId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = Json::decode($result, true);
        curl_close($ch);
        if ($result['result'] == 0 && $result['errors']['code'] == 0) $result = $result['data']['deal'];
        return $result;
    }

    /**
     * Get order object from WCG web site
     */
    public function getOrder($userId)
    {
        $order = null;
        $data = ['deal_id'=>$this->dealId, 'uid'=>$userId, 'money'=>$this->amount,];
        $url = sprintf("%s/deal_order/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode($data)));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $result = curl_exec($ch);
        $result = Json::decode($result, true);
        curl_close($ch);
        if ($result['result'] == 0 && $result['errors']['code'] == 0) $order = $result['data'];
        if (!$order || $this->tenderIsCompleted($order['deal_number'])) return $this->getOrder($userId);
        return $order;
    }

    public function tenderIsCompleted($orderId)
    {
        $url = sprintf("%s/cheack_toubiao/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $orderId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = Json::decode($result, true);
        curl_close($ch);
        if ($result['result'] == 0 && $result['errors']['code'] == 0) return true;
        return false;
    }

    private function getDealOrders()
    {
        $wcgUser = $this->getUserInfo();
        $url = sprintf("%s/cheack_jiaoyi/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $wcgUser->getAttribute('wcg_uid'));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $logs = curl_exec($ch);
        $logs = Json::decode($logs, true);
        $logs = isset($logs['data']['toubiao']) && $logs['data']['toubiao'] ? $logs['data']['toubiao'] : null;
        curl_close($ch);
        $orders = [];
        foreach($logs as $dealOrder)
        {
            if ($dealOrder['status'] == 2) $orders[] = $dealOrder;
        }
        return $orders;
    }
}