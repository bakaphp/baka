<?php
declare(strict_types=1);

namespace Baka\Test\Integration\Contracts\CustomFields;

use Baka\Test\Support\Models\LeadsNormal;
use PhalconUnitTestCase;

class EntityHashTableTest extends PhalconUnitTestCase
{
    public function testModel()
    {
        $lead = LeadsNormal::findFirstOrFail();
        $lead->set('customField', 'hello');

        $this->assertEquals($lead->get('customField'), 'hello');
    }

    public function testModelArray()
    {
        $lead = LeadsNormal::findFirstOrFail();
        $lead->set('customArray', [1, 2, 3]);

        $this->assertEquals($lead->get('customArray'), [1, 2, 3]);
    }

    public function testGetAllCustomFields()
    {
        $lead = LeadsNormal::findFirstOrFail();
        $customFields = $lead->getAllSettings();

        $this->assertIsArray($customFields);
        $this->assertArrayHasKey('customField', $customFields);
    }

    public function testDeleteCustomField()
    {
        $lead = LeadsNormal::findFirstOrFail();

        $this->assertTrue($lead->deleteHash('customField'));
    }
}
