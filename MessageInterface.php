<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

interface MessageInterface
{
    /**
     * Returns the message recipient(s).
     * @return array the message recipients
     */
    public function getPhoneNumbers();

    /**
     * Sets the message recipient(s).
     * @param string|array $phoneNumbers receiver phone numbers.
     * You may pass an array of numbers if multiple recipients should receive this message.
     * You may also specify receiver name in addition to phone number using format:
     * `[13800138000,13800138001]`.
     * @return $this self reference.
     */
    public function setPhoneNumbers($phoneNumbers);

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return $this self reference.
     */
    public function setBody($text);

    /**
     * Sends this sms message.
     * @param SmsInterface $sms the sms that should be used to send this message.
     * If null, the "sms" application component will be used instead.
     * @return bool whether this message is sent successfully.
     */
    public function send(SmsInterface $sms = null);

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString();
}