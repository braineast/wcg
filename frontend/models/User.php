<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/27/2014
 * Time: 10:17 PM
 */

namespace frontend\models;

use common\models\User as CommonUser;
use frontend\models\wcg\User as WCGUser;
use yii\base\Event;


/**
 * Class User
 * @package frontend\models
 * @property string $mobile
 */
class User extends CommonUser
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['mobile', 'filter', 'filter' => 'trim'];
        $rules[] = ['mobile', 'required'];
        $rules[] = ['mobile', 'unique'];
        return $rules;
    }
}
