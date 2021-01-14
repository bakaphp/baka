<?php

namespace Baka\Tes\Integration\Http;

use Baka\Elasticsearch\Client;
use Baka\Elasticsearch\IndexBuilderStructure;
use Baka\Http\Converter\RequestUriToElasticSearch;
use Baka\Tes\Support\Models\Leads;
use PhalconUnitTestCase;

class UriToElasticSqlTes extends PhalconUnitTestCase
{
    /**
     * Create the index if it doesnt exist to run some tes.
     *
     * @return void
     */
    public function tesInitElastic()
    {
        $elasticsearch = new IndexBuilderStructure();
        if (!$elasticsearch->existIndices(Leads::class)) {
            $elasticsearch->createIndices(Leads::class);
            $lead = new Leads();
            $elasticsearch->indexDocument($lead);
            $lead->setId(2);
            $elasticsearch->indexDocument($lead);
            $lead->setId(3);
            $elasticsearch->indexDocument($lead);
        }
    }

    /**
     * Tes a normal query with no conditional.
     *
     * @return bool
     */
    public function tesSimpleQuery()
    {
        //create the index first

        $params = [];
        //$params['q'] = ('is_deleted:0');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes a normal query with no conditional.
     *
     * @return bool
     */
    public function tesQueryColumns()
    {
        //create the index first

        $params = [];
        $params['columns'] = '(id, users_id, firstname, lastname, is_deleted)';
        //$params['q'] = ('is_deleted:0');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['users_id'] > 0);
            $this->assertTrue(!empty($result['firstname']));
            $this->assertTrue(!empty($result['lastname']));
            $this->assertTrue($result['is_deleted'] == 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes a normal query with no conditional.
     *
     * @return bool
     */
    public function tesQueryConditionals()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes a normal query with no conditional.
     *
     * @return bool
     */
    public function tesQueryConditionalsWithAnd()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0,firstname:max%');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes normal with Or.
     *
     * @return bool
     */
    public function tesQueryConditionalsWithOr()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0;firstname:max%');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes and and Or conditions.
     *
     * @return bool
     */
    public function tesQueryConditionalsWithAndOr()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0,firstname:max%;companies_id>0');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '10';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(3, count($results));
    }

    /**
     * Tes limit.
     *
     * @return void
     */
    public function tesQueryConditionalsLimit()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0,firstname:max%;companies_id>0');
        //$params['cq'] = ('company.name:mc%');
        //$params['cq'] = ('eventsversions.events_types_id:1;participantsprograms.programs_id:2,custom_fields.sexo:f,companiesoffices.districts_id:1;companiesoffices.countries_id:2,eventsversionsparticipants.is_deleted:0|1,eventsversionsparticipants.eventsversionsdates.event_date>2019-04-01,eventsversionsparticipants.eventsversionsdates.event_date<2019-04-14');
        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(2, count($results));
    }

    /**
     * Tes nested.
     *
     * @return void
     */
    public function tesQueryWithNestedCondition()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0,companies_id>0');
        $params['cq'] = ('company.name:mc%;company.id>0');

        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(2, count($results));
    }

    /**
     * Tes nested 2 dimesional.
     *
     * @return void
     */
    public function tesQueryWithNestedConditionTwoDimensional()
    {
    }

    /**
     * Tes nested 3 dimesional.
     *
     * @return void
     */
    public function tesQueryWithNestedConditionThreeDimensional()
    {
    }

    /**
     * Tes with and and Or.
     *
     * @return void
     */
    public function tesQueryWithNestedConditionWithAndOr()
    {
        //create the index first

        $params = [];
        $params['q'] = ('is_deleted:0,companies_id>0');
        $params['cq'] = ('company.name:mc%,company.id>0;company.branch_id:1');

        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
        }

        $this->assertEquals(2, count($results));
    }

    /**
     * Specify a custom column.
     *
     * @return void
     */
    public function tesQueryConditionalsWithCustomColumns()
    {
        //create the index first
        $params = [];
        $params['q'] = ('is_deleted:0,companies_id>0');
        $params['cq'] = ('company.name:mc%,company.id>0;company.branch_id:1');
        $params['columns'] = '(id)';
        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $requestToSql->setCustomColumns('companies_id');
        $request = $requestToSql->convert();

        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);
        $results = $client->findBySql($request['sql']);

        //confirme records
        foreach ($results as $result) {
            $this->assertTrue($result['id'] > 0);
            $this->assertTrue($result['companies_id'] > 0);
        }

        $this->assertEquals(2, count($results));
    }

    /**
     * Do custom quer contiioanals.
     *
     * @return void
     */
    public function tesQueryConditionalsWithCustomCondition()
    {
        //create the index first
        $params = [];
        $params['q'] = ('is_deleted:0,companies_id>0');
        $params['cq'] = ('company.name:mc,company.id>0;company.branch_id:1');
        $params['columns'] = '(id)';
        $params['limit'] = '2';
        $params['page'] = '1';
        $params['sort'] = 'id|desc';

        $leads = new Leads();
        $requestToSql = new RequestUriToElasticSearch($params, $leads);
        $requestToSql->setCustomConditions('AND is_deleted = 1');

        $request = $requestToSql->convert();
        $client = new Client('http://' . $this->config->elasticSearch['hosts'][0]);

        $results = $client->findBySql($request['sql']);

        $this->assertEquals(0, count($results));
    }
}
