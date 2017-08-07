<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

class Test extends BaseSms
{
    /**
     * @var string the default class name of the new message instances created by [[createMessage()]]
     */
    public $messageClass = 'xutl\sms\TestMessage';

    protected function sendMessage($message)
    {

    }
}