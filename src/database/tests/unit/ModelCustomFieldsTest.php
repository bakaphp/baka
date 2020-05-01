<?php

use Test\Model\Leads;
use Test\Model\LeadsCustomFields;

class ModelCustomFieldsTest extends PhalconUnitTestCase
{
    /**
     * Create the index if it doesnt exist to run some test.
     *
     * @return void
     */
    public function testBakaModel()
    {
        $lead = Leads::findFirst();
        $this->assertTrue(is_object($lead));
    }

    /**
     * test
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
        $lead->leads_owner_id = 1;
        $lead->setCustomFields([
            'refernce' => $this->faker->name
        ]);
        
        $this->assertTrue($lead->saveOrFail());
    }

    /**
     * test
     *
     * @return void
     */
    public function testUpdateAndFail()
    {
        $lead = Leads::findFirst();
        $lead->lastname = $this->faker->lastname;
        $lead->setCustomFields([
            'refernce' => $this->faker->name
        ]);

        $this->assertTrue($lead->updateOrFail());
    }

    /**
     * Check taht a custom field has it atrribute
     *
     * @return void
     */
    public function testGetCustomFieldRow()
    {
        $leadCustomField = LeadsCustomFields::findFirst();
        $lead = Leads::findFirst($leadCustomField->leads_id);

        $this->assertTrue(isset($lead->refernce));
    }

}