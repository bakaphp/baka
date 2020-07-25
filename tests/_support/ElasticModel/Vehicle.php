<?php

namespace Baka\Test\Support\ElasticModel;

use Baka\Elasticsearch\Objects\Documents;
use stdClass;

class Vehicle extends Documents
{

    /**
     * Index data.
     *
     * @return stdClass
     */
    /* public function data() : stdClass
    {
        $object = new stdClass();
        $object->id = 1;
        $this->setId($object->id);

        $object->description = 'tetada';
        $object->date = '2018-01-01';
        $object->money = 10.1;
        $object->anotherMoney = 10.1;

        $photos[] = [
            'name' => 'test',
            'url' => 'http://mctekk.com',
            'vehicles' => [
                'id' => 2,
                'date' => '2018-01-02',
                'name' => 'wtf',
            ]
        ];

        $photos[] = [
            'name' => 'test',
            'url' => 'http://mctekk.com',
            'vehicles' => [[
                'id' => 2,
                'date' => '2018-01-02',
                'name' => 'wtf', ], [
                    'id' => 2,
                    'date' => '2018-01-02',
                    'name' => 'wtf',
                ]
            ]
        ];

        $object->photo = $photos;

        return $object;
    } */

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
                ]
            ]
        ];
    }
}
