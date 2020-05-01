<?php

use Test\Model\LeadsNormal as Leads;

class ModelTest extends PhalconUnitTestCase
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
        
        $this->assertTrue($lead->updateOrFail());
    }

}