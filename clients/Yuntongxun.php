<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\sms\clients;

use xutl\sms\BaseSms;
use Yii;
use yii\base\InvalidConfigException;

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
class Yuntongxun extends BaseSms
{
    /**
     * @var string 基础请求URL
     */
    public $baseUrl = 'https://app.cloopen.com:8883/';

    /**
     * @var string 账户SID
     */
    public $accountSid;

    /**
     * @var string 账户令牌
     */
    public $accountToken;

    /**
     * @var string 应用ID
     */
    public $appId;

    public $subAccountSid;

    public $subAccountToken;

    public $voIPAccount;

    public $voIPPassword;

    public $softVersion = '2013-12-26';

    public $bodyType = "json";//包体格式，可填值：json 、xml

    private $batch;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->accountSid)) {
            throw new InvalidConfigException ('The "accountSid" property must be set.');
        }
        if (empty ($this->accountToken)) {
            throw new InvalidConfigException ('The "accountToken" property must be set.');
        }
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        $this->baseUrl = $this->baseUrl . $this->softVersion;
    }

    /**
     * 设置主帐号
     *
     * @param string $accountSid 主帐号
     * @param string $accountToken 主帐号Token
     */
    public function setAccount($accountSid, $accountToken)
    {
        $this->accountSid = $accountSid;
        $this->accountToken = $accountToken;
    }

    /**
     * 设置子帐号
     *
     * @param string $subAccountSid 子帐号
     * @param string $subAccountToken 子帐号Token
     * @param string $voIPAccount VoIP帐号
     * @param string $voIpPassword VoIP密码
     */
    public function setSubAccount($subAccountSid, $subAccountToken, $voIPAccount, $voIpPassword)
    {
        $this->subAccountSid = $subAccountSid;
        $this->subAccountToken = $subAccountToken;
        $this->voIPAccount = $voIPAccount;
        $this->voIPPassword = $voIpPassword;
    }

    /**
     * 设置应用ID
     *
     * @param string $appId 应用ID
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * 主帐号信息查询
     * @return array|false
     */
    public function queryAccountInfo()
    {
        return $this->api("Accounts/$this->accountSid/AccountInfo", 'GET');
    }

    /**
     * 创建子帐号
     * @param string $friendlyName 子帐号名称
     * @return array|false
     */
    public function createSubAccount($friendlyName)
    {
        $params = ['appId' => $this->appId, 'friendlyName' => $friendlyName];
        return $this->api("Accounts/$this->accountSid/SubAccounts", 'POST', $params);
    }

    /**
     * 获取子帐号
     * @param int $startNo 开始的序号，默认从0开始
     * @param int $offset 一次查询的最大条数，最小是1条，最大是100条
     * @return array|false
     */
    public function getSubAccounts($startNo = 0, $offset = 10)
    {
        $params = ['appId' => $this->appId, 'startNo' => $startNo, 'offset' => $offset];
        return $this->api("Accounts/$this->accountSid/GetSubAccounts", 'POST', $params);
    }

    /**
     * 子帐号信息查询
     * @param string $friendlyName 子帐号名称
     * @return array|false
     */
    public function querySubAccount($friendlyName)
    {
        $params = ['appId' => $this->appId, 'friendlyName' => $friendlyName];
        return $this->api("Accounts/$this->accountSid/QuerySubAccountByName", 'POST', $params);
    }

    protected function sendSms($phoneNumbers, $content, $signName = null, $outId = null)
    {
        return [];
    }

    /**
     * 发送模板短信
     * @param array $to 短信接收手机号码集合,用英文逗号分开
     * @param array $templateParams 内容数据
     * @param string $tempId 模板Id
     * @return array
     */
    public function sendTemplateSMS($phoneNumbers, $templateCode, array $templateParams = [], $signName = null, $outId = null)
    {
        $params = ['appId' => $this->appId, 'to' => $phoneNumbers, 'templateId' => $templateCode, 'datas' => $templateParam];
        return $this->api("Accounts/$this->accountSid/SMS/TemplateSMS", 'POST', $params);
    }

    /**
     * 双向回呼
     * @param string $from 主叫电话号码
     * @param string $to 被叫电话号码
     * @param string $customerSerNum 被叫侧显示的客服号码
     * @param string $fromSerNum 主叫侧显示的号码
     * @param string $promptTone 自定义回拨提示音
     * @param string $userData 第三方私有数据
     * @param string $maxCallTime 最大通话时长
     * @param string $hangupCdrUrl 实时话单通知地址
     * @param string $alwaysPlay 是否一直播放提示音
     * @param string $terminalDtmf 用于终止播放promptTone参数定义的提示音
     * @param string $needBothCdr 是否给主被叫发送话单
     * @param string $needRecord 是否录音
     * @param string $countDownTime 设置倒计时时间
     * @param string $countDownPrompt 倒计时时间到后播放的提示音
     * @return array|false
     */
    public function callBack($from, $to, $customerSerNum, $fromSerNum, $promptTone, $alwaysPlay, $terminalDtmf, $userData, $maxCallTime, $hangupCdrUrl, $needBothCdr, $needRecord, $countDownTime, $countDownPrompt)
    {
        $params = [
            'from' => $from,
            'to' => $to,
            'customerSerNum' => $customerSerNum,
            'fromSerNum' => $fromSerNum,
            'promptTone' => $promptTone,
            'userData' => $userData,
            'maxCallTime' => $maxCallTime,
            'hangupCdrUrl' => $hangupCdrUrl,
            'alwaysPlay' => $alwaysPlay,
            'terminalDtmf' => $terminalDtmf,
            'needBothCdr' => $needBothCdr,
            'needRecord' => $needRecord,
            'countDownTime' => $countDownTime,
            'countDownPrompt' => $countDownPrompt,
        ];
        return $this->api("Accounts/$this->accountSid/Calls/Callback", 'POST', $params);
    }

    /**
     * 外呼通知
     * @param string $to 被叫号码
     * @param string $mediaName 语音文件名称，格式 wav。与mediaTxt不能同时为空。当不为空时mediaTxt属性失效。
     * @param string $mediaTxt 文本内容
     * @param string $displayNum 显示的主叫号码
     * @param string $playTimes 循环播放次数，1－3次，默认播放1次。
     * @param string $respUrl 外呼通知状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知。
     * @param string $userData 用户私有数据
     * @param string $maxCallTime 最大通话时长
     * @param string $speed 发音速度
     * @param string $volume 音量
     * @param string $pitch 音调
     * @param string $bgsound 背景音编号
     * @return array|false
     */
    public function landingCall($to, $mediaName, $mediaTxt, $displayNum, $playTimes, $respUrl, $userData, $maxCallTime, $speed, $volume, $pitch, $bgsound)
    {
        $params = [
            'playTimes' => $playTimes,
            'mediaTxt' => $mediaTxt,
            'to' => $to,
            'appId' => $this->appId,
            'displayNum' => $displayNum,
            'respUrl' => $respUrl,
            'userData' => $userData,
            'maxCallTime' => $maxCallTime,
            'speed' => $speed,
            'volume' => $volume,
            'pitch' => $pitch,
            'bgsound' => $bgsound,
        ];
        return $this->api("Accounts/$this->accountSid/Calls/LandingCalls", 'POST', $params);
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
     * @return array|false 成功返回结果，失败返回false
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
        return $this->api("Accounts/$this->accountSid/Calls/VoiceVerify", 'POST', $params);
    }

    /**
     * IVR外呼
     * @param string $number 待呼叫号码，为Dial节点的属性
     * @param string $userData 用户数据，在<startservice>通知中返回，只允许填写数字字符，为Dial节点的属性
     * @param string $record 是否录音，可填项为true和false，默认值为false不录音，为Dial节点的属性
     */
    public function ivrDial($number, $userData, $record)
    {
        // 拼接请求包体
        $body = " <Request>
                  <Appid>$this->AppId</Appid>
                  <Dial number='$number'  userdata='$userData' record='$record'></Dial>
                </Request>";
        $this->showlog("request body = " . $body);
        // 大写的sig参数
        $sig = strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
        // 生成请求URL
        $url = "https://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/ivr/dial?sig=$sig";
        $this->showlog("request url = " . $url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
        // 生成包头
        $header = array("Accept:application/xml", "Content-Type:application/xml;charset=utf-8", "Authorization:$authen");
        // 发送请求
        $result = $this->curlPost($url, $body, $header);
        $this->showlog("response body = " . $result);
        $datas = simplexml_load_string(trim($result, " \t\n\r"));

        return $datas;
    }

    /**
     * 话单下载
     * @param string $date day 代表前一天的数据（从00:00 – 23:59）
     * @param string $keywords 客户的查询条件，由客户自行定义并提供给云通讯平台。默认不填忽略此参数
     * @return array|false
     */
    public function billRecords($date, $keywords)
    {
        $params = [
            'appId' => $this->appId,
            'date' => $date,
            'keywords' => $keywords,
        ];
        return $this->api("Accounts/$this->accountSid/BillRecords", 'POST', $params);
    }

    /**
     * 取消回拨
     * @param string $callSid 一个由32个字符组成的电话唯一标识符
     * @param int $type 0： 任意时间都可以挂断电话；1 ：被叫应答前可以挂断电话，其他时段返回错误代码；2： 主叫应答前可以挂断电话，其他时段返回错误代码；默认值为0。
     * @return array|false
     */
    public function CallCancel($callSid, $type = 0)
    {
        $params = [
            'appId' => $this->appId,
            'callSid' => $callSid,
            'type' => $type,
        ];
        return $this->api("SubAccounts/$this->subAccountSid/Calls/CallCancel", 'POST', $params);
    }

    /**
     * 呼叫状态查询
     * @param string $callId 呼叫Id
     * @param string $action 查询结果通知的回调url地址
     * @return array|false
     */
    public function QueryCallState($callId, $action)
    {
        $params = [
            'appId' => $this->appId,
            'QueryCallState' => [
                'callid' => $callId,
                'action' => $action,
            ],
        ];
        return $this->api("SubAccounts/$this->subAccountSid/Calls/CallCancel?callid={$callId}", 'POST', $params);
    }

    /**
     * 呼叫结果查询
     * @param string $callSid 呼叫Id
     * @return array|boolean
     */
    public function CallResult($callSid)
    {
        return $this->api("Accounts/$this->accountSid/CallResult", 'POST', ['callsid' => $callSid]);
    }

    /**
     * 语音文件上传
     * @param string $filename 文件名
     * @param string $body 二进制串
     * @return array|false
     */
    public function MediaFileUpload($filename, $body)
    {
        return $this->api("Accounts/$this->accountSid/Calls/MediaFileUpload?appid=$this->appId&filename=$filename", 'UPLOAD', $body);
    }

    /**
     * 短信模板查询
     * @param string $templateId 模板ID
     * @return array|boolean
     */
    public function QuerySMSTemplate($templateId)
    {
        $params = ['appId' => $this->appId, 'templateId' => $templateId];
        return $this->api("Accounts/$this->accountSid/SMS/QuerySMSTemplate", 'POST', $params);
    }


    /**
     * 请求Api接口
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @return \yii\httpclient\Response
     */
    private function api($url, $method, array $params = [], array $headers = [])
    {
        $this->batch = date("YmdHis");
        $sign = strtoupper(md5($this->accountSid . $this->accountToken . $this->batch));
        if ($method == 'GET') {
            $params = array_merge($params, ['sig' => $sign]);
        } else if ($method == 'POST') {
            $url = $this->composeUrl($url, ['sig' => $sign]);
        }
        $client = new Client([
            'baseUrl' => $this->baseUrl,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_XML
            ],
        ]);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳
        $headers = array_merge($headers, [
            'Accept' => 'application/json',
            'Content-type' => 'application/json;charset=utf-8',
            'Authorization' => base64_encode($this->accountSid . ":" . $this->batch)]);
        $response = $client->createRequest()
            ->setHeaders($headers)
            ->setData($params)
            ->setMethod($method)
            ->setUrl($url)
            ->send();
        if ($response->content['statusCode'] == '0000') {
            unset($response->content['statusCode']);
            return $response->content;
        }
        Yii::error($response->content['statusCode'] . ':' . $response->content['statusMsg']);
        return false;
    }

    /**
     * 合并基础URL和参数
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $url;
    }
}