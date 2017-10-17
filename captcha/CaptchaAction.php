<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace xutl\sms\captcha;

use xutl\sms\SendJob;
use Yii;
use yii\base\Action;
use yii\caching\Cache;
use yii\di\Instance;
use yii\helpers\Url;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * CaptchaAction send a sms message.
 *
 * @property string $verifyCode The verification code. This property is read-only.
 *
 */
class CaptchaAction extends Action
{
    /**
     * @var int 两次获取验证码的等待时间
     */
    public $waitTime = 60;

    /**
     * @var int 测试验证码次数
     */
    public $testLimit = 3;

    /**
     * @var int 最短长度
     */
    public $minLength = 4;

    /**
     * @var int 最长长度
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

    /**
     * @var string 短信发送任务
     */
    public $sendJobClass = 'xutl\sms\CaptchaJob';

    /**
     * @var Cache|string
     */
    public $cache = 'cache';

    /**
     * @var string 会话ID
     */
    private $sessionKey;

    /**
     * @var string 手机号
     */
    private $mobile;

    /**
     * 初始化组件
     */
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        if (!Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException();
        }
        if (($this->mobile = Yii::$app->request->post('mobile')) == null) {
            throw new MethodNotAllowedHttpException();
        }
        $this->sessionKey = $this->getSessionKey($this->mobile);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (time() - $this->cache->get($this->sessionKey . 'time') < 60) {
            $code = $this->getVerifyCode(false);
            return [
                'hash' => $this->generateValidationHash($code),
                'url' => Url::to([$this->id, 'v' => uniqid()]),
                'waitTime' => $this->waitTime,
                'mobile' => $this->mobile,
            ];
        } else {
            $code = $this->getVerifyCode(true);
            $this->cache->set($this->sessionKey . 'mobile', $this->mobile);
            Yii::$app->queue->push(new $this->sendJobClass([
                'mobile' => $this->mobile,
                'code' => $code,
            ]));
            return [
                'hash' => $this->generateValidationHash($code),
                'url' => Url::to([$this->id, 'v' => uniqid()]),
                'waitTime' => $this->waitTime,
                'mobile' => $this->mobile,
            ];
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

        $verifyCode = $this->cache->get($this->sessionKey);
        if ($verifyCode === null || $regenerate) {
            $verifyCode = $this->generateVerifyCode();
            $this->cache->set($this->sessionKey, $verifyCode, $this->waitTime);
            $this->cache->set($this->sessionKey . 'mobile', $this->mobile, 86400);
            $this->cache->set($this->sessionKey . 'count', 1, 86400);
            $this->cache->set($this->sessionKey . 'time', time(), 86400);
        }
        return $verifyCode;
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

        $count = $this->cache->get($this->sessionKey . 'count');
        $count = $count + 1;
        if ($valid || $count > $this->testLimit && $this->testLimit > 0) {
            $this->getVerifyCode(true);
        }
        $this->cache->set($this->sessionKey . 'count', $count, 86400);
        return $valid;
    }

    /**
     * 验证提交的手机号时候和接收验证码的手机号一致
     * @param string $input user input
     * @return boolean whether the input is valid
     */
    public function validateMobile($input)
    {
        $mobile = $this->cache->get($this->sessionKey . 'mobile');
        $valid = strcasecmp($mobile, $input) === 0;
        return $valid;
    }

    /**
     * 生成一个可以用于客户端验证的哈希。
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

        $letters = '678906789067890678906';
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
     * @param string $mobile 手机号
     * @return string the session variable name
     */
    protected function getSessionKey($mobile)
    {
        return '__smsCaptcha/' . $mobile;
    }
}
