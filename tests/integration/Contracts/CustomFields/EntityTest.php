<?php
declare(strict_types=1);

namespace Baka\Test\Integration\Contracts\CustomFields;

use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;

class EntityTest extends PhalconUnitTestCase
{
    public function testModel()
    {
        $lead = Leads::findFirstOrFail();
        $lead->set('customField', 'hello');

        $this->assertEquals($lead->get('customField'), 'hello');
    }

    public function testModelArray()
    {
        $lead = Leads::findFirstOrFail();
        $lead->set('customArray', [1, 2, 3]);

        $this->assertEquals($lead->get('customArray'), [1, 2, 3]);
    }

    public function testGetAllCustomFields()
    {
        $lead = Leads::findFirstOrFail();
        $customFields = $lead->getAllCustomFields();

        $this->assertIsArray($customFields);
        $this->assertArrayHasKey('customField', $customFields);
    }

    public function testDeleteCustomField()
    {
        $lead = Leads::findFirstOrFail();

        $this->assertTrue($lead->del('customField'));
    }

    public function testToArray()
    {
        $lead = Leads::findFirstOrFail();

        $this->assertArrayHasKey('customArray', $lead->toArray());
    }
}
