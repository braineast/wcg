<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/1/2014
 * Time: 1:03 AM
 */

namespace frontend\models\account;


use yii\base\Model;

class DepositForm extends Model{
    public $amount;

    public function rules()
    {
        return [
            ['amount', 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'amount'=>\Yii::t('yii', 'Deposit Amount'),
        ];
    }
}