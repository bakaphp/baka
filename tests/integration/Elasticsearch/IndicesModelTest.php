<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Contracts\Elasticsearch\CustomFiltersSchemaTrait;
use Baka\Contracts\Elasticsearch\IndexBuilderTaskTrait;
use Baka\Elasticsearch\IndexBuilder;
use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;

class IndicesModelTest extends PhalconUnitTestCase
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

        //create index
        $this->createIndexAction([
            Leads::class,
            '1' //depth
        ]);

        $mapping = $this->getSchema('leads');

        $this->assertTrue(array_search('id', $mapping) > 0);
    }

    /**
     * Test inserting data to elastic search from module.
     *
     * @return void
     */
    public function testInsertAllDataFromModel()
    {
        //cli need the config
        $this->config = $this->getDI()->getConfig();
        $this->elastic = $this->getDI()->getElastic();

        $this->insertAction([
            Leads::class,
            1, //depth
        ]);

        $lead = Leads::findFirst();
        $params = [
            'index' => 'leads',
            'type' => 'leads',
            'id' => $lead->getId()
        ];

        $response = $this->elastic->get($params);

        $this->assertTrue($response['_source']['id'] == $lead->getId());
    }

    /**
     * Insert just 1 record.
     *
     * @return void
     */
    public function testInsertOneDocumentFromARecordModel()
    {
        //cli need the config
        $this->config = $this->getDI()->getConfig();
        $this->elastic = $this->getDI()->getElastic();

        $lead = Leads::findFirst();

        // Get elasticsearch class handler instance
        $elasticsearch = new IndexBuilder();

        //insert into elastic
        $elasticsearch->indexDocument($lead, 1); //depth

        $params = [
            'index' => 'leads',
            'type' => 'leads',
            'id' => $lead->getId()
        ];

        $response = $this->elastic->get($params);

        $this->assertTrue($response['_source']['id'] == $lead->getId());
    }

    /**
     * Delete from a record.
     *
     * @return void
     */
    public function testDeleteOneDocumentFromRecordModel()
    {
        //cli need the config
        $this->config = $this->getDI()->getConfig();
        $this->elastic = $this->getDI()->getElastic();

        $lead = Leads::findFirst();

        // Get elasticsearch class handler instance
        $elasticsearch = new IndexBuilder();

        //insert into elastic
        $result = $elasticsearch->deleteDocument($lead); //depth

        $this->assertTrue($result['_shards']['successful'] == 1);
    }
}
