<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

class TestMessage extends BaseMessage
{
    public $message = [];

    /**
     * @inheritdoc
     */
    public function getPhoneNumbers()
    {
        return isset($this->message['to']) ? $this->message['to'] : null;
    }

    /**
     * @inheritdoc
     */
    public function setPhoneNumbers($to)
    {
        $this->message['to'] = $to;
        return $this;
    }

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return $this self reference.
     */
    public function setBody($text)
    {

    }

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString()
    {
        return "_tostring()_method";
    }
}