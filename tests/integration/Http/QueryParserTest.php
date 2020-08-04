<?php

namespace Baka\Test\Integration\Http;

use Baka\Http\QueryParser\QueryParser;
use Baka\Test\Support\ElasticModel\Leads;
use PhalconUnitTestCase;

class QueryParserTest extends PhalconUnitTestCase
{
    /**
     * Test a normal query with no conditional.
     *
     * @return boolean
     */
    public function testSimpleQuery()
    {
        $params = [];
        $params['q'] = "(is_deleted:0,companies_id>0,user.displayname:mc%,user.id>0;user.user_level:3)";
        $params['fields'] = '(id)';
        $params['limit'] = '100';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $queryParser = new QueryParser(new Leads(), $params);

        echo $queryParser->getParsedQuery();
        die();
    }
}
