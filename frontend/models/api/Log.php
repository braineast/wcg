<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 6/27/2014
 * Time: 5:38 PM
 */

namespace frontend\models\api;


use yii\db\ActiveRecord;

class Log extends ActiveRecord{

    const TYPE_CNPNR = 1;

    public static function tableName()
    {
        return 'api_log';
    }

    public static function cnpnr(Array $response)
    {
        if ($response && is_array($response))
        {
            $type = -1;
            $merPriv = isset($response[ChinaPNR::PARAM_MERPRIV]) ? $response[ChinaPNR::PARAM_MERPRIV] : null;
            $command = isset($response[ChinaPNR::PARAM_CMDID]) && $response[ChinaPNR::PARAM_CMDID] ? $response[ChinaPNR::PARAM_CMDID] : null;
            $identity = null;
            $identityValue = null;
            if ($merPriv && is_array($merPriv))
            {
                $identity = isset($merPriv[ChinaPNR::PARAM_PRIVATE_SHOWID]) && $merPriv[ChinaPNR::PARAM_PRIVATE_SHOWID] ? $merPriv[ChinaPNR::PARAM_PRIVATE_SHOWID] : $identity;
                $identityValue = isset($response[$identity]) && $response[$identity] ? $response[$identity] : $identityValue;
            }
            $log = new Log();
            $log->setAttribute('source_id', self::TYPE_CNPNR);
            $keyCode = $merPriv ? implode('|',$merPriv) : null;
            if ($keyCode) $log->setAttribute('key_code', $keyCode);
            if ($command) $log->setAttribute('command', $command);
            if ($identity) $log->setAttribute('identity', $identity);
            if ($identityValue) $log->setAttribute('identity_value', $identityValue);
            if ($response[ChinaPNR::RESP_CODE] == '000') $type = 0;
            $log->setAttribute('type', $type);
            $log->setAttribute('status_code', $response[ChinaPNR::RESP_CODE]);
            $log->setAttribute('message', $response[ChinaPNR::RESP_DESC]);
            $log->setAttribute('data', json_encode($response));
            $log->save();
        }
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