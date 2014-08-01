<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/29/2014
 * Time: 4:42 PM
 */

namespace frontend\controllers;

use frontend\models\Controller;
use frontend\models\api\ChinaPNR;
use frontend\models\wcg\User as WCGUser;

class CnpnrController extends Controller
{
    public $enableCsrfValidation = false;
    private $response;

    public function actionIndex($backend = false)
    {
        header('Content-Type: text/html; charset=UTF-8');
        $backend = $backend ? true : $backend;
        if (isset($_POST) && $_POST)
        {
            $cnpnr = new ChinaPNR(\Yii::$app->request->hostInfo);
            $cnpnr->setResponse($_POST, $backend);
            if ($response = $cnpnr->getResponse())
            {
                $this->response = $response;
                $result = $this->_responser();
                if ($backend) exit('RECV_ORD_ID_'.$response[$response[ChinaPNR::PARAM_MERPRIV]['showId']]);
            }
        }
    }

    public function actionBackend()
    {
        return $this->actionIndex(true);
    }

    protected function UserRegister()
    {
        var_dump(\Yii::$app->request->post());
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
            return $this->postWCG();
            //推送到旺财谷网站 - 开户接口
            $userId = $this->response[ChinaPNR::PARAM_MERPRIV]['id'];
            $wcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>$userId])->one();
            if ($wcgUser)
            {
            }
        }
        return false;
    }

    protected function postWCG()
    {
        $post = [];
        foreach($_POST as $field=>$value)
        {
            $post[] = $field.'='.$value;
        }
        $post = implode('&', $post);

        //后台地址：：$url
        $url = null;
        switch($this->response[ChinaPNR::PARAM_CMDID])
        {
            case ChinaPNR::CMD_DEPOSIT:
                $url = 'http://888.yidaifa.com/HuifuPay/ChargeReturnBack.html';
                break;
            case ChinaPNR::CMD_OPEN:
                $url = 'http://888.yidaifa.com/HuifuPay/OpenReturnBack.html';
                break;
        }
        if ($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec($ch);
            curl_close($ch);
            echo(sprintf("%s, %s", $result, 'RECV_ORD_ID_'.$this->response[$this->response[ChinaPNR::PARAM_MERPRIV]['showId']]));
            if ($result == 'RECV_ORD_ID_'.$this->response[$this->response[ChinaPNR::PARAM_MERPRIV]['showId']])
            {
                WCGUser::fetch();
            }
        }
    }

    protected function NetSave()
    {
        return $this->postWCG();
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
        }
        return false;
    }

    private function _responser()
    {
        $method = $this->response[ChinaPNR::PARAM_CMDID];
        if (method_exists($this, $method))
            return $this->$method();
        elseif (method_exists($this, strtolower($method)))
        {
            $method = strtolower($method);
            return $this->$method();
        }
        return $this;
    }

    private function _getUser()
    {
        var_dump(\Yii::$app->getUser());
    }

}