<?php

/*
 * CKFinder Configuration File
 *
 * For the official documentation visit https://ckeditor.com/docs/ckfinder/ckfinder3-php/
 */

/*============================ PHP Error Reporting ====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/debugging.html

// Production
use common\components\Environment;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
/** @var yii\web\Application $yiiApp */
$yiiApp = require 'auth.php';

// Development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/*============================ General Settings =======================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html

$config = [];

/*============================ Enable PHP Connector HERE ==============================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_authentication

$config['authentication'] = static fn() => !$yiiApp->user->getIsGuest();

/*============================ License Key ============================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_licenseKey

$config['licenseName'] = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
try {
    require 'keygen.php';
    $sessionCache = getCKEditorSessionKey($config['licenseName']);
    if (!$key = $yiiApp->session->get($sessionCache) ?: $yiiApp->cache->get($sessionCache)) {
        $key = generateLicenseKey(2, $config['licenseName']);
    }
    $yiiApp->session->set($sessionCache, $key);
    $yiiApp->cache->set($sessionCache, $key);
    $config['licenseKey'] = $key;
} catch (Exception $e) {
    $config['licenseKey'] = '*R?T-*1**-W**E-*W**-*T**-3*G*-9**B';
}

/*============================ CKFinder Internal Directory ============================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_privateDir

$config['privateDir'] = [
    'backend' => 'default',
    'tags' => '.ckfinder/tags',
    'logs' => '.ckfinder/logs',
    'cache' => '.ckfinder/cache',
    'thumbs' => '.ckfinder/cache/thumbs',
];

/*============================ Images and Thumbnails ==================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_images

$config['images'] = [
    'maxWidth' => 4096,
    'maxHeight' => 3072,
    'quality' => 80,
    'sizes' => [
        'small' => ['width' => 480, 'height' => 320, 'quality' => 80],
        'medium' => ['width' => 600, 'height' => 480, 'quality' => 80],
        'large' => ['width' => 800, 'height' => 600, 'quality' => 80]
    ]
];

/*=================================== Backends ========================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_backends

$config['backends'] = [
    [
        'name' => 'default',
        'adapter' => 'local',
        'baseUrl' => '/uploads/', // Относительные ссылки
        'root' => dirname(__DIR__) . '/uploads/',
        'chmodFiles' => 0777,
        'chmodFolders' => 0755,
        'filesystemEncoding' => 'UTF-8',
    ],
    [
        'name' => 's3',
        'adapter' => 's3',
        'bucket' => Environment::readEnv('S3_BUCKET'),
        'region' => Environment::readEnv('S3_REGION'),
        'key' => Environment::readEnv('S3_KEY'),
        'secret' => Environment::readEnv('S3_SECRET'),
        'endpoint' => Environment::readEnv('S3_ENDPOINT'),
        'baseUrl' => Environment::readEnv('S3_BASEURL')
            ?: (Environment::readEnv('S3_ENDPOINT') . '/' . Environment::readEnv('S3_BUCKET')),
        'use_path_style_endpoint' => true
    ]
];

/*================================ Resource Types =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_resourceTypes

$config['defaultResourceTypes'] = '';
if (Environment::readEnv('S3_BUCKET')
    && Environment::readEnv('S3_REGION')
    && Environment::readEnv('S3_KEY')
    && Environment::readEnv('S3_SECRET')
    && Environment::readEnv('S3_ENDPOINT')) {
    $backend = 's3';
} else {
    $backend = 'default';
}
$config['resourceTypes'] = [
    [
        'name' => 'Files',
        'directory' => 'files',
        'maxSize' => 0,
        'allowedExtensions' => '7z,aiff,asf,bmp,csv,doc,docx,fla,flv,gz,gzip,mid,ods,odt,pdf,ppt,pptx,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,swf,sxc,sxw,tar,tgz,txt,vsd,xls,xlsx,zip',
        'deniedExtensions' => '',
        'backend' => $backend
    ],
    [
        'name' => 'Images',
        'directory' => 'images',
        'maxSize' => 0,
        'allowedExtensions' => 'bmp,gif,jpeg,jpg,png,svg,tif,tiff,webp',
        'deniedExtensions' => '',
        'backend' => $backend
    ],
    [
        'name' => 'Audio',
        'directory' => 'audio',
        'maxSize' => 0,
        'allowedExtensions' => 'mpc,mp3,wav,wma,weba',
        'deniedExtensions' => '',
        'backend' => $backend
    ],
    [
        'name' => 'Video',
        'directory' => 'video',
        'maxSize' => 0,
        'allowedExtensions' => 'avi,mov,m4a,mp4,mpeg,mpg,webm,wmv',
        'deniedExtensions' => '',
        'backend' => $backend
    ]
];

/*================================ Access Control =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_roleSessionVar

$config['roleSessionVar'] = 'CKFinder_UserRole';

// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_accessControl
$config['accessControl'][] = [
    'role' => '*',
    'resourceType' => '*',
    'folder' => '/',

    'FOLDER_VIEW' => true,
    'FOLDER_CREATE' => true,
    'FOLDER_RENAME' => true,
    'FOLDER_DELETE' => true,

    'FILE_VIEW' => true,
    'FILE_CREATE' => true,
    'FILE_RENAME' => true,
    'FILE_DELETE' => true,

    'IMAGE_RESIZE' => true,
    'IMAGE_RESIZE_CUSTOM' => true
];

/*================================ Other Settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html

$config['overwriteOnUpload'] = false;
$config['checkDoubleExtension'] = true;
$config['disallowUnsafeCharacters'] = false;
$config['secureImageUploads'] = true;
$config['checkSizeAfterScaling'] = true;
$config['htmlExtensions'] = ['html', 'htm', 'xml', 'js', 'svg', 'php', 'pug'];
$config['hideFolders'] = ['.*', 'CVS', '__thumbs'];
$config['hideFiles'] = ['.*'];
$config['forceAscii'] = false;
$config['xSendfile'] = false;

// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_debug
$config['debug'] = false;

/*==================================== Plugins ========================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_plugins

$config['pluginsDirectory'] = __DIR__ . '/plugins';
$config['plugins'] = [];

/*================================ Cache settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_cache

$config['cache'] = [
    'imagePreview' => 24 * 3600,
    'thumbnails' => 24 * 3600 * 365,
    'proxyCommand' => 0
];

/*============================ Temp Directory settings ================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_tempDirectory

$config['tempDirectory'] = sys_get_temp_dir();

/*============================ Session Cause Performance Issues =======================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_sessionWriteClose

$config['sessionWriteClose'] = true;

/*================================= CSRF protection ===================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_csrfProtection

$config['csrfProtection'] = true;

/*===================================== Headers =======================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_headers

$config['headers'] = [];

/*============================== End of Configuration =================================*/

// Config must be returned - do not change it.
return $config;
