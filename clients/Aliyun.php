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
use xutl\sms\BaseSms;

/**
 * 阿里云短消息接口
 *
 * 'components' => [
 *     'sms' => [
 *         'aliyun' => [
 *             'class' => 'xutl\aliyun\clients\Aliyun',
 *             'accessId' => 'access_id',
 *             'accessKey' => 'access_key',
 *          ],
 *     ]
 *     ...
 * ]
 * ```
 * @package xutl\sms\clients
 */
class Aliyun extends BaseSms
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
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->baseUrl)) {
            throw new InvalidConfigException ('The "baseUrl" property must be set.');
        }
        if (empty ($this->accessId)) {
            throw new InvalidConfigException ('The "accessId" property must be set.');
        }
        if (empty ($this->accessKey)) {
            throw new InvalidConfigException ('The "accessKey" property must be set.');
        }
        if (empty ($this->version)) {
            throw new InvalidConfigException ('The "version" property must be set.');
        }
    }

    /**
     * 发送短信
     * @param string $phoneNumbers
     * @param string $content
     * @param null $signName
     * @param null $outId
     * @return array
     */
    protected function sendSms($phoneNumbers, $content, $signName = null, $outId = null)
    {
        return [];
    }

    /**
     * 发送模板短信
     * @param string $phoneNumbers
     * @param string $templateCode
     * @param array $templateParam
     * @param string $signName
     * @param string $outId
     * @return mixed
     */
    protected function sendTemplateSms($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null)
    {
        return $this->api('', 'GET', [
            'Action' => 'SendSms',
            'PhoneNumbers' => $phoneNumbers,
            'SignName' => $signName ? $signName : $this->signName,
            'TemplateCode' => $templateCode,
            'TemplateParam' => Json::encode($templateParam),
            'OutId' => $outId
        ]);
    }

    /**
     *
     * @param string $url
     * @param string $method
     * @param array $params
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
        $response = $this->createRequest()
            ->setUrl($url)
            ->setMethod($method)
            ->setHeaders($headers)
            ->setData($params)
            ->send();

        if (!$response->isOk) {
            throw new Exception($response->content, $response->statusCode);
        }
        return $response->data;
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