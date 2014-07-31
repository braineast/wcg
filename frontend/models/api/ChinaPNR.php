<?php
/**
 * Created by IntelliJ IDEA.
 * Author: al
 * Date: 6/24/2014
 * Time: 2:55 AM
 */

namespace frontend\models\api;

use Yii;


class ChinaPNR {
    const VERSION10 = 10;
    const VERSION20 = 20;

    const CMD_DEPOSIT = 'NetSave';
    const CMD_UNFREEZE = 'UsrUnFreeze';
    const CMD_OPEN = 'UserRegister';

    const PARAM_VERSION = 'Version';
    const PARAM_CMDID = 'CmdId';
    const PARAM_MERCUSTID = 'MerCustId';
    const PARAM_USRCUSTID = 'UsrCustId';
    const PARAM_ORDID = 'OrdId';
    const PARAM_ORDDATE = 'OrdDate';
    const PARAM_GATEBUSIID = 'GateBusiId';
    const PARAM_OPENBANKID = 'OpenBankId';
    const PARAM_DCFLAG = 'DcFlag';
    const PARAM_TRANSAMT = 'TransAmt';
    const PARAM_RETURL = 'RetUrl';
    const PARAM_BGRETURL = 'BgRetUrl';
    const PARAM_MERPRIV = 'MerPriv';
    const PARAM_USRID = 'UsrId';
    const PARAM_USRNAME = 'UsrName';
    const PARAM_IDTYPE = 'IdType';
    const PARAM_IDNO = 'IdNo';
    const PARAM_USRMP = 'UsrMp';
    const PARAM_USREMAIL = 'UsrEmail';
    const PARAM_CHARSET = 'CharSet';
    const PARAM_CHKVALUE = 'ChkValue';
    const PARAM_PRIVATE_SHOWID = 'showId';

    const RESP_CODE = 'RespCode';
    const RESP_DESC = 'RespDesc';
    const RESP_TRXID = 'TrxId';

    protected $host;
    private $merId;
    protected $params;
    protected $response;
    private $link;
    private $queryString;
    private $maps;
    private $signOrder;
    private $vSignOrder;
    private $retUrl;
    private $bgRetUrl;
    private $showId;
    private $apiInfo;

