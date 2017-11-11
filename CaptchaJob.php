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
use yii\queue\RetryableJob;

/**
 * 发送短信验证码
 * @package xutl\sms
 */
class CaptchaJob extends BaseObject implements RetryableJob
{
    /**
     * @var string Mobile number
     */
    public $mobile;

    /**
     * @var string 短信模板代码
     */
    public $templateCode;

    /**
     * @var string 验证码
     */
    public $code;

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
        Yii::$app->sms->sendTemplate($this->mobile, $this->getTemplateCode(), $this->getTemplateParam());
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
        return [];
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