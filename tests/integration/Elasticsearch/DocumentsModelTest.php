<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Contracts\Http\Api\CrudElasticBehaviorTrait;
use Baka\Contracts\Http\Api\ResponseTrait;
use Baka\Elasticsearch\Objects\Indices;
use Baka\Http\QueryParser\QueryParser;
use Baka\Test\Support\ElasticModel\Vehicle;
use PhalconUnitTestCase;

class DocumentsModelTest extends PhalconUnitTestCase
{
    use CrudElasticBehaviorTrait;
    use ResponseTrait;

    public function testCreateIndexByDocument()
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
                    'model' => [
                        'id' => $this->faker->randomDigit,
                        'name' => $this->faker->name,
                    ]
                ]
            ],
            'rooftop' => [
                'id' => $this->faker->randomDigit,
                'name' => $this->faker->name,
                'description' => $this->faker->sentence(),
                'category' => [
                    'id' => $this->faker->randomDigit,
                    'name' => $this->faker->name,
                    'parent' => [
                        'id' => $this->faker->randomDigit,
                        'name' => $this->faker->name,
                    ]
                ]
            ],
            'selling_price' => [
                'price' => [
                    'value' => $this->faker->randomNumber
                ],
            ],
            'issues' => [
                'key' => 'not_vin_decode'
            ]
        ];
        $vehicle = new Vehicle();
        $vehicle->setData(rand(100, 1000), $data);
        if (!Indices::exist($vehicle->getIndices())) {
            $indices = Indices::create($vehicle);
            $vehicle->add();
            $this->assertArrayHasKey('index', $indices);
            $this->assertTrue((int) $indices['acknowledged'] == 1);
        }
        $vehicle->add();
    }

    public function testIndexWithAdditional()
    {
        sleep(3);
        $vehicle = new Vehicle();
        $this->model = $vehicle;

        $limit = 100;
        $params = [];
        $params['q'] = '(issues.key:not_vin_decode)';
        $params['limit'] = $limit;
        $params['page'] = '1';

        $parse = new QueryParser($vehicle, $params);

        //convert to SQL
        $processedRequest = [
            'sql' => $parse
        ];

        $results = $this->processOutput(
            $this->getRecords($processedRequest)
        );

        foreach ($results['results'] as $result) {
            $this->assertTrue($result instanceof Vehicle);
        }

        $this->assertTrue($results['total'] > 0);
    }
}
