<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Metadata;

use Baka\Test\Support\Contracts\Metadata\TestMetadataObject;
use PhalconUnitTestCase;

class MetadataTest extends PhalconUnitTestCase
{
    public function testGetMetadataString()
    {
        $testObject = new TestMetadataObject();
        $testObject->setMetadata('test-key', 'test');

        $value = $testObject->getMetadata('test-key');

        $this->assertEquals('test', $value);
    }

    public function testGetMetadataInt()
    {
        $testObject = new TestMetadataObject();

        $testObject->setMetadata('test-key-int', 1234);
        $value = $testObject->getMetadata('test-key-int');

        $this->assertEquals(1234, $value);
    }
}
