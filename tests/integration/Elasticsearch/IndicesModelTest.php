<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Contracts\Elasticsearch\CustomFiltersSchemaTrait;
use Baka\Elasticsearch\Models\Documents;
use Baka\Elasticsearch\Models\Indices;
use Baka\Test\Support\ElasticModel\Leads as ElasticModelLeads;
use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;

class IndicesModelTest extends PhalconUnitTestCase
{
    use CustomFiltersSchemaTrait;

    public $config;

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

        //insert into elastic
        Documents::add($lead, 1); //depth

        $params = [
            'index' => 'leads',
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

        //insert into elastic
        $result = Documents::delete($lead); //depth

        $this->assertTrue($result['_shards']['successful'] == 1);
    }

    public function testInsertFromFindFirst()
    {
        $lead = ElasticModelLeads::findFirst();
        $leadElastic = $lead->saveToElastic();

        $this->assertArrayHasKey('result', $leadElastic);
        $this->assertTrue($leadElastic['result'] == 'created');
        $this->assertTrue($lead->getId() == $leadElastic['_id']);
    }

    public function testDeleteFromFindFirst()
    {
        $lead = ElasticModelLeads::findFirst();
        $leadElastic = $lead->deleteFromElastic();

        $this->assertArrayHasKey('result', $leadElastic);
        $this->assertTrue($leadElastic['result'] == 'deleted');
        $this->assertTrue($lead->getId() == $leadElastic['_id']);
    }

    public function testCheckIndices()
    {
        $this->assertTrue(Indices::exist(Leads::class));
    }

    public function testIndicesName()
    {
        $lead = Leads::findFirst();
        $this->assertTrue(Indices::getName($lead) == $lead->getSource());
    }

    /*     public function testCreateIndices()
        {
            $indices = Indices::create(Leads::class);

            $this->assertArrayHasKey('index', $indices);
            $this->assertTrue((int) $indices['acknowledged'] == 1);
        }

        public function testCreateWithOptionsIndices()
        {
            //delete and create again
            Indices::delete(Leads::findFirst());

            $indices = Indices::create(Leads::class, 3, 300);

            $this->assertArrayHasKey('index', $indices);
            $this->assertTrue((int) $indices['acknowledged'] == 1);
        } */

    public function testAfterSave()
    {
        $lead = new ElasticModelLeads();
        $lead->firstname = $this->faker->name;
        $lead->lastname = $this->faker->lastname;
        $lead->email = $this->faker->email;
        $lead->system_modules_id = 1;
        $lead->apps_id = $this->getDI()->get('app')->getId();
        $lead->companies_branch_id = 1;
        $lead->users_id = 1;
        $lead->companies_id = 1;
        $lead->leads_owner_id = 1;
        $lead->saveOrFail();

        //need to wait 1 sec for it to showup on results (will need to review this later on)
        sleep(3);

        $this->assertTrue(
            ElasticModelLeads::findFirstInElastic(['conditions' => 'id = ' . $lead->getId()]) instanceof ElasticModelLeads
        );
    }

    /*   public function testCreateDeleteIndices()
      {
          $lead = Leads::findFirst();
          $indices = Indices::delete($lead);
          $this->assertTrue((int) $indices['acknowledged'] == 1);
      } */
}
