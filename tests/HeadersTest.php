<?php

use PHPUnit\Framework\TestCase;
use SoftanConnect\Headers;

final class HeadersTest extends TestCase
{
    public function testBuildValidationHeaders(): void
    {
        $apiKey = 'k123';
        $info   = 'i456';
        $sdk    = 's789';

        $h = Headers::buildValidationHeaders($apiKey, $info, $sdk);
        $this->assertSame('application/json', $h['Content-Type'] ?? null);
        $this->assertSame('k123', $h['X-API-KEY'] ?? null);
        $this->assertSame('i456', $h['X-Connect-Info'] ?? null);
        $this->assertSame('s789', $h['X-Connect-SDK'] ?? null);
    }

    public function testBuildRuntimeHeadersWithEnvCfg(): void
    {
        $envCfg = [
            'api_key' => 'abc',
            'connect_info' => 'def',
            'connect_sdk' => 'ghi',
        ];

        $h = Headers::buildRuntimeHeaders($envCfg);
        $this->assertSame('application/json', $h['Content-Type'] ?? null);
        $this->assertSame('abc', $h['X-API-KEY'] ?? null);
        $this->assertSame('def', $h['X-Connect-Info'] ?? null);
        $this->assertSame('ghi', $h['X-Connect-SDK'] ?? null);
    }
}

