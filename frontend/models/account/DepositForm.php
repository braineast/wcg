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
            $user = User::create($this->attributes);
            if ($user) {
                $data = $user->attributes;
                $data['password'] = $this->password;
                WcgUser::create($data);
            }
            return $user;
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