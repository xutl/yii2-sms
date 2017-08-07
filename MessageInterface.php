<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

interface MessageInterface
{
    public function getPhoneNumbers();

    public function setPhoneNumbers($phoneNumbers);

    public function setTemplateCode($templateCode);

    public function getTemplateCode();

    public function getTemplateParam();

    public function setTemplateParam($templateParam);

    public function send(SmsInterface $sms = null);

    public function toString();
}