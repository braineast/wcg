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
            ['amount', 'number', 'min'=>100.00],
            ['amount', 'checkAmount'],
        ];
    }

    public function checkAmount($attribute, $params)
    {
        $dealBrief = $this->getDealBrief();
        $userInfo = $this->getUserInfo();
        if (!$userInfo['cnpnr_account']) $this->addError($attribute, '您尚未开通汇付天下资金托管账户，无法进行投资，请先行开户。');
        if ($dealBrief['deal_status'] == 1) $this->addError($attribute, '该标的目前处于准备期，无法投资。');
        if ($dealBrief['deal_status'] == 3) $this->addError($attribute, '该标的已经满标，无法继续投资。');
        if ($dealBrief['deal_status'] == 4) $this->addError($attribute, '该标的已经流标，无法投资。');
        if ($dealBrief['deal_status'] == 5) $this->addError($attribute, '该标的已经处于还款中，无法投资。');
        if ($dealBrief['deal_status'] == 6) $this->addError($attribute, '该标的已经完成，无法投资。');
        if ($dealBrief['balance'] < $this->$attribute) $this->addError($attribute, '该标的可投金额已不足以满足您的投资，请修改投资金额。');
        if ($userInfo['avl_balance'] < $this->$attribute) $this->addError($attribute, '您的账户可用余额不足以进行本次投资，请修改投资金额。');
        if ($this->$attribute % 100 > 0) $this->addError($attribute, '请输入100或100的整数进行投资。');
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
            $data = [
                'deal_id'=>$this->dealId,
                'uid'=>$uid,
                'money'=>$this->amount,
                'freeze_order_id'=>$this->_createSerial(),
                'freeze_order_date'=>date('Ymd'),
            ];
            $url = sprintf("%s/deal_order/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode($data)));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $result = Json::decode($result, true);
            curl_close($ch);
            $order = null;
            if ($result['result'] == 0 && $result['errors']['code']==0)
            {
                $order = $result['data'];
                $cnpnr = new ChinaPNR(Yii::$app->request->hostInfo);
                $cnpnr->tender($cnpnrAcctId);
                $cnpnr->transAmt = $order['order_money'];
                $cnpnr->ordId = $order['deal_number'];
                $cnpnr->ordDate = $order['OrdDate'];
                $cnpnr->isFreeze = 'Y';
                $cnpnr->freezeordid = $order['deal_number'] . '000';
                $cnpnr->maxTenderRate = 0.09;
                $cnpnr->BorrowerDetails = Json::encode([['BorrowerCustId'=>'6000060000288503', 'BorrowerAmt'=>$cnpnr->transAmt, 'BorrowerRate'=>'0.99']]);
                $cnpnr->merPriv = json_encode(['id'=>$wcgUser->getAttribute('wcg_uid'),'username'=>$order['username'],'cnpnr_acct'=>$cnpnrAcctId]);
                return $cnpnr->getLink();
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

    public function getDealBrief()
    {
        $url = sprintf("%s/deal_jiben/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $this->dealId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = Json::decode($result, true);
        curl_close($ch);
        if ($result['result'] == 0 && $result['errors']['code'] == 0) $result = $result['data'];
        return $result;
    }

    private function _createSerial()
    {
        $orderNumber = false;
        if (preg_match('/.*\.+?(\d+)?\s*(\d+)$/', microtime(), $microTimeArr))
        {
            $orderNumber = date('YmdHis', $microTimeArr[2]).substr($microTimeArr[1], 0, 6);
        }
        return $orderNumber;
    }
}