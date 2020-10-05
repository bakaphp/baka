<?php

namespace Baka\Test\Integration\Elasticsearch;

use Baka\Test\Support\ElasticModel\Leads;
use Exception;
use PhalconUnitTestCase;

class ElasticableModelTest extends PhalconUnitTestCase
{
    /**
     * Insert just 1 record.
     *
     * @return void
     */
    public function testFindFirst()
    {
        $lead = Leads::findFirstInElastic([
            'conditions' => 'is_deleted >= :is_deleted: AND user.id > 1',
            'bind' => [
                'is_deleted' => 0
            ],
            'limit' => 100
        ]);

        $this->assertTrue($lead instanceof Leads);
    }

    public function testFind()
    {
        $limit = 5;
        $leads = Leads::findInElastic([
            'conditions' => 'is_deleted >= :is_deleted: AND user.id > 1',
            'bind' => [
                'is_deleted' => 0
            ],
            'limit' => $limit
        ]);

        $this->assertIsArray($leads);
        $this->assertTrue(count($leads) == $limit);
        foreach ($leads as $lead) {
            $this->assertTrue($lead instanceof Leads);
        }
    }

    public function testFindWithoutRelationshipReplace()
    {
        $limit = 5;
        $leads = Leads::findInElastic([
            'conditions' => 'is_deleted >= :is_deleted: AND user.id > 1',
            'bind' => [
                'is_deleted' => 0
            ],
            'limit' => $limit
        ]);

        $this->assertIsArray($leads);
        $this->assertTrue(count($leads) == $limit);
        foreach ($leads as $lead) {
            $this->assertTrue($lead instanceof Leads);
        }
    }

    public function testFindFirstNotFound()
    {
        $catch = false;
        try {
            $lead = Leads::findFirstInElastic([
                'conditions' => 'is_deleted >= :is_deleted: AND user.id > 1',
                'bind' => [
                    'is_deleted' => 1
                ],
            ]);
        } catch (Exception $e) {
            $catch = true;
        }

        $this->assertTrue($catch === true);
    }

    public function testFindNotFound()
    {
        $catch = false;
        try {
            $lead = Leads::findInElastic([
                'conditions' => 'is_deleted >= :is_deleted: AND user.id > 1',
                'bind' => [
                    'is_deleted' => 1
                ],
                'limit' => 100
            ]);
        } catch (Exception $e) {
            $catch = true;
        }

        $this->assertTrue($catch === true);
    }
}
