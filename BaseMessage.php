<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\base\BaseObject;
use yii\base\ErrorHandler;

/**
 * Class BaseMessage
 * @package xutl\sms
 */
abstract class BaseMessage extends BaseObject implements MessageInterface
{
    /**
     * @var SmsInterface the mailer instance that created this message.
     * For independently created messages this is `null`.
     */
    public $sms;

    /**
     * Sends this email message.
     * @param SmsInterface $mailer the mailer that should be used to send this message.
     * If no mailer is given it will first check if [[mailer]] is set and if not,
     * the "mail" application component will be used instead.
     * @return bool whether this message is sent successfully.
     */
    public function send(SmsInterface $mailer = null)
    {
        if ($mailer === null && $this->sms === null) {
            $mailer = Yii::$app->getSms();
        } elseif ($mailer === null) {
            $mailer = $this->sms;
        }
        return $mailer->send($this);
    }

    /**
     * PHP magic method that returns the string representation of this object.
     * @return string the string representation of this object.
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }
}