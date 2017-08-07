<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\sms;

use Yii;
use yii\di\Instance;
use yii\base\Component;
use yii\helpers\Inflector;
use yii\httpclient\Client;
use yii\helpers\StringHelper;
use yii\base\InvalidConfigException;

/**
 * Class BaseClient
 * @package xutl\sms
 */
abstract class BaseSms extends Component implements SmserInterface
{
    /**
     * @event SmsEvent an event raised right before send.
     * You may set [[SmsEvent::isValid]] to be false to cancel the send.
     */
    const EVENT_BEFORE_SEND = 'beforeSend';

    /**
     * @event SmsEvent an event raised right after send.
     */
    const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @var bool 存储到文件中
     */
    public $useFileTransport = false;

    /**
     * @var string the directory where the sms messages are saved when [[useFileTransport]] is true.
     */
    public $fileTransportPath = '@runtime/sms';

    /**
     * @var callable a PHP callback that will be called by [[send()]] when [[useFileTransport]] is true.
     * The callback should return a file name which will be used to save the email message.
     * If not set, the file name will be generated based on the current timestamp.
     *
     * The signature of the callback is:
     *
     * ```php
     * function ($sms, $message)
     * ```
     */
    public $fileTransportCallback;

    /**
     * Sends the given sms message.
     * This method will log a message about the sms being sent.
     * If [[useFileTransport]] is true, it will save the sms as a file under [[fileTransportPath]].
     * Otherwise, it will call [[sendMessage()]] to send the sms to its recipient(s).
     * Child classes should implement [[sendMessage()]] with the actual sms sending logic.
     * @param MessageInterface $message sms message instance to be sent
     * @return bool whether the message has been sent successfully
     */
    public function send($message)
    {
        if (!$this->beforeSend($message)) {
            return false;
        }

        $phone = $message->getTo();
        if (is_array($phone)) {
            $phone = implode(', ', $phone);
        }
        Yii::info('Sending email "' . $message->getBody() . '" to "' . $phone . '"', __METHOD__);

        if ($this->useFileTransport) {
            $isSuccessful = $this->saveMessage($message);
        } else {
            $isSuccessful = $this->sendMessage($message);
        }
        $this->afterSend($message, $isSuccessful);
        return $isSuccessful;
    }

    /**
     * Sends multiple messages at once.
     *
     * The default implementation simply calls [[send()]] multiple times.
     * Child classes may override this method to implement more efficient way of
     * sending multiple messages.
     *
     * @param array $messages list of sms messages, which should be sent.
     * @return int number of messages that are successfully sent.
     */
    public function sendMultiple(array $messages)
    {
        $successCount = 0;
        foreach ($messages as $message) {
            if ($this->send($message)) {
                $successCount++;
            }
        }
        return $successCount;
    }

    /**
     * @return string the file name for saving the message when [[useFileTransport]] is true.
     */
    public function generateMessageFileName()
    {
        $time = microtime(true);

        return date('Ymd-His-', $time) . sprintf('%04d', (int) (($time - (int) $time) * 10000)) . '-' . sprintf('%04d', mt_rand(0, 10000)) . '.sms';
    }

    /**
     * This method is invoked right before sms send.
     * You may override this method to do last-minute preparation for the message.
     * If you override this method, please make sure you call the parent implementation first.
     * @param MessageInterface $message
     * @return bool whether to continue sending an sms.
     */
    public function beforeSend($message)
    {
        $event = new SmsEvent(['message' => $message]);
        $this->trigger(self::EVENT_BEFORE_SEND, $event);

        return $event->isValid;
    }

    /**
     * This method is invoked right after sms was send.
     * You may override this method to do some postprocessing or logging based on mail send status.
     * If you override this method, please make sure you call the parent implementation first.
     * @param MessageInterface $message
     * @param bool $isSuccessful
     */
    public function afterSend($message, $isSuccessful)
    {
        $event = new SmsEvent(['message' => $message, 'isSuccessful' => $isSuccessful]);
        $this->trigger(self::EVENT_AFTER_SEND, $event);
    }
}