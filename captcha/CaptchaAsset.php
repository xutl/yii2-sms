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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaAsset extends AssetBundle
{
    public $sourcePath = '@xutl/sms/captcha/assets';

    public $js = [
        'js/yii.smscaptcha.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
