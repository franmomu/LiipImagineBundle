<?php

namespace Liip\ImagineBundle\Tests\Async;

use Enqueue\Bundle\EnqueueBundle;
use Liip\ImagineBundle\Async\CacheResolved;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Async\CacheResolved
 */
class CacheResolvedTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!class_exists(EnqueueBundle::class)) {
            self::markTestSkipped('The tests are run without enqueue integration. Skip them');
        }
    }

    public function testCouldBeJsonSerialized()
    {
        $message = new CacheResolved('thePath', array(
            'fooFilter' => 'http://example.com/fooFilter/thePath',
            'barFilter' => 'http://example.com/barFilter/thePath',
        ));

        $this->assertEquals(
            '{"path":"thePath","uris":{"fooFilter":"http:\/\/example.com\/fooFilter\/thePath","barFilter":"http:\/\/example.com\/barFilter\/thePath"}}',
            json_encode($message)
        );
    }

    public function testCouldBeJsonDeSerialized()
    {
        $message = CacheResolved::jsonDeserialize('{"path":"thePath","uris":{"fooFilter":"http:\/\/example.com\/fooFilter\/thePath","barFilter":"http:\/\/example.com\/barFilter\/thePath"}}');

        $this->assertInstanceOf('Liip\ImagineBundle\Async\CacheResolved', $message);
        $this->assertEquals('thePath', $message->getPath());
        $this->assertEquals(array(
            'fooFilter' => 'http://example.com/fooFilter/thePath',
            'barFilter' => 'http://example.com/barFilter/thePath',
        ), $message->getUris());
    }
}
