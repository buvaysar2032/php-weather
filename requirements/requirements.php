<?php

$phpVersionRequired = '8.2.0';
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/* @var $this RequirementChecker */

/**
 * These are the Yii core requirements for the [[YiiRequirementChecker]] instance.
 * These requirements are mandatory for any Yii application.
 */
$intlPhpLink = '<a href="https://www.php.net/manual/en/book.intl.php">Internationalization</a> support';
$yiiLink = '<a href="https://www.yiiframework.com">Yii Framework</a>';
$captchaLink = '<a href="https://www.yiiframework.com/doc/api/2.0/yii-captcha-captcha">Captcha</a>';

return [
    [
        'name' => 'PHP version',
        'mandatory' => true,
        'condition' => version_compare(PHP_VERSION, $phpVersionRequired, '>='),
        'by' => $yiiLink,
        'memo' => "PHP $phpVersionRequired or higher is required.",
    ],
    [
        'name' => 'PHP bit capacity',
        'mandatory' => false,
        'condition' => PHP_INT_MAX !== 2147483647,
        'by' => 'Bitmask support',
        'memo' => '64 bit of PHP recommended for bitmask capacity',
    ],
    [
        'name' => 'Reflection extension',
        'mandatory' => true,
        'condition' => class_exists('Reflection', false),
        'by' => $yiiLink,
    ],
    [
        'name' => 'PCRE extension',
        'mandatory' => true,
        'condition' => extension_loaded('pcre'),
        'by' => $yiiLink,
    ],
    [
        'name' => 'SPL extension',
        'mandatory' => true,
        'condition' => extension_loaded('SPL'),
        'by' => $yiiLink,
    ],
    [
        'name' => 'Ctype extension',
        'mandatory' => true,
        'condition' => extension_loaded('ctype'),
        'by' => $yiiLink
    ],
    [
        'name' => 'MBString extension',
        'mandatory' => true,
        'condition' => extension_loaded('mbstring'),
        'by' => '<a href="https://www.php.net/manual/en/book.mbstring.php">Multibyte string</a> processing',
        'memo' => 'Required for multibyte encoding string processing.'
    ],
    [
        'name' => 'OpenSSL extension',
        'mandatory' => false,
        'condition' => extension_loaded('openssl'),
        'by' => '<a href="https://www.yiiframework.com/doc/api/2.0/yii-base-security">Security Component</a>',
        'memo' => 'Required by encrypt and decrypt methods.'
    ],
    [
        'name' => 'Intl extension',
        'mandatory' => false,
        'condition' => $this->checkPhpExtensionVersion('intl', '1.0.2'),
        'by' => $intlPhpLink,
        'memo' => 'PHP Intl extension 1.0.2 or higher is required when you want to use advanced parameters formatting
        in <code>Yii::t()</code>, non-latin languages with <code>Inflector::slug()</code>,
        <abbr title="Internationalized domain names">IDN</abbr>-feature of
        <code>EmailValidator</code> or <code>UrlValidator</code> or the <code>yii\i18n\Formatter</code> class.'
    ],
    [
        'name' => 'ICU version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '49', '>='),
        'by' => $intlPhpLink,
        'memo' => 'ICU 49.0 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href="https://www.yiiframework.com/doc/api/2.0/yii-i18n-formatter#asRelativeTime%28%29-detail">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU version is ' .
            (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '(ICU is missing)') . '.'
    ],
    [
        'name' => 'ICU Data version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>='),
        'by' => $intlPhpLink,
        'memo' => 'ICU Data 49.1 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href="https://www.yiiframework.com/doc/api/2.0/yii-i18n-formatter#asRelativeTime%28%29-detail">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU Data version is ' .
            (defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '(ICU Data is missing)') . '.'
    ],
    [
        'name' => 'Fileinfo extension',
        'mandatory' => false,
        'condition' => extension_loaded('fileinfo'),
        'by' => '<a href="https://www.php.net/manual/en/book.fileinfo.php">File Information</a>',
        'memo' => 'Required for files upload to detect correct file mime-types.'
    ],
    [
        'name' => 'DOM extension',
        'mandatory' => false,
        'condition' => extension_loaded('dom'),
        'by' => '<a href="https://www.php.net/manual/en/book.dom.php">Document Object Model</a>',
        'memo' => 'Required for REST API to send XML responses via <code>yii\web\XmlResponseFormatter</code>.'
    ],
    [
        'name' => 'IPv6 support',
        'mandatory' => false,
        'condition' => defined('AF_INET6'),
        'by' => 'IPv6 expansion in <a href="https://www.yiiframework.com/doc/api/2.0/yii-validators-ipvalidator">IpValidator</a>',
        'memo' => 'When <a href="https://www.yiiframework.com/doc/api/2.0/yii-validators-ipvalidator#$expandIPv6-detail">IpValidator::expandIPv6</a>
        property is set to <code>true</code>, PHP must support IPv6 protocol stack. Currently PHP constant <code>AF_INET6</code> is not defined
        and IPv6 is probably unsupported.'
    ],
    [
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes'
    ],
    [
        'name' => 'PDO MySQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for MySQL database.'
    ],
    // Cache :
//    [
//        'name' => 'Memcache extension',
//        'mandatory' => false,
//        'condition' => extension_loaded('memcache') || extension_loaded('memcached'),
//        'by' => '<a href="https://www.yiiframework.com/doc/api/2.0/yii-caching-memcache">MemCache</a>',
//        'memo' => extension_loaded('memcached')
//            ? 'To use memcached set <a href="https://www.yiiframework.com/doc/api/2.0/yii-caching-memcache#$useMemcached-detail">MemCache::useMemcached</a> to <code>true</code>.'
//            : ''
//    ],
    [
        'name' => 'APCu extension',
        'mandatory' => false,
        'condition' => extension_loaded('apcu'),
        'by' => '<a href="https://www.yiiframework.com/doc/api/2.0/yii-caching-apccache">ApcCache</a>'
    ],
    // CAPTCHA:
    [
        'name' => 'GD PHP extension with FreeType support',
        'mandatory' => false,
        'condition' => extension_loaded('gd') && !empty(gd_info()['FreeType Support']),
        'by' => $captchaLink,
        'memo' => 'GD extension should be installed with FreeType support in order to be used for image CAPTCHA.'
    ],
    [
        'name' => 'ImageMagick PHP extension with PNG support',
        'mandatory' => false,
        'condition' => extension_loaded('imagick') && in_array('PNG', (new Imagick())::queryFormats('PNG'), true),
        'by' => $captchaLink,
        'memo' => 'Imagick extension should be installed with PNG support in order to be used for image CAPTCHA.'
    ],
    // PHP ini :
    [
        'name' => 'Expose PHP',
        'mandatory' => false,
        'condition' => $this->checkPhpIniOff('expose_php'),
        'by' => 'Security reasons',
        'memo' => '"expose_php" should be disabled at php.ini'
    ],
    [
        'name' => 'PHP allow url include',
        'mandatory' => false,
        'condition' => $this->checkPhpIniOff('allow_url_include'),
        'by' => 'Security reasons',
        'memo' => '"allow_url_include" should be disabled at php.ini'
    ],
    [
        'name' => 'PHP mail SMTP',
        'mandatory' => false,
        'condition' => strlen(ini_get('SMTP')) > 0,
        'by' => 'Email sending',
        'memo' => 'PHP mail SMTP server required'
    ],
];
