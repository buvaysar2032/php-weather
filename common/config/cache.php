<?php

use common\components\Environment;
use yii\caching\{ApcCache, FileCache, MemCache};
use yii\redis\Cache;

if (!empty(Environment::readEnv('MEMCACHED_HOST')) && !empty(Environment::readEnv('MEMCACHED_PORT'))) {
    $cache = [
        'class' => MemCache::class,
        'useMemcached' => true,
        'servers' => [
            [
                'host' => Environment::readEnv('MEMCACHED_HOST'),
                'port' => (int)Environment::readEnv('MEMCACHED_PORT'),
                'weight' => 100
            ]
        ]
    ];
}
if (!isset($cache)) {
    if (!empty(Environment::readEnv('REDIS_HOSTNAME')) && !empty(Environment::readEnv('REDIS_PORT'))) {
        $cache = ['class' => Cache::class];
    } elseif (extension_loaded('apcu')) {
        $cache = ['class' => ApcCache::class, 'useApcu' => true];
    } else {
        $cache = ['class' => FileCache::class];
    }
}
return $cache;
