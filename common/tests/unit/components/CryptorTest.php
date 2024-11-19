<?php

namespace common\tests\unit\components;

use Codeception\Test\Unit;
use common\components\Cryptor;

/**
 * Class CryptorTest
 *
 * @package common\tests\unit\components
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class CryptorTest extends Unit
{
    public function testEncrypt(): void
    {
        $cryptor = new Cryptor('some_randomly_generated_string');
        $data = 'Text to encrypt';
        $encrypted = $cryptor->encrypt($data);
        expect($encrypted)->notToBe($data, 'String is encoded');
    }

    public function testDecrypt(): void
    {
        $cryptor = new Cryptor('some_randomly_generated_string');
        $data = 'Secret text to decrypt';
        $encrypted = $cryptor->encrypt($data);
        $decrypted = $cryptor->decrypt($encrypted);
        expect($decrypted)->toBe($data, 'String is decoded');
    }
}