<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\base\Component;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\httpclient\Client;

/**
 * Class BaseClient
 * @package xutl\sms
 */
abstract class BaseClient extends Component implements ClientInterface
{
    /**
     * @var string 短信签名
     */
    public $signName;

    /**
     * @var bool 存储到文件中
     */
    public $useFileTransport = false;

    /**
     * @var string auth service id.
     * This value mainly used as HTTP request parameter.
     */
    private $_id;

    /**
     * @var string auth service name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name;

    /**
     * @var Client|array|string internal HTTP client.
     * @since 2.1
     */
    private $_httpClient = 'yii\httpclient\Client';

    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultRequestOptions()]].
     * @since 2.1
     */
    private $_requestOptions = [];

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->signName)) {
            throw new InvalidConfigException ('The "signName" property must be set.');
        }
    }

    /**
     * @param string $id service id.
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string service id
     */
    public function getId()
    {
        if (empty($this->_id)) {
            $this->_id = $this->getName();
        }

        return $this->_id;
    }

    /**
     * @param string $name service name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string service name.
     */
    public function getName()
    {
        if ($this->_name === null) {
            $this->_name = $this->defaultName();
        }

        return $this->_name;
    }

    /**
     * 发送短信
     * @param array|string $phoneNumbers
     * @param string $content
     * @param string $signName
     * @param string $outId
     * @return array
     */
    public function send($phoneNumbers, $content, $signName = null, $outId = null)
    {
        if (is_array($phoneNumbers)) {
            $phoneNumbers = implode(',', $phoneNumbers);
        }
        return $this->sendSms($phoneNumbers, $content, $signName, $outId);
    }

    /**
     * @param string $phoneNumbers
     * @param string $content
     * @param string $signName
     * @param string $outId
     * @return array
     */
    abstract protected function sendSms($phoneNumbers, $content, $signName = null, $outId = null);

    /**
     * 发送模板短信
     * @param array|string $phoneNumbers 短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码
     * @param string $signName 短信签名
     * @param string $templateID 短信模板ID
     * @param array $templateParams 短信模板变量替换JSON串。
     * @param string $outId 外部流水扩展字段
     * @return array
     */
    public function sendTemplate($phoneNumbers, $templateID, array $templateParams = [], $signName = null, $outId = null)
    {
        if (is_array($phoneNumbers)) {
            $phoneNumbers = implode(',', $phoneNumbers);
        }
        return $this->sendTemplateSms($phoneNumbers, $templateID, $templateParams, $signName, $outId);
    }

    /**
     * @param string $phoneNumbers
     * @param string $templateCode
     * @param array $templateParam
     * @param string $signName
     * @param string $outId
     * @return array
     */
    abstract protected function sendTemplateSms($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null);

    /**
     * @param array $options HTTP request options.
     * @since 2.1
     */
    public function setRequestOptions(array $options)
    {
        $this->_requestOptions = $options;
    }

    /**
     * @return array HTTP request options.
     * @since 2.1
     */
    public function getRequestOptions()
    {
        return $this->_requestOptions;
    }

    /**
     * Creates HTTP request instance.
     * @return \yii\httpclient\Request HTTP request instance.
     * @since 2.1
     */
    public function createRequest()
    {
        return $this->getHttpClient()
            ->createRequest()
            ->addOptions($this->defaultRequestOptions())
            ->addOptions($this->getRequestOptions());
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
     * Creates HTTP client instance from reference or configuration.
     * @param string|array $reference component name or array configuration.
     * @return Client HTTP client instance.
     */
    protected function createHttpClient($reference)
    {
        return Instance::ensure($reference, Client::className());
    }

    /**
     * Returns HTTP client.
     * @return Client internal HTTP client.
     */
    public function getHttpClient()
    {
        if (!is_object($this->_httpClient)) {
            $this->_httpClient = $this->createHttpClient($this->_httpClient);
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
     * Generates service name.
     * @return string service name.
     */
    protected function defaultName()
    {
        return Inflector::camel2id(StringHelper::basename(get_class($this)));
    }
}