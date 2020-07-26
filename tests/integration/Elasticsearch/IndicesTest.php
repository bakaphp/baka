<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Elasticsearch\Objects\Indices;
use Baka\Test\Support\ElasticModel\Vehicle;
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
            'name' => $this->faker->name,
            'url' => 'http://mctekk.com',
            'photos' => [
                'name' => $this->faker->name,
                'url' => '3234',
                'vehicles' => [
                'id' => 2,
                'date' => '2018-01-02',
                'name' => 'wtf',
                ]
            ]
        ];

        $vehicle = new Vehicle(1, $data);
        $indices = Indices::create($vehicle);

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
            'name' => $this->faker->name,
            'url' => 'http://mctekk.com',
            'photos' => [
                'name' => $this->faker->name,
                'url' => '3234',
                'vehicles' => [
                'id' => 2,
                'date' => '2018-01-02',
                'name' => $this->faker->name,
                ]
            ]
        ];
        $vehicle = new Vehicle(1, $data);
        $vehicleElastic = $vehicle->add();

        $this->assertArrayHasKey('result', $vehicleElastic);
        $this->assertTrue($vehicleElastic['result'] == 'created');
        $this->assertTrue($vehicle->getId() == $vehicleElastic['_id']);
    }

    public function testGetById()
    {
        $vehicle = Vehicle::getById(1);

        $this->assertTrue($vehicle->getId() == 1);
    }

    public function testDeleteDocumentToIndex()
    {
        $vehicle = Vehicle::getById(1)->delete();

        $this->assertArrayHasKey('result', $vehicle);
        $this->assertTrue($vehicle['result'] == 'deleted');
        $this->assertTrue($vehicle['_id'] == 1);
    }
}
