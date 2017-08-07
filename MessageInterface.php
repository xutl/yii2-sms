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
    public function getTo();

    /**
     * Sets the message recipient(s).
     * @param string|array $to receiver phone numbers.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to phone number using format:
     * `[13800138000,13800138001]`.
     * @return $this self reference.
     */
    public function setTo($to);

    /**
     * Sends this email message.
     * @param SmserInterface $smser the smser that should be used to send this message.
     * If null, the "sms" application component will be used instead.
     * @return bool whether this message is sent successfully.
     */
    public function send(SmserInterface $smser = null);

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString();
}