<?php
declare(strict_types=1);

namespace Baka\Test\Integration\Contracts\Http;

use Baka\Contracts\Http\Api\CrudElasticBehaviorTrait;
use Baka\Contracts\Http\Api\ResponseTrait;
use Baka\Http\QueryParser\QueryParser;
use Baka\Test\Support\ElasticModel\Leads;
use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use  PhalconUnitTestCase;

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
        $params['q'] = '(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)';
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
