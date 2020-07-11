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

    /**
     * Check that a custom field has it attribute.
     *
     * @return void
     */
    public function testGetCustomFieldRow()
    {
        $lead = Leads::findFirst(AppsCustomFields::findFirst()->entity_id);

        print_r($lead->customFields);
        die();

        $this->assertTrue(isset($lead->reference));
    }

    public function testSet()
    {
        $name = $this->faker->name;
        $lead = Leads::findFirst();
        $lead->set('test_set', $name);

        $this->assertEquals($lead->get('test_set'), $name);
    }
}
