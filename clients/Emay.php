<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms\clients;

use SoapClient;
use xutl\sms\Sms;
use yii\base\Exception;
use yii\base\NotSupportedException;

/**
 * Class Emay
 * @package xutl\sms\clients
 */
class Emay extends Sms
{
    public $baseUrl = 'http://sdk4report.eucp.b2m.cn:8080/sdk/SDKService?wsdl';
    public $serialNumber;
    public $password;
    public $sessionKey;

    /**
     * @var string 短信签名
     */
    public $signName;

    /**
     * @var SoapClient
     */
    private $client;

    /**
     * 初始化
     * @throws Exception
     */
    public function init()
    {
        parent::init();
        if (!class_exists('SoapClient', false)) {
            throw new Exception ('suppert does not exist.' . ':SoapClient');
        }
        $this->client = new SoapClient ($this->baseUrl);
    }

    /**
     * 发送请求
     * @param string $url
     * @param string $method
     * @param array|string $params
     * @param array $headers
     * @return array
     * @throws Exception
     */
    public function api($method, $params = [])
    {
        return $this->client->__soapCall($method, ['parameters' => $params]);
    }

    /**
     * 指定一个 session key 并 进行登录操作
     *
     * @param string $sessionKey 指定一个session key
     * @return int 操作结果状态码
     */
    public function login($sessionKey = '')
    {
        if (!empty($sessionKey)) {
            $this->sessionKey = $sessionkey;
        }

        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->sessionKey, 'arg2' => $this->password);
        return $this->api('registEx', $params);

    }

    /**
     * 注销操作 (注:此方法必须为已登录状态下方可操作)
     *
     * @return int 操作结果状态码
     *
     *         之前保存的sessionKey将被作废
     *         如需要，可重新login
     */
    function logout()
    {
        $params = ['arg0' => $this->serialNumber, 'arg1' => $this->sessionKey];
        $result = $this->api('logout', $params);
        return $result;
    }


    /**
     * 修改密码
     *
     * @param string $NewPassword 新密码
     * @return int 操作结果状态码
     */
    public function editpassword($new_password)
    {
        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->sessionKey, 'arg2' => $this->password, 'arg3' => $new_password);
        return $this->api('serialPwdUpd', $params);
    }

    /**
     * 余额查询
     *
     * @return double 余额
     */
    public function get_balance()
    {
        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->sessionKey);
        return $this->api('getBalance', $params);
    }

    /**
     * 得到状态报告
     *
     * @return array 状态报告列表, 一次最多取5个
     */
    public function get_report()
    {
        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->sessionKey);
        return $this->api('getReport', $params);
    }

    /**
     * 短信充值
     *
     * @param string $cardId [充值卡卡号]
     * @param string $cardPass [密码]
     * @return int 操作结果状态码
     */
    public function chargeUp($cardid, $cardpass)
    {
        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->session_key, 'arg2' => $cardid, 'arg3' => $cardpass);
        return $this->api('chargeUp', array('parameters' => $params));
    }

    protected function sendTemplateMessage($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null)
    {
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }

    public function sendMessage($phoneNumbers, $content, $signName = null, $outId = null)
    {
        if (!is_array($phoneNumbers))
            $phoneNumbers = [$phoneNumbers];
        $params = array('arg0' => $this->serialNumber, 'arg1' => $this->sessionKey, 'arg2' => '', 'arg3' => [], 'arg4' => $this->signName . $content, 'arg5' => '', 'arg6' => 'UTF-8', 'arg7' => 5, 'arg8' => 888);
        foreach ($phoneNumbers as $mobile) {
            array_push($params ['arg3'], $mobile);
        }
        return $this->api('sendSMS', $params);
    }
}