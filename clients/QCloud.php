<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms\clients;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use xutl\sms\BaseClient;

/**
 * Class QCloud
 * @package xutl\sms\clients
 */
class QCloud extends BaseClient
{
    /**
     * @var string
     */
    public $baseUrl = 'https://yun.tim.qq.com';
    /**
     * @var string 应用ID
     */
    public $appId;

    /**
     * @var string 应用Key
     */
    public $appKey;

    /**
     * @var Client internal HTTP client.
     */
    private $_httpClient;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->appKey)) {
            throw new InvalidConfigException ('The "appKey" property must be set.');
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
                    'format' => Client::FORMAT_JSON
                ],
            ]);
        }
        return $this->_httpClient;
    }

    /**
     * 创建签名
     * @param string $random
     * @param integer $curTime
     * @param string $phoneStrings
     * @return string
     */
    protected function makeSign($random, $curTime, $phoneStrings)
    {
        return hash("sha256", "appkey=" . $this->appKey . "&random=" . $random
            . "&time=" . $curTime . "&mobile=" . $phoneStrings);
    }

    /**
     * 发送短信
     * @param string|array $phoneNumbers 接收者手机号
     * @param string $content 短信内容
     * @param string $signName 签名
     * @param string $outId 用户的session内容，腾讯server回包中会原样返回，可选字段，不需要就填空。
     * @return bool
     */
    public function send($phoneNumbers, $content, $signName = null, $outId = null)
    {
        //Yii::info('Sending sms "' . $content . '" to "' . $phoneNumbers . '"', __METHOD__);
        return $this->sendMessage($phoneNumbers, $content, $signName, $outId);
    }

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 接收者手机号
     * @param string $templateCode 模板代码
     * @param array $templateParam 模板参数
     * @param string $signName 签名
     * @param string $outId 用户的session内容，腾讯server回包中会原样返回，可选字段，不需要就填空。
     * @return bool
     */
    public function sendTemplate($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null)
    {
        //Yii::info('Sending template sms "' . $templateCode . '" to "' . $phoneNumbers . '"', __METHOD__);
        return $this->sendTemplateMessage($phoneNumbers, $templateCode, $templateParam, $signName, $outId);
    }

    /**
     * 发送短信
     * @param array|string $phoneNumbers 手机号
     * @param string $content 内容
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendMessage($phoneNumbers, $content, $signName = null, $outId = null)
    {
        return $this->sendInternationalMessage(86, $phoneNumbers, $content, $signName, $outId);
    }

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 手机号
     * @param string $template 模板
     * @param array $templateParam 模板参数
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendTemplateMessage($phoneNumbers, $template, array $templateParam = [], $signName = null, $outId = null)
    {
        return $this->sendInternationalTemplateMessage(86, $phoneNumbers, $template, $templateParam, $signName, $outId);
    }

    /**
     * 发送模板短信
     * @param string $nationCode 国家代码
     * @param string|array $phoneNumbers 手机号
     * @param string $content 内容
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendInternationalMessage($nationCode, $phoneNumbers, $content, $signName = null, $outId = null)
    {
        $random = Yii::$app->security->generateRandomString(10);
        $url = ['v5/tlssmssvr/sendmultisms2', 'sdkappid' => $this->appId, 'random' => $random];
        $tel = [];
        $curTime = time();
        if (is_array($phoneNumbers)) {
            $phoneNumbersString = implode(',', $phoneNumbers);
            foreach ($phoneNumbers as $phoneNumber) {
                $tel[] = [
                    'nationcode' => $nationCode,
                    'mobile' => $phoneNumber,
                ];
            }
            $sig = $this->makeSign($random, $curTime, $phoneNumbersString);
        } else {
            $tel[] = [
                'nationcode' => $nationCode,
                'mobile' => $phoneNumbers,
            ];
            $sig = $this->makeSign($random, $curTime, $phoneNumbers);
        }
        $data = [
            'tel' => $tel,
            'type' => 0,//0:普通短信;1:营销短信（强调：要按需填值，不然会影响到业务的正常使用）
            'msg' => $content,
            'sig' => $sig,
            'time' => $curTime,
            'extend' => '',
            'ext' => $outId
        ];
        return $this->sendRequest('POST', $url, $data);
    }

    /**
     * 发送模板短信
     * @param string $nationCode 国家代码
     * @param string|array $phoneNumbers 手机号
     * @param string $template 模板
     * @param array $templateParam 模板参数
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendInternationalTemplateMessage($nationCode, $phoneNumbers, $template, array $templateParam = [], $signName = null, $outId = null)
    {
        $random = Yii::$app->security->generateRandomString(10);
        $url = ['v5/tlssmssvr/sendmultisms2', 'sdkappid' => $this->appId, 'random' => $random];
        $tel = [];
        $curTime = time();
        if (is_array($phoneNumbers)) {
            $phoneNumbersString = implode(',', $phoneNumbers);
            foreach ($phoneNumbers as $phoneNumber) {
                $tel[] = [
                    'nationcode' => '86',
                    'mobile' => $phoneNumber,
                ];
            }
            $sig = $this->makeSign($random, $curTime, $phoneNumbersString);
        } else {
            $tel[] = [
                'nationcode' => '86',
                'mobile' => $phoneNumbers,
            ];
            $sig = $this->makeSign($random, $curTime, $phoneNumbers);
        }
        $data = [
            'tel' => $tel,
            'type' => 0,//0:普通短信;1:营销短信（强调：要按需填值，不然会影响到业务的正常使用）
            'tpl_id' => $template,
            'params' => $templateParam,
            'sig' => $sig,
            'time' => $curTime,
            'extend' => '',
            'ext' => $outId
        ];
        return $this->sendRequest('POST', $url, $data);
    }
}