<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms\clients;

use Yii;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use xutl\sms\Sms;
use yii\httpclient\Client;
use yii\httpclient\Exception;

/**
 * Class Yuntongxun
 * 'components' => [
 *     'sms' => [
 *         'aliyun' => [
 *             'class' => 'xutl\aliyun\clients\Yuntongxun',
 *             'accountSid' => 'account_sid',
 *             'accountToken' => 'account_token',
 *             'appId' => 'app_id',
 *          ],
 *     ]
 *     ...
 * ]
 * ```
 * @package yuncms\sms\clients
 */
class Yuntongxun extends Sms
{
    /**
     * @var string 基础请求URL
     */
    public $baseUrl = 'https://app.cloopen.com:8883/';

    /**
     * @var string 应用ID
     */
    public $appId;

    /**
     * @var string 账户SID
     */
    public $accountId;

    /**
     * @var string 账户令牌
     */
    public $accountToken;

    public $softVersion = '2013-12-26';

    /**
     * @var Client internal HTTP client.
     */
    private $_httpClient;

    private $batch;
    private $sign;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->accountId)) {
            throw new InvalidConfigException ('The "accountSid" property must be set.');
        }
        if (empty ($this->accountToken)) {
            throw new InvalidConfigException ('The "accountToken" property must be set.');
        }
        $this->baseUrl = $this->baseUrl . $this->softVersion;
        $this->batch = date("YmdHis");
        $this->sign = strtoupper(md5($this->accountId . $this->accountToken . $this->batch));
    }

    /**
     * 主帐号信息查询
     * @return array|false
     */
    public function queryAccountInfo()
    {
        return $this->api("Accounts/$this->accountId/AccountInfo", 'GET');
    }

    /**
     * 创建子帐号
     * @param string $friendlyName 子帐号名称
     * @return array
     */
    public function createSubAccount($friendlyName)
    {
        $params = ['appId' => $this->appId, 'friendlyName' => $friendlyName];
        return $this->api("Accounts/$this->accountId/SubAccounts", 'POST', $params);
    }

    /**
     * 获取子帐号
     * @param int $startNo 开始的序号，默认从0开始
     * @param int $offset 一次查询的最大条数，最小是1条，最大是100条
     * @return array
     */
    public function getSubAccounts($startNo = 0, $offset = 10)
    {
        $params = ['appId' => $this->appId, 'startNo' => $startNo, 'offset' => $offset];
        return $this->api("Accounts/$this->accountId/GetSubAccounts", 'POST', $params);
    }

    /**
     * 子帐号信息查询
     * @param string $friendlyName 子帐号名称
     * @return array
     */
    public function querySubAccount($friendlyName)
    {
        $params = ['appId' => $this->appId, 'friendlyName' => $friendlyName];
        return $this->api("Accounts/$this->accountId/QuerySubAccountByName", 'POST', $params);
    }

    /**
     * 发送短信
     * @param array|string $phoneNumbers
     * @param string $content
     * @param null $signName
     * @param null $outId
     * @return mixed|void
     * @throws NotSupportedException
     */
    protected function sendMessage($phoneNumbers, $content, $signName = null, $outId = null)
    {
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }

    /**
     * 发送模板短信
     * @param array|string $phoneNumbers 短信接收手机号码集合,用英文逗号分开
     * @param array $templateParams 内容数据
     * @param string $templateCode 模板Id
     * @return array
     */
    public function sendTemplateMessage($phoneNumbers, $templateCode, array $templateParams = [], $signName = null, $outId = null)
    {
        $params = ['appId' => $this->appId, 'to' => $phoneNumbers, 'templateId' => $templateCode, 'datas' => $templateParams];
        return $this->api("Accounts/$this->accountId/SMS/TemplateSMS", 'POST', $params);
    }


    /**
     * 语音验证码
     * @param string $to 接收号码
     * @param string $verifyCode 验证码内容，为数字和英文字母，不区分大小写，长度4-8位
     * @param int $playTimes 播放次数，1－3次
     * @param string $displayNum 显示的主叫号码
     * @param string $respUrl 语音验证码状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知
     * @param string $lang 语言类型
     * @param string $userData 第三方私有数据
     * @return array
     */
    public function voiceVerify($to, $verifyCode, $playTimes = 2, $displayNum = '', $respUrl = '', $lang = 'zh', $userData = '')
    {
        $params = [
            'appId' => $this->appId,
            'verifyCode' => $verifyCode,
            'playTimes' => $playTimes,
            'to' => $to,
            'respUrl' => $respUrl,
            'displayNum' => $displayNum,
            'lang' => $lang,
            'userData' => $userData,
        ];
        return $this->api("Accounts/$this->accountId/Calls/VoiceVerify", 'POST', $params);
    }

    /**
     * 短信模板查询
     * @param string $templateId 模板ID
     * @param $templateId
     * @return array
     */
    public function QuerySMSTemplate($templateId)
    {
        $params = ['appId' => $this->appId, 'templateId' => $templateId];
        return $this->api("Accounts/$this->accountSid/SMS/QuerySMSTemplate", 'POST', $params);
    }

    /**
     * 请求Api接口
     * @param string|array $url
     * @param string $method
     * @param array|string $params
     * @param array $headers
     * @return mixed
     * @throws Exception
     */
    private function api($url, $method, array $params = [], array $headers = [])
    {
        $url = (array)$url;
        $url['sig'] = $this->sign;

        // 生成授权：主帐户Id + 英文冒号 + 时间戳
        $headers = array_merge($headers, ['Authorization' => base64_encode($this->accountId . ":" . $this->batch)]);

        /** @var \yii\httpclient\Response $response */
        $response = $this->sendRequest($method, $url, $params, $headers);
        if ($response['statusCode'] == '000000') {
            return $response['TemplateSMS'];
        } else {
            throw new Exception('Request fail. response: ' . $response['statusMsg'], $response['statusCode']);
        }
    }

    /**
     * Returns HTTP client.
     * @return Client internal HTTP client.
     */
    public function getHttpClient()
    {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = new Client([
                'baseUrl' => $this->baseUrl,
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_XML
                ],
            ]);
        }
        return $this->_httpClient;
    }
}