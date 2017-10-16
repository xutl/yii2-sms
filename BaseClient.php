<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace xutl\sms;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\base\InvalidConfigException;

/**
 * Class BaseSms
 * @package xutl\sms
 */
abstract class BaseClient extends Component
{
    /**
     * @var string 网关地址
     */
    public $baseUrl;

    /**
     * @var array 短信模板配置
     */
    public $templates = [];

    /**
     * @var Client internal HTTP client.
     */
    private $_httpClient;

    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     */
    private $_requestOptions = [];

    /**
     * 初始化短信
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->baseUrl)) {
            throw new InvalidConfigException ('The "baseUrl" property must be set.');
        }
    }

    /**
     * Sends HTTP request.
     * @param string $method request type.
     * @param string|array $url use a string to represent a URL (e.g. `http://some-domain.com`, `item/list`),
     * or an array to represent a URL with query parameters (e.g. `['item/list', 'param1' => 'value1']`).
     * @param string|array $params request params.
     * @param array $headers additional request headers.
     * @return array response.
     * @throws Exception on failure.
     */
    protected function sendRequest($method, $url, $params = [], array $headers = [])
    {
        $request = $this->getHttpClient()
            ->createRequest()
            ->addOptions($this->defaultRequestOptions())
            ->addOptions($this->getRequestOptions())
            ->setUrl($url)
            ->setMethod($method)
            ->setHeaders($headers);
        if (is_array($params)) {
            $request->setData($params);
        } else {
            $request->setContent($params);
        }
        $response = $request->send();
        if (!$response->isOk) {
            throw new Exception('Request fail. response: ' . $response->content, $response->statusCode);
        }
        return $response->data;
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
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
            ]);
        }
        return $this->_httpClient;
    }

    /**
     * Sets HTTP client to be used.
     * @param array|Client $httpClient internal HTTP client.
     */
    public function setHttpClient($httpClient)
    {
        $this->_httpClient = $httpClient;
    }

    /**
     * 设置Http请求参数
     * @param array $options HTTP request options.
     */
    public function setRequestOptions(array $options)
    {
        $this->_requestOptions = $options;
    }

    /**
     * 获取Http请求参数
     * @return array HTTP request options.
     */
    public function getRequestOptions()
    {
        return $this->_requestOptions;
    }

    /**
     * Returns default HTTP request options.
     * @return array HTTP request options.
     */
    protected function defaultRequestOptions()
    {
        return [
            'timeout' => 30,
            'sslVerifyPeer' => false,
        ];
    }

    /**
     * 发送短信
     * @param string|array $phoneNumbers 接收者手机号
     * @param string $content 短信内容
     * @param string $signName 签名
     * @param string $outId
     * @return bool
     */
    public function send($phoneNumbers, $content, $signName = null, $outId = null)
    {
        if (is_array($phoneNumbers)) {
            $phoneNumbers = implode(', ', $phoneNumbers);
        }
        Yii::info('Sending sms "' . $content . '" to "' . $phoneNumbers . '"', __METHOD__);

        return $this->sendMessage($phoneNumbers, $content, $signName, $outId);
    }

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers
     * @param string $templateCode
     * @param array $templateParam
     * @param string $signName
     * @param string $outId
     * @return bool
     */
    public function sendTemplate($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null)
    {
        if (is_array($phoneNumbers)) {
            $phoneNumbers = implode(', ', $phoneNumbers);
        }
        Yii::info('Sending template sms "' . $templateCode . '" to "' . $phoneNumbers . '"', __METHOD__);

        return $this->sendTemplateMessage($phoneNumbers, $templateCode, $templateParam, $signName, $outId);
    }

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 手机号
     * @param string $content 内容
     * @param string $signName 签名
     * @param string $outId
     * @return mixed
     */
    abstract protected function sendMessage($phoneNumbers, $content, $signName = null, $outId = null);

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 手机号
     * @param string $template 模板
     * @param array $templateParam 模板参数
     * @param string $signName 签名
     * @param string $outId
     * @return mixed
     */
    abstract protected function sendTemplateMessage($phoneNumbers, $template, array $templateParam = [], $signName = null, $outId = null);
}