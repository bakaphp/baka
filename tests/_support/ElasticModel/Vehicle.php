<?php

namespace Baka\Test\Support\ElasticModel;

use Baka\Elasticsearch\Objects\Documents;

class Vehicle extends Documents
{
    /**
     * initialize.
     *
     * @return void
     */
    public function initialize() : void
    {
        $this->setIndices('vehicles');
        $this->addRelation('photos', ['alias' => 'photos', 'elasticAlias' => 'ph', 'elasticIndex' => 1]);
        $this->addRelation('rooftop', ['alias' => 'rooftop', 'elasticAlias' => 'rp', 'elasticIndex' => 1]);
        $this->addRelation('selling_price', ['alias' => 'selling_price', 'elasticAlias' => 'sp', 'elasticIndex' => 1]);
        $this->addRelation('issues', ['alias' => 'issues', 'elasticAlias' => 'iss', 'elasticIndex' => 1]);
    }

    /**
     * Define the structure of this index.
     *
     * @return array
     */
    public function structure() : array
    {
        return [
            'id' => $this->integer,
            'name' => $this->text,
            'description' => $this->text,
            'date' => $this->dateNormal,
            'money' => $this->decimal,
            'anotherMoney' => $this->bigInt,
            'photos' => [
                'name' => $this->text,
                'url' => $this->text,
                'vehicles' => [
                    'id' => $this->integer,
                    'date' => $this->dateNormal,
                    'name' => $this->text,
                    'model' => [
                        'id' => $this->integer,
                        'name' => $this->text
                    ]
                ]
            ],
            'rooftop' => [
                'id' => $this->integer,
                'name' => $this->text,
                'description' => $this->text,
                'category' => [
                    'id' => $this->integer,
                    'name' => $this->text,
                    'parent' => [
                        'id' => $this->integer,
                        'name' => $this->text
                    ]
                ]
            ],
            'selling_price' => [
                'price' => [
                    'value' => $this->integer,
                ],
            ],
            'issues' => [
                'key' => $this->text
            ]
        ];
    }
}
