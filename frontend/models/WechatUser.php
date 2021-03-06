<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/30/2014
 * Time: 7:34 AM
 */

namespace frontend\models;


use yii\db\ActiveRecord;
use frontend\models\wcg\User as WCGUser;
use frontend\models\User;

use Yii;

class WechatUser extends ActiveRecord{

    public static function tableName()
    {
        return 'wechat_user';
    }

    public static function login($openid)
    {
        if (Yii::$app->getUser()->isGuest)
        {
            if ($wechatUser = WechatUser::find()->where('open_id=:openId', [':openId'=>$openid])->one())
            {
                $user = \frontend\models\User::findIdentity($wechatUser->getAttribute('user_id'));
                if ($user)
                {
                    Yii::$app->getUser()->login($user);
                    WCGUser::fetch();
                }
            }
        }
        return true;
    }

    public static function create($attributes)
    {
        $wechatUser = new static();
        $wechatUser->setAttribute('open_id', $attributes['open_id']);
        $wechatUser->setAttribute('user_id', $attributes['user_id']);
        if ($wechatUser->save()) return $wechatUser;
        return false;
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