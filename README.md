# SMS Extension for Yii 2


[![Latest Stable Version](https://poser.pugx.org/xutl/yii2-sms/v/stable.png)](https://packagist.org/packages/xutl/yii2-sms)
[![Total Downloads](https://poser.pugx.org/xutl/yii2-sms/downloads.png)](https://packagist.org/packages/xutl/yii2-sms)
[![Reference Status](https://www.versioneye.com/php/xutl:yii2-sms/reference_badge.svg)](https://www.versioneye.com/php/xutl:yii2-sms/references)
[![Build Status](https://img.shields.io/travis/xutl/yii2-sms.svg)](http://travis-ci.org/xutl/yii2-sms)
[![Dependency Status](https://www.versioneye.com/php/xutl:yii2-sms/dev-master/badge.png)](https://www.versioneye.com/php/xutl:yii2-sms/dev-master)
[![License](https://poser.pugx.org/xutl/yii2-sms/license.svg)](https://packagist.org/packages/xutl/yii2-sms)


Installation
------------

Next steps will guide you through the process of installing yii2-admin using [composer](http://getcomposer.org/download/). Installation is a quick and easy three-step process.

### Step 1: Install component via composer

Either run

```
composer require --prefer-dist xutl/yii2-sms
```

or add

```json
"xutl/yii2-sms": "~2.0.0"
```

to the `require` section of your composer.json.

### Step 2: Configuring your application

Add following lines to your main configuration file:

```php
'modules' => [
    'sms' => [
        'class' => 'xutl\sms\clients\Aliyun',
        //etc 
    ],
],
```

### Step 3: Configuring your sms template

继承 `xutl\sms\SendJob` 实现你的短信发送个，在SendJob子类中设定模板什么的。发送直接推给队列了。由队列负责发送。


## License

This is released under the MIT License. See the bundled [LICENSE.md](LICENSE.md)
for details.