    public function __construct($hostInfo)
    {
        $this->apiInfo = \Yii::$app->params['api']['cnpnr']['dev'];
        $this->retUrl = $hostInfo . '/cnpnr';
        $this->bgRetUrl = $hostInfo . '/cnpnr/backend';
        $this->host = $this->apiInfo['host'];
        $this->merId = $this->apiInfo['merid'];
        $this->params = [
            self::PARAM_VERSION => self::VERSION10,
            self::PARAM_MERCUSTID => $this->apiInfo['mercustid'],
        ];
        $this->link = null;
        $this->queryString = null;
        $this->maps = [
            self::PARAM_VERSION,self::PARAM_CMDID,self::PARAM_MERCUSTID,
            self::PARAM_USRCUSTID,self::PARAM_ORDID,self::PARAM_ORDDATE,
            self::PARAM_GATEBUSIID,self::PARAM_OPENBANKID,self::PARAM_DCFLAG,
            self::PARAM_TRANSAMT,self::PARAM_RETURL,self::PARAM_BGRETURL,
            self::PARAM_MERPRIV,self::PARAM_CHKVALUE,self::RESP_TRXID,
            self::PARAM_USRID, self::PARAM_USRNAME, self::PARAM_IDTYPE, self::PARAM_IDNO,
            self::PARAM_USRMP, self::PARAM_USREMAIL, self::PARAM_CHARSET,
        ];
        $this->signOrder = [
            self::CMD_OPEN => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_BGRETURL, 4=>self::PARAM_RETURL, 5=>self::PARAM_USRID,
                6=>self::PARAM_USRNAME, 7=>self::PARAM_IDTYPE, 8=>self::PARAM_IDNO,
                9=>self::PARAM_USRMP, 10=>self::PARAM_USREMAIL, 11=>self::PARAM_MERPRIV,
            ],
            self::CMD_DEPOSIT => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,4=>self::PARAM_ORDID,5=>self::PARAM_ORDDATE,
                6=>self::PARAM_GATEBUSIID,7=>self::PARAM_OPENBANKID,8=>self::PARAM_DCFLAG,
                9=>self::PARAM_TRANSAMT,10=>self::PARAM_RETURL,11=>self::PARAM_BGRETURL,
                12=>self::PARAM_MERPRIV
            ],
            self::CMD_UNFREEZE => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID,4=>self::PARAM_ORDDATE,5=>self::RESP_TRXID,
                6=>self::PARAM_RETURL,7=>self::PARAM_BGRETURL,8=>self::PARAM_MERPRIV
            ],
        ];
        $this->vSignOrder = [
            self::CMD_OPEN => [
                0=>self::PARAM_CMDID,1=>self::RESP_CODE,2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRID, 4=>self::PARAM_USRCUSTID, 5=>self::PARAM_BGRETURL,
                6=>self::RESP_TRXID, 7=>self::PARAM_RETURL, 8=>self::PARAM_MERPRIV
            ],
            self::CMD_DEPOSIT => [
                0=>self::PARAM_CMDID,1=>self::RESP_CODE,2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,4=>self::PARAM_ORDID,5=>self::PARAM_ORDDATE,
                6=>self::PARAM_TRANSAMT,7=>self::RESP_TRXID,8=>self::PARAM_RETURL,
                9=>self::PARAM_BGRETURL,10=>self::PARAM_MERPRIV
            ],
            self::CMD_UNFREEZE => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_ORDDATE, 5=>self::RESP_TRXID,
                6=>self::PARAM_RETURL, 7=>self::PARAM_BGRETURL, 8=>self::PARAM_MERPRIV
            ],
        ];
        $this->response = null;
        $this->showId = self::PARAM_ORDID;
    }

    public function deposit($cnpnr_account_id)
    {
        $this->params[self::PARAM_CMDID] = self::CMD_DEPOSIT;
        $this->params[self::PARAM_USRCUSTID] = $cnpnr_account_id;
        $this->params[self::PARAM_DCFLAG] = 'D';
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->showId = self::RESP_TRXID;
        return $this;
    }

    public function open()
    {
        $this->params[self::PARAM_CMDID] = self::CMD_OPEN;
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->showId = self::RESP_TRXID;
        return $this;
    }


    public function setResponse(array $responseArr, $isBackend = false)
    {
        $cmdId = isset($responseArr[self::PARAM_CMDID]) && $responseArr[self::PARAM_CMDID] ? $responseArr[self::PARAM_CMDID] : null;
        $chkValue = isset($responseArr[self::PARAM_CHKVALUE]) && $responseArr[self::PARAM_CHKVALUE] ? $responseArr[self::PARAM_CHKVALUE] : null;
        if ($cmdId && $chkValue)
        {
            $vSignFieldsOrd = isset($this->vSignOrder[$cmdId]) ? $this->vSignOrder[$cmdId] : null;
            if ($vSignFieldsOrd)
            {
                //Check Sign
                $vSignMessage = '';
                for($i=0;$i<count($vSignFieldsOrd);$i++)
                {
                    $field = $vSignFieldsOrd[$i];
                    $value = isset($responseArr[$field]) && $responseArr[$field] ? trim($responseArr[$field]) : null;
                    if ($value) $vSignMessage .= $value;
                }
                if ($this->_vSign($vSignMessage, $chkValue))
                {
                    foreach($responseArr as $k => $v)
                    {
                        if ($k == self::PARAM_MERPRIV)
                        {
                            $v = json_decode(base64_decode($v), true);
                            if ($isBackend) $v['return'] = 'backend';
                        }
                        $this->response[$k] = $v;
                    }
                    Log::cnpnr($this->response);
                }
                else exit('验签失败');
            }
        }
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getLink()
    {
        if (!$this->link)
        {
            $this->sign();
            $this->link = $this->host.($this->queryString ? $this->queryString : '');
        }
        return $this->link;
    }

    public function __get($name)
    {
        $value = null;
        $params = [];
        foreach($this->params as $k=>$v)
            $params[strtolower($k)] = $v;
        if (isset($params[strtolower($name)]) && $params[strtolower($name)]) $value = $params[strtolower($name)];
        else $value = $this->$name;
        return $value;
    }

    public function __set($name, $value)
    {
        $name = strtolower($name);
        foreach($this->maps as $field)
        {
            if ($name == strtolower($field))
            {
                if ($name == strtolower(self::PARAM_TRANSAMT)) $value = number_format($value, 2, '.', '');
                $this->params[$field] = $value;
            }
        }
        return $this;
    }

    private function sign()
    {
        if (isset($this->params[self::PARAM_CMDID]))
        {
            $signMessage = null;
            $chkValue = null;
            $signMessage = $this->_getSignMessageStr($this->signOrder[$this->params[self::PARAM_CMDID]]);
            if ($signMessage) $chkValue = $this->_sign($signMessage);
            if ($chkValue)
            {
                $this->queryString .= '&'.self::PARAM_CHKVALUE.'='.$chkValue;
                $this->params[self::PARAM_CHKVALUE] = $chkValue;
            }
            return $this;
        }
        return false;
    }

    private function _sign($msg)
    {
        $sign = null;
        $fp = fsockopen($this->apiInfo['sign']['host'], $this->apiInfo['sign']['port'], $errno, $errstr, 10);
        if ($fp)
        {
            $len = sprintf("%04s", strlen($msg));
            $out = 'S'.$this->merId.$len.$msg;
            $out = sprintf("%04s", strlen($out)).$out;
            fputs($fp, $out);
            while(!feof($fp)) $sign .= fgets($fp, 128);
            fclose($fp);
            $sign = substr($sign, -256);
        }
        return $sign;
    }

    private function _vSign($messageBody, $chkValue)
    {
        $result = false;
        $len = sprintf("%04s", strlen($messageBody));
        $out = 'V'.$this->merId.$len.$messageBody.$chkValue;
        $out = sprintf("%04s", strlen($out)).$out;
        $fp = fsockopen($this->apiInfo['sign']['host'], $this->apiInfo['sign']['port'], $errno, $errstr, 10);
        if ($fp)
        {
            fputs($fp, $out);
            $in = '';
            while(!feof($fp)) $in .= fgets($fp, 128);
            fclose($fp);
            $result = substr($in, -4) == '0000';
        }
        return $result;
    }

    private function _getSignMessageStr(array $signParamOrd)
    {
        $message = '';
        for($i=0; $i<count($signParamOrd);$i++)
        {
            $name = $signParamOrd[$i];
            $val = isset($this->params[$name]) ? trim($this->params[$name]) : null;
            if ($val)
            {
                if ($name == self::PARAM_MERPRIV)
                {
                    $val = json_decode($val, true);
                    $val[self::PARAM_PRIVATE_SHOWID] = $this->showId;
                    $val = base64_encode(json_encode($val));
                }
                $message .= $val;
                if ($name == self::PARAM_RETURL || $name == self::PARAM_BGRETURL) $val = urlencode($val);
                $this->queryString .= $this->queryString ? '&'.$name.'='.$val : '?'.$name.'='.$val;
            }
        }
        return $message;
    }
}