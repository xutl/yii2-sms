<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace xutl\sms\captcha;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[Captcha]] widget.
 * @package xutl\sms\captcha
 */
class CaptchaAsset extends AssetBundle
{
    /**
     * @inherit
     */
    public $sourcePath = '@vendor/xutl/yii2-sms/captcha/assets';

    public $js = [
        'js/yii.smsCaptcha.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
