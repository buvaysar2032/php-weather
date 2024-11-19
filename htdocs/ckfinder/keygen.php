<?php

function random(): float
{
    return (float)mt_rand() / (float)mt_getrandmax();
}

/**
 * @throws JsonException
 */
function getUTF16CodeUnits(string $string): array
{
    $string = substr(json_encode($string, JSON_THROW_ON_ERROR), 1, -1);
    preg_match_all("/\\\\u[0-9a-fA-F]{4}|./mi", $string, $matches);
    return $matches[0];
}

/**
 * @throws JsonException
 */
function JS_charCodeAt(string $string, int $index): float|int
{
    $utf16CodeUnits = getUTF16CodeUnits($string);
    $unit = $utf16CodeUnits[$index];

    if (strlen($unit) > 1) {
        $hex = substr($unit, 2);
        return hexdec($hex);
    }

    return ord($unit);
}

function lc(string $c, bool $useLicenseName): string
{
    $map = [1, 8, 17, 22, 3, 13, 11, 20, 5, 24, 27];
    $key = str_split(str_replace('-', '', '*???-*?**-?**?-*?**-*?**-?*?*-?**?'));
    if ($useLicenseName) {
        $lc2Map = [1, 2, 6, 7, 11, 12, 16, 17, 21, 22, 26, 27, 31, 32];
        $lc2Pos = floor(count($lc2Map) * random());
        $key[2] = str_split('123456789ABCDEFGHJKLMNPQRSTUVWXYZ')[$lc2Pos];
    }
    for ($i = 0, $iMax = count($map); $i < $iMax; ++$i) {
        $key[$map[$i]] = $c[$i];
    }
    $result = [];
    for ($i = 0, $iMax = count($key); $i < $iMax; $i += 4) {
        $result_part = '';
        for ($j = $i; $j < $i + 4 && $j < $iMax; ++$j) {
            $result_part .= $key[$j];
        }
        $result[] = $result_part;
    }
    return implode('-', $result);
}

function c(array $f): string
{
    $map = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $result = '';
    foreach ($f as $iValue) {
        $result .= $map[(int)$iValue];
    }
    return $result;
}

/**
 * @throws JsonException
 */
function f10(string $c): float|int
{
    $tmp = 0;
    for ($i = 0; $i < 10; ++$i) {
        $tmp += JS_charCodeAt($c, $i);
    }
    while ($tmp > 33) {
        $tmp1 = str_split((string)$tmp);
        $tmp = 0;
        foreach ($tmp1 as $iValue) {
            $tmp += (int)$iValue;
        }
    }
    return $tmp;
}

function f4(float $f0, int $licenseType): int
{
    return ($f0 + $licenseType) % 33;
}

/**
 * @throws JsonException
 */
function f7(string $licenseName): int
{
    $tmp = 0;
    for ($i = 0, $iMax = strlen($licenseName); $i < $iMax; ++$i) {
        $tmp += JS_charCodeAt($licenseName, $i);
    }
    return $tmp % 100 % 33;
}

function f5(float $f1, float|int $arg1): int
{
    $arg1 = $arg1 ? 1 : 0;
    return ($f1 + $arg1) % 33;
}

function f8f9(float $f0, float $f1, float $f2, float $f3): ?array
{
    $c = 33 + ($f0 * $f3 - $f1 * $f2) % 33;
    $u = 0;
    for ($i = 0; $i < 33; ++$i) {
        if (1 === $c * $i % 33) {
            $u = $i;
        }
    }
    $_f1 = 33 - $f1;
    $_f2 = 33 - $f2;
    for ($f8 = 0; $f8 < 33; ++$f8) {
        for ($f9 = 0; $f9 < 33; ++$f9) {
            if (12 * (($u * $f3 % 33 * $f8 + $u * $_f1 % 33 * $f9) % 33) + ($u * $_f2 % 33 * $f8 + $u * $f0 % 33 * $f9) % 33 >= 211) {
                return [$f8, $f9];
            }
        }
    }
    return null;
}

function rand33(): float|bool
{
    return floor(33 * random());
}

/**
 * @throws JsonException
 */
function generateLicenseKey(int $licenseType, bool|string $licenseName): string
{
    $licenseType = ($licenseType < 0 || $licenseType > 3) ? 2 : $licenseType;
    $licenseName = $licenseName || '';
    $f = [];
    $i = 0;
    do {
        $f[0] = rand33();
        $f[1] = rand33();
        $f[2] = rand33();
        $f[3] = rand33();
        $f8f9Pair = f8f9($f[0], $f[1], $f[2], $f[3]);
        ++$i;
        if ($i > 1000) {
            throw new RuntimeException(
                'Generate license key error, there may be some problem with the random number generator of your computer.'
            );
        }
    } while (!$f8f9Pair);
    $f[4] = f4($f[0], $licenseType);
    $f[5] = f5($f[1], floor(2 * random()));
    $f[6] = rand33();
    $f[7] = f7($licenseName);
    $f[8] = $f8f9Pair[0];
    $f[9] = $f8f9Pair[1];
    $f[10] = f10(c($f));
    return lc(c($f), ($licenseType !== 2));
}

function getCKEditorSessionKey(string $licenseName): string
{
    return'__CK_Editor_Key_' . $licenseName .
    (array_key_exists('HTTP_REFERER', $_SERVER) && str_contains($_SERVER['HTTP_REFERER'], '/admin/')
        ? '_admin' : '_frontend');
}