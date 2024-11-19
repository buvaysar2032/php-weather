<?php

namespace admin\components\consoleRunner;

use Yii;
use yii\base\{Component, InvalidConfigException};

/**
 * ConsoleRunner - a component for running console commands in background.
 *
 * Usage:
 * ```php
 * ...
 * $cr = new ConsoleRunner([
 *     'file' => '@my/path/to/yii',
 *     'phpBinaryPath' => '/my/path/to/php', // This is an optional param you may use to override the default `php` binary path.
 * ]);
 * $cr->run('controller/action param1 param2 ...');
 * ...
 * ```
 * or use it like an application component:
 * ```php
 * // config.php
 * ...
 * components [
 *     'consoleRunner' => [
 *         'class' => 'vova07\console\ConsoleRunner',
 *         'file' => '@my/path/to/yii', // Or an absolute path to console file.
 *         'phpBinaryPath' => '/my/path/to/php', // This is an optional param you may use to override the default `php` binary path.
 *     ]
 * ]
 * ...
 *
 * // some-file.php
 * Yii::$app->consoleRunner->run('controller/action param1 param2 ...');
 * ```
 *
 * @package admin\components\consoleRunner
 */
class ConsoleRunner extends Component
{
    /**
     * Console application file that will be executed.
     *
     * Usually it can be `yii` file.
     */
    public ?string $file;

    /**
     * The PHP binary path.
     */
    public string $phpBinaryPath = PHP_BINARY;

    /**
     * @throws InvalidConfigException
     */
    final public function init(): void
    {
        parent::init();

        if ($this->file === null) {
            throw new InvalidConfigException('The "file" property must be set.');
        }
        $this->file = Yii::getAlias($this->file);
    }

    /**
     * Running console command on background.
     *
     * @param string $cmd Argument that will be passed to console application.
     */
    final public function run(string $cmd): bool
    {
        $cmd = "$this->phpBinaryPath $this->file $cmd";
        $cmd = $this->isWindows() === true
            ? "start /b $cmd"
            : "$cmd > /dev/null 2>&1 &";

        pclose(popen($cmd, 'r'));

        return true;
    }

    /**
     * Check operating system.
     *
     * @return bool `true` if it's Windows OS.
     */
    private function isWindows(): bool
    {
        return PHP_OS === 'WINNT' || PHP_OS === 'WIN32';
    }
}
