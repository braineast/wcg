<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 1:03 AM
 */

namespace frontend\models\account;


use frontend\models\api\ChinaPNR;
use yii\base\Model;
use frontend\models\wcg\User as WCGUser;

use Yii;
use yii\helpers\Json;

class DepositForm extends Model{
    public $amount;

    public function rules()
    {
        return [
            ['amount', 'required'],
            ['amount', 'number', 'min'=>0.01],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function deposit()
    {
        if ($this->validate()) {
            //请求订单数据
            //获取用户的ChinaPNR Account id
            if ($wcgUser = WCGUser::fetch())
            {
                $cnpnrAcctId = $wcgUser->getAttribute('cnpnr_account');
                $uid = $wcgUser->getAttribute('wcg_uid');
            }
            $url = sprintf("%s/charge/attribute-data-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], base64_encode(Json::encode(['uid'=>$uid, 'money'=>$this->amount])));
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
                $cnpnr->deposit($cnpnrAcctId);
                $cnpnr->transAmt = $order['fund'];
                $cnpnr->ordId = $order['number'];
                $cnpnr->ordDate = date('Ymd', $order['create_time']);
                $cnpnr->merPriv = json_encode(['id'=>$wcgUser->getAttribute('wcg_uid'),'username'=>$order['username'],'cnpnr_acct'=>$cnpnrAcctId]);
                return $cnpnr->getLink();
            }
        }

        return null;
    }

    public function attributeLabels()
    {
        return [
            'amount'=>\Yii::t('yii', 'Deposit Amount'),
        ];
    }
}