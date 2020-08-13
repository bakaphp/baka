<?php

namespace Baka\Test\Integration\Database;

use Baka\Database\CustomFields\AppsCustomFields;
use Baka\Test\Support\Models\Leads;
use PhalconUnitTestCase;

class ModelCustomFieldsTest extends PhalconUnitTestCase
{
    /**
     * Create the index if it doesn't exist to run some test.
     *
     * @return void
     */
    public function testBakaModel()
    {
        $lead = Leads::findFirst();
        $this->assertTrue(is_object($lead));
    }

    /**
     * test.
     *
     * @return void
     */
    public function testSaveAndFail()
    {
        $lead = new Leads();
        $lead->firstname = $this->faker->name;
        $lead->lastname = $this->faker->lastname;
        $lead->email = $this->faker->email;
        $lead->users_id = 1;
        $lead->companies_id = 1;
        $lead->apps_id = $this->getDI()->get('app')->getId();
        $lead->companies_branch_id = 1;
        $lead->leads_owner_id = 1;
        $lead->system_modules_id = 1;
        $lead->setCustomFields([
            'reference' => $this->faker->name
        ]);

        $this->assertTrue($lead->saveOrFail());
    }

    /**
     * test.
     *
     * @return void
     */
    public function testUpdateAndFail()
    {
        $lead = Leads::findFirst();
        $lead->lastname = $this->faker->lastname;
        $lead->setCustomFields([
            'reference' => $this->faker->name
        ]);

        $this->assertTrue($lead->updateOrFail());
    }

    public function testSet()
    {
        $name = $this->faker->name;
        $lead = Leads::findFirst();
        $lead->set('test_set', $name);
        $lead->set('reference', $name);

        $this->assertEquals($lead->get('test_set'), $name);
    }

    public function testGet()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $this->assertNotEmpty($lead->get('reference'));
    }

    public function testSetUpdate()
    {
        $name = $this->faker->name;
        $lead = Leads::findFirst();
        $lead->set('test_set', $name);

        $this->assertEquals($lead->get('test_set'), $name);

        $name = $this->faker->name;
        $lead->set('test_set', $name);
        $this->assertEquals($lead->get('test_set'), $name);
    }

    /**
     * Check that a custom field has it attribute.
     *
     * @return void
     */
    public function testGetAllCustomField()
    {
        $leads = Leads::find('id in (' .
            implode(',', array_map(
                fn ($lead) => $lead['entity_id'],
                AppsCustomFields::find(['limit' => 10, 'columns' => 'entity_id'])->toArray()
            ))
        . ')');

        foreach ($leads as $lead) {
            $this->assertNotEmpty($lead->getAll());
        }
    }

    public function testGetOneCustomField()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $this->assertNotEmpty($lead->get('reference'));
    }

    public function testToArray()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $this->assertArrayHasKey('reference', $lead->toArray());
    }

    public function testReCacheCustomField()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $this->di->get('redis')->del($lead->getCustomFieldPrimaryKey());
        $lead->reCacheCustomFields();
        $this->assertNotEmpty($lead->get('reference'));
    }

    public function testCreateAppCustomField()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $field = 'new_field';
        $this->assertTrue($lead->createCustomField($field)->name == $field);
    }

    public function testCleanFields()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $lead->deleteAllCustomFields();
        $this->assertEmpty($lead->getAll());
    }

    public function testDeleteCustomField()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst([
            'order' => 'id desc',
        ])->entity_id);

        $this->assertNotEmpty($lead->get('reference'));
        $lead->del('reference');
        $this->assertEmpty($lead->get('reference'));
    }
}
