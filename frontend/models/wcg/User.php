<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/29/2014
 * Time: 2:06 PM
 */

namespace frontend\models\wcg;


use yii\db\ActiveRecord;
use yii\helpers\Json;
use frontend\models\wcg\User as WCGUser;

class User extends ActiveRecord {

    public $userinfo;
    public static function tableName()
    {
        return '{{%wcg_user}}';
    }

    //注册到旺财谷网站
    public static function create($attributes)
    {
        $registerData = null;
        $registerData['username'] = $attributes['username'];
        $registerData['email'] = $attributes['email'];
        $registerData['phone'] = $attributes['mobile'];
        $registerData['password'] = md5($attributes['password']);
        $registerData['reg_ip'] = \Yii::$app->request->getUserIP();
        $registerData = base64_encode(json_encode($registerData));
        $url = sprintf("%s/reg_save/attribute-data-value-%s", \Yii::$app->params['api']['wcg']['baseUrl'], $registerData);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($result['result'] == 0 && $result['errors']['code'] == 0)
        {
            return static::bind(['id'=>$attributes['id'], 'wcg_uid'=>$result['data']]);
        }
        \Yii::error('Error: Register to WCG web has been failed.');
        return false;
    }

    //将旺财谷用户和本地用户进行绑定
    public static function bind($attributes)
    {
        $bindUser = new static();
        $bindUser->setAttribute('user_id', $attributes['id']);
        $bindUser->setAttribute('wcg_uid', $attributes['wcg_uid']);
        if ($bindUser->save())
        {
            return $bindUser;
        }
        \Yii::error('Error: User bind failed that WCG_User with local user.');
        return null;
    }

    //更新来自旺财谷网站的数据
    public static function fetch()
    {
        $wcgUser = null;
        if (!\Yii::$app->getUser()->isGuest)
        {
            $wcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>\Yii::$app->getUser()->getId()])->one();
            if ($wcgUser)
            {
                $apiPath = sprintf("%s/user_info/attribute-id-value-%s", \Yii::$app->params['api']['wcg']['baseUrl'], $wcgUser->getAttribute('wcg_uid'));
                $ch = curl_init($apiPath);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $result = Json::decode($result, true);
                curl_close($ch);
                $data = null;
                if ($result['result'] == 0)
                {
                    if ($result['errors']['code'] == 0)
                    {
                        $data = $result['data'];
                    }
                }
                if ($data)
                {
                    $wcgUser->setAttribute('cnpnr_account', $data['UsrCustId'] ? $data['UsrCustId'] : '');
                    $wcgUser->setAttribute('balance', $data['AcctBal'] ? $data['AcctBal'] : 0.00);
                    $wcgUser->setAttribute('avl_balance', $data['AvlBal'] ? $data['AvlBal'] : 0.00);
                    $wcgUser->setAttribute('freeze_balance', $data['FrzBal'] ? $data['FrzBal'] : 0.00);
                    $wcgUser->setAttribute('slb_balance', $data['SLBBal'] ?  $data['SLBBal'] : 0.00);
                    $wcgUser->setAttribute('invest_balance', $data['bid_sum'] ? $data['bid_sum'] : 0.00);
                    $wcgUser->setAttribute('interest_balance', $data['lixi'] ? $data['lixi'] : 0.00);
                    $wcgUser->setAttribute('returned_interest_balance', $data['yizhuan_lixi'] ? $data['yizhuan_lixi'] : 0.00);
                    $wcgUser->save();
                    $wcgUser->userinfo = $data;
                }
            }
        }

        return $wcgUser;
    }

    public function hasCnpnrAccount()
    {
        return $this->getAttribute('cnpnr_account') ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}