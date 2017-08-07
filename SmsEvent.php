<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use yii\base\Event;

class SmsEvent extends Event
{
    /**
     * @var \xutl\sms\MessageInterface the mail message being send.
     */
    public $message;

    /**
     * @var bool if message was sent successfully.
     */
    public $isSuccessful;

    /**
     * @var bool whether to continue sending an sms. Event handlers of
     * [[\xutl\sms\BaseSms::EVENT_BEFORE_SEND]] may set this property to decide whether
     * to continue send or not.
     */
    public $isValid = true;

}