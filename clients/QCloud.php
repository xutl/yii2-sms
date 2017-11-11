<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms\clients;

use xutl\sms\BaseClient;

/**
 * Class QCloud
 * @package xutl\sms\clients
 */
class QCloud extends BaseClient
{

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 手机号
     * @param string $content 内容
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendMessage($phoneNumbers, $content, $signName = null, $outId = null)
    {
        // TODO: Implement sendMessage() method.
    }

    /**
     * 发送模板短信
     * @param string|array $phoneNumbers 手机号
     * @param string $template 模板
     * @param array $templateParam 模板参数
     * @param string $signName 签名
     * @param string $outId 外部流水扩展字段
     * @return mixed
     */
    protected function sendTemplateMessage($phoneNumbers, $template, array $templateParam = [], $signName = null, $outId = null)
    {
        // TODO: Implement sendTemplateMessage() method.
    }
}