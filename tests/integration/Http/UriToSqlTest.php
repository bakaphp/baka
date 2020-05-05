<?php

namespace Baka\Test\Integration\Http;

use PhalconUnitTestCase;
use Baka\Http\Converter\RequestUriToSql;
use Baka\Test\Support\Models\Leads;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleRecords;

class UriToSqlTest extends PhalconUnitTestCase
{
    /**
     * Test a normal query with no conditional.
     *
     * @return boolean
     */
    public function testSimpleQuery()
    {
        $params = [];
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();
        //print_r('3');
        //throw new Exception('3');
        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertTrue(count($results->toArray()) > 0);
        $this->assertTrue($count > 0);

    }

    /**
     * Test normal columns.
     *
     * @return void
     */
    public function testQueryColumns()
    {
        $params = [];
        $params['columns'] = '(users_id, firstname, lastname, is_deleted, is_active, leads_owner_id)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirmed records
        foreach ($results as $result) {
            //doesn't existe id
            $this->assertFalse(isset($result->id));
        }

        $this->assertTrue(count($results->toArray()) == 10);
        $this->assertTrue($count > 0);


    }

    /**
     * Test normal conditions.
     *
     * @return void
     */
    public function testQueryConditionals()
    {
        $params = [];
        $params['q'] = '(is_deleted:0)';
        $params['limit'] = '11';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirmed records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertTrue(count($results->toArray()) == 11);
        $this->assertTrue($count > 0);
    }

    /**
     * Test normal conditions.
     *
     * @return void
     */
    public function testQueryConditionalsWithAnd()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1)';
        $params['limit'] = '12';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertTrue(count($results->toArray()) == 12);

    }

    /**
     * Test conditional with an OR.
     *
     * @return void
     */
    public function testQueryConditionalsWithOr()
    {
        $params = [];
        $params['q'] = '(is_deleted:0;companies_id:2)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);

        $request = $requestToSql->convert();
        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertTrue(count($results->toArray()) > 0);
        $this->assertTrue($count > 0);
    }

    /**
     * Test with and and Or.
     *
     * @return void
     */
    public function testQueryConditionalsWithAndOr()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1,leads_owner_id~0,id>0;created_at>0)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertTrue(count($results->toArray()) > 0);
        $this->assertTrue($count > 0);
    }

    /**
     * Test with and and Or.
     *
     * @return void
     */
    public function testQueryConditionalsLimit()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1,leads_owner_id~0,id>0;created_at>0)';
        $params['limit'] = '1';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result->id > 0);
        }

        $this->assertEquals(1, count($results->toArray()));
        $this->assertTrue($count > 0);
    }

    /**
     * Do relationship queries.
     *
     * @return void
     */
    public function testQueryConditionalsWithRelationships()
    {
    }

    /**
     * Do custom fields query.
     *
     * @return void
     */
    public function testQueryConditionalsWithCutomFields()
    {
    }

    /**
     * Specify a custom column.
     *
     * @return void
     */
    public function testQueryConditionalsWithCustomColumns()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1,leads_owner_id~0,id>0;created_at>0)';
        $params['columns'] = '(id)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $requestToSql->setCustomColumns('companies_id');
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue(isset($result->companies_id));
            $this->assertFalse(isset($result->users_id));
        }

        $this->assertTrue(count($results->toArray())  > 0);
        $this->assertTrue($count > 0);
    }

    /**
     * Specify a custom table.
     *
     * @return void
     */
    public function testQueryConditionalsWithCustomTable()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1,leads_owner_id~0,id>0;created_at>0)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);

        //join with the same table
        $requestToSql->setCustomTableJoins(' , leads as b');
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue(isset($result->companies_id));
            $this->assertTrue(isset($result->users_id));
        }

        $this->assertTrue(count($results->toArray())  > 0);
        $this->assertTrue($count > 0);
    }

    /**
     * Do custom quer contiioanals.
     *
     * @return void
     */
    public function testQueryConditionalsWithCustomCondition()
    {
        $params = [];
        $params['q'] = '(is_deleted:0,is_active:1)';
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);

        $requestToSql->setCustomConditions('AND leads_owner_id != 1');
        $request = $requestToSql->convert();

        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        $this->assertTrue(empty($results->toArray()));

        $this->assertEquals(0, $count);
    }

    /**
     * Test query with append params.
     *
     * @return void
     */
    public function testQueryWithAppendParams()
    {
        //create the index first
        $params = [];
        $params['q'] = ('companies_id>0');
        $params['columns'] = '(id)';
        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToSql($params, $leads);
        $requestToSql->appendParams([
            ['is_deleted', ':', '0']
        ]);

        $request = $requestToSql->convert();
        $results = (new SimpleRecords(null, $leads, $leads->getReadConnection()->query($request['sql'], $request['bind'])));
        $count = $leads->getReadConnection()->query($request['countSql'], $request['bind'])->fetch(\PDO::FETCH_OBJ)->total;

        $this->assertTrue(count($results->toArray()) > 0);
    }
}
