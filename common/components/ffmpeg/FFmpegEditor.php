<?php

namespace common\components\ffmpeg;

use yii\base\Component;

/**
 * Компонент для работы с библиотекой ffmpeg
 *
 * Может автоматически определить, где находится бинарник ffmpeg.
 * По умолчанию представлены методы с разбиением видео на фреймы с возможностью настроить вставку двигающихся элементов
 * по координатным трекинг данным в формате json.
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read bool $isVideoOpened Открыто ли видео в данный момент
 */
class FFmpegEditor extends Component
{
    private const FFMPEG_BINARIES_PARAM = 'ffmpeg.binaries';
    private const FFPROBE_BINARIES_PARAM = 'ffprobe.binaries';

    /**
     * Конфигурация исполняемых файлов ffmpeg
     */
    private ?array $_config;

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        parent::init();
        $prefix = '';
        // Если это DiskStation, то берем актуальный ffmpeg из каталога пакетов
        if ((PHP_OS_FAMILY === 'Linux') && gethostname() === 'DiskStation') {
            $prefix = '/var/packages/ffmpeg/target/bin/';
        }
        $this->_config = [
            self::FFMPEG_BINARIES_PARAM => "{$prefix}ffmpeg",
            self::FFPROBE_BINARIES_PARAM => "{$prefix}ffprobe",
        ];
    }

    /**
     * Вызов бинарника ffmpeg
     *
     * @param array $config Конфигурация ffmpeg
     */
    private function execBinary(array $config = []): void
    {
        $cmd = $this->_config[self::FFMPEG_BINARIES_PARAM] . ' -y'; // Запуск ffmpeg с перезаписью
        foreach ($config as $key => $item) {
            if (!is_int($key)) {
                if (is_array($item)) {
                    foreach ($item as $elem) { // Вывод дублированных ключей
                        $cmd .= "-$key $elem ";
                    }
                } else {
                    $cmd .= "-$key $item ";
                }
            } else {
                $cmd .= "$item ";
            }
        }
        $console = popen($cmd, 'w');
        pclose($console);
    }
}
