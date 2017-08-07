<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * Collection is a storage for all sms clients in the application.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'sms' => [
 *         'class' => 'xutl\sms\Collection',
 *         'clients' => [
 *             'aliyun' => [
 *                 'class' => 'xutl\aliyun\clients\Aliyun',
 *                 'accessId' => 'access_id',
 *                 'accessKey' => 'access_secret',
 *              ],
 *             'yuntongxun' => [
 *                 'class' => 'xutl\aliyun\clients\Yuntongxun',
 *                 'accountSid' => 'account_sid',
 *                 'accountToken' => 'account_token',
 *                 'appId' => 'app_id',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 * @package xutl\sms
 */
class Collection extends Component
{
    /**
     * @var \yii\httpclient\Client|array|string HTTP client instance or configuration for the [[clients]].
     * If set, this value will be passed as 'httpClient' config option while instantiating particular client object.
     * This option is useful for adjusting HTTP client configuration for the entire list of auth clients.
     */
    public $httpClient;

    /**
     * @var array list of sms clients with their configuration in format: 'clientId' => [...]
     */
    private $_clients = [];

    /**
     * @param array $clients list of sms clients
     */
    public function setClients(array $clients)
    {
        $this->_clients = $clients;
    }

    /**
     * @return ClientInterface[] list of sms clients.
     */
    public function getClients()
    {
        $clients = [];
        foreach ($this->_clients as $id => $client) {
            $clients[$id] = $this->getClient($id);
        }
        return $clients;
    }

    /**
     * @param string $id service id.
     * @return ClientInterface sms client instance.
     * @throws InvalidParamException on non existing client request.
     */
    public function getClient($id)
    {
        if (!array_key_exists($id, $this->_clients)) {
            throw new InvalidParamException("Unknown sms client '{$id}'.");
        }
        if (!is_object($this->_clients[$id])) {
            $this->_clients[$id] = $this->createClient($id, $this->_clients[$id]);
        }
        return $this->_clients[$id];
    }

    /**
     * Checks if client exists in the hub.
     * @param string $id client id.
     * @return bool whether client exist.
     */
    public function hasClient($id)
    {
        return array_key_exists($id, $this->_clients);
    }

    /**
     * Creates sms client instance from its array configuration.
     * @param string $id sms client id.
     * @param array $config sms client instance configuration.
     * @return ClientInterface sms client instance.
     */
    protected function createClient($id, $config)
    {
        $config['id'] = $id;
        if (!isset($config['httpClient']) && $this->httpClient !== null) {
            $config['httpClient'] = $this->httpClient;
        }
        return Yii::createObject($config);
    }
}