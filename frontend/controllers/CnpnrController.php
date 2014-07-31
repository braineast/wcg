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
        $url = 'http://888.yidaifa.com/HuifuPay/OpenReturnBack.html';
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

    protected function NetSave()
    {
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
            $orderId = $this->response[ChinaPNR::PARAM_ORDID];
            $paymentOrder = OrderPayment::loadBySerial($orderId); //获取支付单
            if ($paymentOrder && $paymentOrder->status != OrderPayment::STATUS_PAID)
            {
                $paymentOrder->status = OrderPayment::STATUS_PAID;
                $paymentOrder->save();
                if ($paymentOrder->status == OrderPayment::STATUS_PAID)
                {
                    $order = Order::loadById($paymentOrder->orderId); //获取支付单对应的订单，并进行处理
                    if ($order && $order->status == Order::STATUS_UNPAID)
                    {
                        $order->paid_amount += $this->response[ChinaPNR::PARAM_TRANSAMT];
                        if ($order->save())
                        {
                            $user = User::find()->where('id=:id', [':id'=>$this->response[ChinaPNR::PARAM_MERPRIV]['id']])->one();
                            $user->setAttribute('money', $user->getAttribute('money') + $this->response[ChinaPNR::PARAM_TRANSAMT]);
                            $user->save();
                        }
//                        if ($order->status == Order::STATUS_PAID) return true;
                    }
                }
            }
            $this->redirect('account');
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