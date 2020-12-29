<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Contracts\Elasticsearch\CustomFiltersSchemaTrait;
use Baka\Elasticsearch\Models\Indices;
use Baka\Test\Support\ElasticModel\Leads;
use PhalconUnitTestCase;

class CustomFilterSchemaTest extends PhalconUnitTestCase
{
    use CustomFiltersSchemaTrait;

    /**
     * Emulate DI.
     *
     * @var elastic
     */
    protected $elastic;

    /**
     * Test the creation of a normal index based on a model extending
     * from the Indices class of the package.
     *
     * @return void
     */
    public function testFilterSchema()
    {
        //Indices::create(Leads::class);

        $this->elastic = $this->getDI()->getElastic();

        $mapping = $this->getSchema('leads');

        $this->assertTrue(!empty($mapping));
        $this->assertTrue(array_search('id', $mapping) > 0);

        //Indices::delete(Leads::findFirst());
    }
}
