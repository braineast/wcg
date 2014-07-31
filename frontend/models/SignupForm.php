<?php
namespace frontend\models;

use yii\base\Model;
use Yii;
use frontend\models\User;
use frontend\models\wcg\User as WcgUser;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $id;
    public $username;
    public $email;
    public $mobile;
    public $password;
    public $repeatpassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\frontend\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'message'=>'The username length is error', 'min'=>6, 'max'=>16],
            ['username', 'fieldFormatValidator'],
            [['username', 'email', 'mobile'], 'fieldExistsWCG'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\frontend\models\User', 'message' => 'This email address has already been taken.'],

            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'unique', 'targetClass' => '\frontend\models\User', 'message' => 'This mobile number has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['repeatpassword', 'required'],
            ['repeatpassword', 'compare', 'compareAttribute'=>'password', 'message'=>'The repeat password don\'t match.']
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
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


    public function fieldFormatValidator($attribute, $params)
    {
        if ($attribute == 'username')
        {
            $pattern = '/^[a-zA-Z\d]+[a-zA-Z\d_]$/';
            if (!preg_match($pattern, $this->$attribute)) $this->addError($attribute, '用户名只能包含数字、字母、下划线，不能使用特殊字符。');
        }
    }

    public function fieldExistsWCG($attribute, $params)
    {
        $value = $this->$attribute;
        $url = sprintf("%s/reg/attribute-%s-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $attribute, $value);
        if ('mobile' == $attribute)
            $url = sprintf("%s/reg/attribute-phone-value-%s", Yii::$app->params['api']['wcg']['baseUrl'], $value);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($result['errors']['code'] < 0) $this->addError($attribute, sprintf("您输入的%s已被占用，请重新输入。", $this->getAttributeLabel($attribute)));
    }

    public function attributeLabels()
    {
        return [
            'username'=>Yii::t('yii', 'Username'),
            'email'=>Yii::t('yii', 'Email'),
            'mobile'=>Yii::t('yii', 'Mobile'),
            'password'=>Yii::t('yii', 'Password'),
        ];
    }
}
