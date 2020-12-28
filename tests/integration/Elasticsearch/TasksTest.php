<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Contracts\Elasticsearch\CustomFiltersSchemaTrait;
use Baka\Contracts\Elasticsearch\IndexBuilderTaskTrait;
use Baka\Elasticsearch\Models\Indices;
use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;

class TasksTest extends PhalconUnitTestCase
{
    use IndexBuilderTaskTrait;
    use CustomFiltersSchemaTrait;

    public $config;

    /**
     * Create a index base on a model.
     *
     * @return void
     */
    public function testCreateIndiceFromModel()
    {
        $this->elastic = $this->getDI()->getElastic();

        $this->createIndexAction(Leads::class, 2);

        $mapping = $this->getSchema('leads');

        $this->assertTrue(array_search('id', $mapping) > 0);
    }

    public function testdeleteIndiceFromModel()
    {
        $this->elastic = $this->getDI()->getElastic();

        $this->deleteIndexAction(Leads::class);

        $this->assertFalse(Indices::exist(Leads::class));
    }

    /**
     * Test inserting data to elastic search from module.
     *
     * @return void
     */
    public function testInsertAllDataFromModel()
    {
        $this->createIndexAction(Leads::class, 2, 1000);

        //cli need the config
        $this->config = $this->getDI()->getConfig();
        $this->elastic = $this->getDI()->getElastic();

        $this->createDocumentsAction(Leads::class, 2);

        $lead = Leads::findFirst();
        $params = [
            'index' => 'leads',
            'id' => $lead->getId()
        ];

        $response = $this->elastic->get($params);

        $this->assertTrue($response['_source']['id'] == $lead->getId());
    }
}
