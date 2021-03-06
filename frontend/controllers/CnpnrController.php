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
use frontend\models\TenderForm;
use frontend\models\wcg\User as WCGUser;
use Yii;
use yii\base\Exception;

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
            $cnpnr = new ChinaPNR();
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
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
            //推送到旺财谷网站 - 开户接口
            $result =  $this->postWCG();
            if (!Yii::$app->getUser()->isGuest)
            {
                WCGUser::fetch();
            }
            if ($result) $this->redirect('/site/notice?type=open');
        }
        return false;
    }

    protected function postWCG()
    {
        echo(sprintf("已经进入postWCG;<br/>"));
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
                $url = sprintf("%s/HuifuPay/ChargeReturnBack.html", \Yii::$app->params['api']['cnpnr']['noticeUrl']);
                break;
            case ChinaPNR::CMD_OPEN:
                $url = sprintf("%s/HuifuPay/OpenReturnBack.html", \Yii::$app->params['api']['cnpnr']['noticeUrl']);
                break;
            case ChinaPNR::CMD_TENDER:
                $url = sprintf("%s/HuifuPay/BidReturnBack.html", \Yii::$app->params['api']['cnpnr']['noticeUrl']);
                break;
        }
        if ($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec($ch);
            curl_close($ch);
            $status = $result == 'RECV_ORD_ID_'.$this->response[$this->response[ChinaPNR::PARAM_MERPRIV][ChinaPNR::PARAM_PRIVATE_SHOWID]];
            if (!$status)
            {
                $act = 'v'.$this->response[ChinaPNR::PARAM_CMDID];
                if (method_exists($this, $act))
                {
                    return $this->$act();
                }
            }
            if (!$status && $this->response[ChinaPNR::PARAM_CMDID] == ChinaPNR::CMD_TENDER)
            {
                $tenderModel = new TenderForm();
                if ($tenderModel->tenderIsCompleted($this->response[$this->response[ChinaPNR::PARAM_MERPRIV][ChinaPNR::PARAM_PRIVATE_SHOWID]])) $status = true;
            }
            return $status;
        }
        return null;
    }

    protected function vUserRegister($params = null)
    {
        try
        {
            $wcgUser = WCGUser::find()->where('user_id=:userId', [':userId'=>Yii::$app->user->id])->one();
            if (!$wcgUser) throw new Exception('The user not found.', 1001);
            $url = sprintf("%s/user_info/attribute-id-value-%s.html", Yii::$app->params['api']['wcg']['baseUrl'], $wcgUser->getAttribute('wcg_uid'));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            if (!$result) throw new Exception('The api server not accessed.', 1002);
            $result = json_decode($result, true);
            if ($result['result'] == 0) $result = $result['data'];

            return isset($result['UsrCustId']) && $result['UsrCustId'] ? true : false;
        }
        catch(Exception $e)
        {
            exit($e->getMessage());
        }
    }

    protected function InitiativeTender()
    {
//        if ($this->response[ChinaPNR::RESP_CODE] == '000')
//        {
           $result = $this->postWCG();
            $status = false;
            $tenderModel = new TenderForm();
            if ($tenderModel->tenderIsCompleted($this->response[$this->response[ChinaPNR::PARAM_MERPRIV][ChinaPNR::PARAM_PRIVATE_SHOWID]])) $status = true;
            if ($status)
                return $this->redirect('/site/notice?type=tender');
            else return $this->redirect('/site/notice?type=tender&subject=抱歉，投标失败！&message=非常抱歉，您本次投标没有成功，请再次进行尝试！');
//        }
//        return false;
    }

    protected function NetSave()
    {
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
            $result = $this->postWCG();
            if ($result) $this->redirect('/site/notice?type=deposit');
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