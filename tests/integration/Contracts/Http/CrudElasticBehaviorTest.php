<?php
declare(strict_types=1);

namespace Baka\Test\Integration\Contracts\Http;

use Baka\Contracts\Http\Api\CrudElasticBehaviorTrait;
use Baka\Contracts\Http\Api\ResponseTrait;
use Baka\Http\QueryParser\QueryParser;
use Baka\Support\Str;
use Baka\Test\Support\ElasticModel\Leads;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use PhalconUnitTestCase;

class CrudElasticBehaviorTest extends PhalconUnitTestCase
{
    use ResponseTrait;
    use CrudElasticBehaviorTrait;

    protected ?RequestInterface $request = null;

    public function testIndex()
    {
        $leads = new Leads();
        $this->model = $leads;

        $limit = 100;
        $params = [];
        $params['q'] = '(is_deleted:0,companies_id>0)';
        //$params['q'] = '(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level>0)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $additionalSearchFields = [];

        $parse = new QueryParser($leads, $params);
        //$parse->setAdditionalQueryFields($this->additionalSearchFields);

        //convert to SQL
        $processedRequest = [
            'sql' => $parse
        ];

        $results = $this->processOutput(
            $this->getRecords($processedRequest)
        );

        foreach ($results['results'] as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof Leads);
        }

        $this->assertTrue($results['total'] > 0);
    }

    public function testAdditionalFixParams()
    {
        $leads = new Leads();
        $this->model = $leads;

        $limit = 100;
        $params = [];
        // $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)';
        //$params['q'] = '(is_deleted:0)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $additionalSearchFields = [
            ['companies_id', '>', 1],
            ['is_deleted', ':', 0],
        ];

        $parse = new QueryParser($leads, $params);
        $parse->setAdditionalQueryFields($additionalSearchFields);

        //convert to SQL
        $processedRequest = [
            'sql' => $parse
        ];

        $results = $this->processOutput(
            $this->getRecords($processedRequest)
        );

        $this->assertTrue($results['total'] >= 0);
        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'companies_id'));
        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'is_deleted'));
    }

    public function testAdditionalWithPagination()
    {
        $leads = new Leads();
        $this->model = $leads;

        $limit = 100;
        $params = [];
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $additionalSearchFields = [
            ['companies_id', '>', 1],
            ['is_deleted', ':', 0],
        ];

        $parse = new QueryParser($leads, $params);
        $parse->setAdditionalQueryFields($additionalSearchFields);

        //convert to SQL
        $processedRequest = [
            'sql' => $parse
        ];

        $records = $this->getRecords($processedRequest);
        $results = $records['results'];

        //this means the want the response in a vuejs format

        $results = [
            'data' => $results,
            'limit' => $limit,
            'page' => $params['page'],
            'total_pages' => ceil($records['total'] / $limit),
        ];

        $results = $this->processOutput($results);

        $this->assertTrue($results['total_pages'] >= 1);
        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'companies_id'));
        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'is_deleted'));
    }

    public function testIndexWithAdditional()
    {
        $leads = new Leads();
        $this->model = $leads;

        $limit = 100;
        $params = [];
        $params['q'] = '(is_deleted:1,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)';
        //$params['fields'] = '';
        $params['limit'] = $limit;
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $additionalSearchFields = [
            ['is_deleted', ':', 0],
        ];
        $parse = new QueryParser($leads, $params);
        $parse->setAdditionalQueryFields($additionalSearchFields);

        //convert to SQL
        $processedRequest = [
            'sql' => $parse
        ];

        $results = $this->processOutput(
            $this->getRecords($processedRequest)
        );

        foreach ($results['results'] as $result) {
            $this->assertTrue($result->getId() > 0);
            $this->assertTrue($result instanceof Leads);
        }

        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'companies_id'));
        $this->assertTrue(Str::contains($parse->getParsedQuery(), 'is_deleted = 0'));
        $this->assertTrue($results['total'] > 0);
    }

    public function testGetById()
    {
        $leads = new Leads();
        $this->model = $leads;
        $this->request = new Request();

        $elasticLead = Leads::findFirstInElastic();

        $result = $this->getRecordById($elasticLead->getId());

        $this->assertTrue($result->getId() > 0);
        $this->assertTrue($result instanceof Leads);
    }
}
