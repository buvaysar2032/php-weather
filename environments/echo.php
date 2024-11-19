<?php
/**
 * Prints error message.
 * @param string $message message
 */
function printError(string $message)
{
    echo "\n  " . formatMessage("Error. $message", ['fg-red']) . " \n";
}

/**
 * Returns true if the stream supports colorization. ANSI colors are disabled if not supported by the stream.
 *
 * - windows without ansicon
 * - not tty consoles
 *
 * @return bool true if the stream supports ANSI colors, otherwise false.
 */
function ansiColorsSupported(): bool
{
    return DIRECTORY_SEPARATOR === '\\' || (function_exists('posix_isatty') && @posix_isatty(STDOUT));
}

/**
 * Get ANSI code of style.
 * @param string $name style name
 * @return int ANSI code of style.
 */
function getStyleCode(string $name): int
{
    $styles = [
        'bold' => 1,
        'fg-black' => 30, 'fg-red' => 31, 'fg-green' => 32, 'fg-yellow' => 33, 'fg-blue' => 34, 'fg-magenta' => 35, 'fg-cyan' => 36, 'fg-white' => 37,
        'bg-black' => 40, 'bg-red' => 41, 'bg-green' => 42, 'bg-yellow' => 43, 'bg-blue' => 44, 'bg-magenta' => 45, 'bg-cyan' => 46, 'bg-white' => 47,
    ];
    return $styles[$name];
}

/**
 * Formats message using styles if STDOUT supports it.
 * @param string $message message
 * @param string[] $styles styles
 * @return string formatted message.
 */
function formatMessage(string $message, array $styles): string
{
    if (empty($styles) || !ansiColorsSupported()) {
        return $message;
    }

    return sprintf("\x1b[%sm", implode(';', array_map('getStyleCode', $styles))) . $message . "\x1b[0m";
}