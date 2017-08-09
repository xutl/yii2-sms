<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\di\Instance;
use yii\base\Component;
use yii\helpers\Inflector;
use yii\httpclient\Client;
use yii\helpers\StringHelper;
use yii\base\InvalidConfigException;

/**
 * Class BaseClient
 * @package xutl\sms
 */
abstract class Sms extends Component
{
    /**
     * @var string 短信签名
     */
    public $signName;

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
     * @param string|array $phoneNumbers
     * @param string $content
     * @param string $signName
     * @param string $outId
     * @return mixed
     */
    abstract protected function sendMessage($phoneNumbers, $content, $signName = null, $outId = null);

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers
     * @param string $templateCode
     * @param array $templateParam
     * @param string $signName
     * @param string $outId
     * @return mixed
     */
    abstract protected function sendTemplateMessage($phoneNumbers, $templateCode, array $templateParam = [], $signName = null, $outId = null);
}