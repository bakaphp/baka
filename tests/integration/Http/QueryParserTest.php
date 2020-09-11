<?php
declare(strict_types=1);

namespace Baka\Test\Integration\Http;

use Baka\Elasticsearch\Models\Documents as ModelsDocuments;
use Baka\Elasticsearch\Objects\Documents as ElasticDocuments;
use Baka\Http\QueryParser\QueryParser;
use Baka\Test\Support\ElasticModel\Leads;
use PhalconUnitTestCase;

class QueryParserTest extends PhalconUnitTestCase
{
    /**
     * Test a normal query with no conditional.
     *
     * @return bool
     */
    public function testSimpleQuery()
    {
        $params = [];
        $params['q'] = '';

        $queryParser = new QueryParser(new Leads(), $params);
        $results = ElasticDocuments::findBySql($queryParser->getParsedQuery());

        foreach ($results as $result) {
            $this->assertTrue(isset($result['id']));
            $this->assertTrue(isset($result['user']['id']));
        }
    }

    public function testSimpleQueryWithPagination()
    {
        $params = [];
        $params['q'] = '';
        //$params['fields'] = '';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $queryParser = new QueryParser(new Leads(), $params);
        $results = ElasticDocuments::findBySql($queryParser->getParsedQuery());

        foreach ($results as $result) {
            $this->assertTrue(isset($result['id']));
            $this->assertTrue(isset($result['user']['id']));
        }
    }

    public function testSimpleQueryWithConditional()
    {
        $limit = 100;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $queryParser = new QueryParser(new Leads(), $params);
        $results = ElasticDocuments::findBySql($queryParser->getParsedQuery());

        foreach ($results as $result) {
            $this->assertTrue(isset($result['id']));
            $this->assertTrue(isset($result['user']['id']));
        }
    }

    public function testSimpleQueryWithConditionalAndAdditionalQueryFields()
    {
        $limit = 100;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $queryParser = new QueryParser(new Leads(), $params);
        $queryParser->setAdditionalQueryFields([
            ['is_deleted', ':', '0'],
            ['companies_id', ':', 1],
        ]);

        $results = ElasticDocuments::findBySql($queryParser->getParsedQuery());

        foreach ($results as $result) {
            $this->assertTrue(isset($result['id']));
            $this->assertTrue(isset($result['user']['id']));
        }
    }

    public function testSimpleQueryWithModel()
    {
        $limit = 100;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:%mc%,user.id>0;user.user_level>0)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof $lead);
        }
    }

    public function testSimpleQueryWithModelLimit()
    {
        $limit = 2;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:%mc%,user.id>0;user.user_level>0)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        $this->assertTrue(count($results) == $limit);
        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof $lead);
        }
    }

    public function testSimpleQueryWithModelNoNested()
    {
        $limit = 2;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        $this->assertTrue(count($results) == $limit);
        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof $lead);
        }
    }

    public function testMultiNestedQuery()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.subscriptions.apps_id:1)';
        //$params['fields'] = '';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof $lead);
        }
    }

    public function testMultiNestedWithNestedQuery()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.subscriptions.apps_id:1,user.welcome:0)';
        //$params['fields'] = '';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof $lead);
        }
    }

    public function testMultiNestedWithNestedWithColumnsQuery()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.subscriptions.apps_id:1,user.welcome:0)';
        $params['fields'] = 'id,email';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ModelsDocuments::findBySql($queryParser->getParsedQuery(), new Leads());

        foreach ($results as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result->getId() > 0);
            $this->assertNotEmpty($result->email);
        }
    }

    public function testMultiNestedWithNestedNotModelQuery()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0,user.subscriptions.apps_id:1,user.welcome:0)';
        //$params['fields'] = '';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $lead = new Leads();
        $queryParser = new QueryParser($lead, $params);
        $results = ElasticDocuments::findBySql($queryParser->getParsedQuery());

        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
            $this->assertIsArray($result['user']);
            $this->assertIsArray($result);
        }
    }
}
