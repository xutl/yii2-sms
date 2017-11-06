<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\queue\RetryableJobInterface;

/**
 * 发送短信
 * @package xutl\sms
 */
class SendJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var string Mobile number
     */
    public $mobile;

    /**
     * @var string 模板代码
     */
    public $templateCode;

    /**
     * @var array 模板参数
     */
    public $templateParam;

    /**
     * @var \xutl\sms\BaseClient
     */
    public $sms = 'sms';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->sms = Instance::ensure($this->sms, 'xutl\sms\BaseClient');
    }

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $this->sms->sendTemplate($this->mobile, $this->getTemplateCode(), $this->getTemplateParam());
    }

    /**
     * 获取模板
     * @return string
     */
    public function getTemplateCode()
    {
        return $this->templateCode;
    }

    /**
     * 获取参数
     * @return array
     */
    public function getTemplateParam()
    {
        return $this->templateParam;
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}