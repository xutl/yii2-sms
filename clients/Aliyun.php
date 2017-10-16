<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms\clients;

use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use xutl\sms\BaseClient;

/**
 * 阿里云短消息接口
 *
 * 'components' => [
 *     'sms' => [
 *         'class' => 'xutl\sms\clients\Aliyun',
 *         'accessId' => 'access_id',
 *         'accessKey' => 'access_key',
 *     ]
 *     ...
 * ]
 * ```
 * @package xutl\sms\clients
 */
class Aliyun extends BaseClient
{
    /**
     * @var string 阿里云AccessKey ID
     */
    public $accessId;

    /**
     * @var string AccessKey
     */
    public $accessKey;

    /**
     * @var string 短信签名
     */
    public $signName;

    /**
     * @var string 网关地址
     */
    public $baseUrl = 'https://dysmsapi.aliyuncs.com/';

    /**
     * @var string Api接口版本
     */
    public $version = '2017-05-25';

    /**
     * @var string
     */
    protected $signatureMethod = 'HMAC-SHA1';

    /**
     * @var string
     */
    protected $signatureVersion = '1.0';

    /**
     * @var string
     */
    protected $dateTimeFormat = 'Y-m-d\TH:i:s\Z';

    /**
     * 初始化短信
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->accessId)) {
            throw new InvalidConfigException ('The "accessId" property must be set.');
        }
        if (empty ($this->accessKey)) {
            throw new InvalidConfigException ('The "accessKey" property must be set.');
        }
        if (empty ($this->signName)) {
            throw new InvalidConfigException ('The "signName" property must be set.');
        }
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
     * @param string $phoneNumbers
     * @param string $template
     * @param array $templateParam
     * @param string $signName
     * @param string $outId
     * @return mixed
     */
    protected function sendTemplateMessage($phoneNumbers, $template, array $templateParam = [], $signName = null, $outId = null)
    {
        return $this->api('', 'GET', [
            'Action' => 'SendSms',
            'PhoneNumbers' => $phoneNumbers,
            'SignName' => $signName ? $signName : $this->signName,
            'TemplateCode' => $template,
            'TemplateParam' => Json::encode($templateParam),
            'OutId' => $outId
        ]);
    }

    /**
     * 短信查询API
     * @param string $phoneNumber
     * @param int $sendDate
     * @param int $pageSize
     * @param int $currentPage
     * @param null $bizId
     * @return array
     */
    public function querySendDetails($phoneNumber, $sendDate, $pageSize = 10, $currentPage = 1, $bizId = null)
    {
        return $this->api('', 'GET', [
            'Action' => 'QuerySendDetails',
            'PhoneNumber' => $phoneNumber,
            'BizId' => $bizId,
            'SendDate' => $sendDate,
            'PageSize' => $pageSize,
            'CurrentPage' => $currentPage
        ]);
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
    public function api($url, $method, $params = [], array $headers = [])
    {
        $params['Version'] = $this->version;
        $params['Format'] = 'JSON';
        $params['AccessKeyId'] = $this->accessId;
        $params['SignatureMethod'] = $this->signatureMethod;
        $params['Timestamp'] = gmdate($this->dateTimeFormat);
        $params['SignatureVersion'] = $this->signatureVersion;
        $params['SignatureNonce'] = uniqid();

        //参数排序
        ksort($params);
        $query = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        $source = strtoupper($method) . '&%2F&' . $this->percentEncode($query);

        //签名
        $params['Signature'] = base64_encode(hash_hmac('sha1', $source, $this->accessKey . '&', true));

        /** @var \yii\httpclient\Response $response */
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * 参数转码
     * @param string $str
     * @return mixed|string
     */
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}