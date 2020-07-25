<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Elasticsearch\Objects\Indices;
use Baka\Test\Support\ElasticModel\Money;
use PhalconUnitTestCase;

class IndicesTest extends PhalconUnitTestCase
{
    /**
     * Test the creation of a normal index based on a model extending
     * from the Indices class of the package.
     *
     * @return void
     */
    public function testCreateNormalIndex()
    {
        $data = [
            'name' => 'test',
            'url' => 'http://mctekk.com',
            'vehicles' => [
                'id' => 2,
                'date' => '2018-01-02',
                'name' => 'wtf',
            ]
        ];
        $money = new Money(1, $data);
        $indices = Indices::create($money);

        $this->assertArrayHasKey('index', $indices);
        $this->assertTrue((int) $indices['acknowledged'] == 1);
    }

    /**
     * Inset document test normal.
     *
     * @return void
     */
    public function testInsertDocumentToIndex()
    {
        $data = [
            'name' => 'test',
            'url' => 'http://mctekk.com',
            'vehicles' => [
                'id' => 2,
                'date' => '2018-01-02',
                'name' => 'wtf',
            ]
        ];
        $money = new Money(1, $data);
        $moneyElastic = $money->add();

        $this->assertArrayHasKey('result', $moneyElastic);
        $this->assertTrue($moneyElastic['result'] == 'created');
        $this->assertTrue($money->getId() == $moneyElastic['_id']);
    }

    public function testGetById()
    {
        $money = Money::getById(1);

        $this->assertTrue($money->getId() == 1);
    }

    public function testDeletetDocumentToIndex()
    {
        $money = Money::getById(1)->delete();

        $this->assertArrayHasKey('result', $money);
        $this->assertTrue($money['result'] == 'deleted');
        $this->assertTrue($money['_id'] == 1);
    }
}
