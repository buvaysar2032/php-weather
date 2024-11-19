<?php

namespace common\components;

use common\components\exceptions\CryptorRuntimeException;
use yii\base\InvalidConfigException;

/**
 * Class Cryptor
 *
 * Позволяет шифровать и дешифровать строки с данными
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Cryptor
{
    /**
     * Метод шифрования
     *
     * @see https://www.php.net/manual/ru/function.openssl-get-cipher-methods.php
     */
    private string|bool $method = 'aes-128-ctr';

    /**
     * Уникальный ключ
     */
    private string|bool|null $key;

    /**
     * Cryptor constructor.
     *
     * @throws InvalidConfigException
     */
    public function __construct(string $key = null, string $method = null)
    {
        if (!$key) {
            $key = php_uname(); // default encryption key if none supplied
        }
        if (ctype_print($key)) {
            // convert ASCII keys to binary format
            $this->key = openssl_digest($key, 'SHA256', true);
        } else {
            $this->key = $key;
        }
        if ($method) {
            if (in_array(strtolower($method), openssl_get_cipher_methods(), true)) {
                $this->method = $method;
            } else {
                throw new InvalidConfigException(__METHOD__ . ': unrecognised cipher method: ' . $method);
            }
        }
    }

    /**
     * Зашифровать строку
     *
     * @param string $data Исходная строка
     *
     * @return string Зашифрованная строка
     *
     * @throws CryptorRuntimeException
     */
    final public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes($this->iv_bytes(), $strongResult);
        if (!$iv || !$strongResult) {
            throw new CryptorRuntimeException('openssl_random_pseudo_bytes() IV generation failed');
        }
        return bin2hex($iv) . openssl_encrypt($data, $this->method, $this->key, 0, $iv);
    }

    private function iv_bytes(): bool|int
    {
        return openssl_cipher_iv_length($this->method);
    }

    /**
     * Расшифровать строку
     *
     * @param string $data Зашифрованная строка
     *
     * @return false|string Исходная строка или false при неудаче
     */
    final public function decrypt(string $data): bool|string
    {
        $iv_strlen = 2 * $this->iv_bytes();
        if (preg_match('/^(.{' . $iv_strlen . '})(.+)$/', $data, $regs)) {
            [, $iv, $crypted_string] = $regs;
            if (ctype_xdigit($iv) && strlen($iv) % 2 === 0) {
                return openssl_decrypt($crypted_string, $this->method, $this->key, 0, hex2bin($iv));
            }
        }
        return false; // failed to decrypt
    }
}
