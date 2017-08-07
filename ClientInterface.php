<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

interface ClientInterface
{
    /**
     * 发送短信
     * @param array|string $phoneNumbers
     * @param string $content
     * @param null $signName
     * @param null $outId
     * @return array
     */
    public function send($phoneNumbers, $content, $signName = null, $outId = null);

    /**
     * 发送模板短信
     * @param array|string $phoneNumbers
     * @param string $templateCode
     * @param array $templateParams
     * @param null $signName
     * @param null $outId
     * @return array
     */
    public function sendTemplate($phoneNumbers, $templateCode, array $templateParams = [], $signName = null, $outId = null);
}