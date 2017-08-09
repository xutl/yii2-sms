<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace xutl\sms\captcha;

use Yii;
use yii\base\Action;
use yii\di\Instance;
use yii\helpers\Url;
use yii\web\Response;
use xutl\sms\Sms;

/**
 * CaptchaAction renders a CAPTCHA image.
 *
 * CaptchaAction is used together with [[Captcha]] and [[\yii\captcha\CaptchaValidator]]
 * to provide the [CAPTCHA](http://en.wikipedia.org/wiki/Captcha) feature.
 *
 * By configuring the properties of CaptchaAction, you may customize the appearance of
 * the generated CAPTCHA images, such as the font color, the background color, etc.
 *
 * Note that CaptchaAction requires either GD2 extension or ImageMagick PHP extension.
 *
 * Using CAPTCHA involves the following steps:
 *
 * 1. Override [[\yii\web\Controller::actions()]] and register an action of class CaptchaAction with ID 'captcha'
 * 2. In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 *    to be validated by the 'captcha' validator.
 * 3. In the controller view, insert a [[Captcha]] widget in the form.
 *
 * @property string $verifyCode The verification code. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaAction extends Action
{
    /**
     * @var int 两次获取验证码的等待时间
     */
    public $waitTime = 60;

    /**
     * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
     * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
     */
    public $testLimit = 3;

    /**
     * @var integer the minimum length for randomly generated word. Defaults to 6.
     */
    public $minLength = 4;

    /**
     * @var integer the maximum length for randomly generated word. Defaults to 7.
     */
    public $maxLength = 6;

    /**
     * @var string the fixed verification code. When this property is set,
     * [[getVerifyCode()]] will always return the value of this property.
     * This is mainly used in automated tests where we want to be able to reproduce
     * the same verification code each time we run the tests.
     * If not set, it means the verification code will be randomly generated.
     */
    public $fixedVerifyCode;

    public $smsTemplateCode = 'SMS_82545002';

    /**
     * @var string|Sms
     */
    public $sms = 'sms';

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        $this->sms = Instance::ensure($this->sms, Sms::class);
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        if (Yii::$app->request->isPost && ($mobile = Yii::$app->request->post('mobile')) != null) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $session = Yii::$app->getSession();
            $session->open();
            $name = $this->getSessionKey();
            if (time() - $session[$name . 'time'] < 60) {
                $code = $this->getVerifyCode(false);
            } else {
                $code = $this->getVerifyCode(true);
                $session['newMobile'] = $mobile;
                $this->sms->sendTemplate($mobile, $this->smsTemplateCode, ['code' => $code]);
            }
            $return = [
                'hash' => $this->generateValidationHash($code),
                'url' => Url::to([$this->id, 'v' => uniqid()]),
                'waitTime' => $this->waitTime,
                'mobile' => $mobile,
            ];
            if (YII_DEBUG) {
                $return['code'] = $code;
            }
            return $return;
        }
    }

    /**
     * 获取验证码
     * @param boolean $regenerate 是否重新生成验证码
     * @return string 验证码
     */
    public function getVerifyCode($regenerate = false)
    {
        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }

        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey();
        if ($session[$name] === null || $regenerate) {
            $session[$name] = $this->generateVerifyCode();
            $session[$name . 'count'] = 1;
            $session[$name . 'time'] = time();
        }
        return $session[$name];
    }

    /**
     * 验证输入，看看它是否与生成的代码相匹配
     * @param string $input user input
     * @param boolean $caseSensitive whether the comparison should be case-sensitive
     * @return boolean whether the input is valid
     */
    public function validate($input, $caseSensitive)
    {
        $code = $this->getVerifyCode();
        $valid = $caseSensitive ? ($input === $code) : strcasecmp($input, $code) === 0;
        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey() . 'count';
        $session[$name] = $session[$name] + 1;
        if ($valid || $session[$name] > $this->testLimit && $this->testLimit > 0) {
            $this->getVerifyCode(true);
        }
        return $valid;
    }

    /**
     * 验证提交的手机号时候和接收验证码的手机号一致
     * @param string $input user input
     * @return boolean whether the input is valid
     */
    public function validateMobile($input)
    {
        $session = Yii::$app->getSession();
        $session->open();
        $valid = strcasecmp($session['newMobile'], $input) === 0;
        return $valid;
    }

    /**
     * 生成一个可以用于客户端验证的哈希代码。
     * @param string $code 验证码
     * @return string 用户客户端验证的哈希码
     */
    public function generateValidationHash($code)
    {
        for ($h = 0, $i = strlen($code) - 1; $i >= 0; --$i) {
            $h += $code[$i];
        }
        return $h;
    }

    /**
     * 生成验证码
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        if ($this->minLength > $this->maxLength) {
            $this->maxLength = $this->minLength;
        }
        if ($this->minLength < 4) {
            $this->minLength = 4;
        }
        if ($this->maxLength > 20) {
            $this->maxLength = 20;
        }
        $length = mt_rand($this->minLength, $this->maxLength);

        $letters = '6789067890678906789067890';
        $vowels = '12345';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }

        return $code;
    }

    /**
     * 返回用于存储验证代码的会话变量名
     * @return string the session variable name
     */
    protected function getSessionKey()
    {
        return '__smsCaptcha/' . $this->getUniqueId();
    }
}
