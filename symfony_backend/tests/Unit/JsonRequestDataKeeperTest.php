<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\JsonRequestDataKeeper;
use Symfony\Component\HttpFoundation\Request;

class JsonRequestDataKeeperTest extends TestCase
{
    public function testKeepJson(): void
    {
        $key = 'foo';
        $value = 'bar';
        $jsonData = [$key => $value];
        $content = json_encode($jsonData);
        $request = new Request([], [], [], [], [], [], $content);
        $foo = (string)$request->get($key, '');
        $this->assertEmpty($foo);
        $request = JsonRequestDataKeeper::keepJson($request);
        $foo = (string)$request->get($key, '');
        $this->assertEquals($value, $foo);
    }

    public function testKeepJsonIfNoJsonContent(): void
    {
        $key = 'foo';
        $value = 'bar';
        $request = new Request([$key => $value]);
        $foo = (string)$request->get($key, '');
        $this->assertEquals($value, $foo);
        $request = JsonRequestDataKeeper::keepJson($request);
        $foo = (string)$request->get($key, '');
        $this->assertEquals($value, $foo);
    }
}