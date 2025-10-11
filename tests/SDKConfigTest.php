<?php

use PHPUnit\Framework\TestCase;
use SoftanConnect\SDK;

final class SDKConfigTest extends TestCase
{
    public function testLoadJsonMissingAndInvalid(): void
    {
        $this->assertSame([], SDK::loadJson(__DIR__ . '/_does_not_exist.json'));

        $tmp = tempnam(sys_get_temp_dir(), 'sdk');
        file_put_contents($tmp, '{invalid json');
        $this->assertSame([], SDK::loadJson($tmp));
        @unlink($tmp);
    }

    public function testSaveAndLoadJsonRoundtrip(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'sdk');
        $arr = ['a' => 1, 'b' => ['c' => true]];
        $ok = SDK::saveJson($tmp, $arr);
        $this->assertTrue($ok);
        $loaded = SDK::loadJson($tmp);
        $this->assertSame($arr, $loaded);
        @unlink($tmp);
    }
}

