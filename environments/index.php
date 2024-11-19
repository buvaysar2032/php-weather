<?php

/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
$setWritable = [
    'admin/assets/source/styles',
    'admin/runtime',
    'admin/widgets/ckfinder/assets/css',
    'api/runtime',
    'common/assets/source/styles',
    'common/modules/mail/templates',
    'common/runtime',
    'htdocs/admin/assets',
    'htdocs/api/assets',
    'htdocs/uploads'
];
$setCookieValidationKey = [
    'admin/config/main-local.php',
    'api/config/main-local.php'
];
return [
    'Peppers Development' => [
        'path' => 'peppers_dev',
        'setWritable' => $setWritable,
        'setExecutable' => ['yii'],
        'setCookieValidationKey' => array_merge($setCookieValidationKey, ['common/config/codeception-local.php'])
    ],
    'Stage Development' => [
        'path' => 'stage_dev',
        'setWritable' => $setWritable,
        'setExecutable' => ['yii'],
        'setCookieValidationKey' => $setCookieValidationKey
    ],
    'Stage Production' => [
        'path' => 'stage_prod',
        'setWritable' => $setWritable,
        'setExecutable' => ['yii'],
        'setCookieValidationKey' => $setCookieValidationKey
    ]
];
