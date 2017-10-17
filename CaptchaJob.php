<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\base\Object;
use yii\queue\RetryableJob;

/**
 * Class SendJob
 * @package xutl\sms
 */
class CaptchaJob extends Object implements RetryableJob
{
    /**
     * @var string Mobile number
     */
    public $mobile;

    public $templateCode;

    /**
     * @var string
     */
    public $code;

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