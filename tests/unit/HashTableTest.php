<?php

use Test\Model\LeadsNormal as Leads;
use Baka\Database\Contracts\HashTableTasksTrait;

class HashTableTest extends PhalconUnitTestCase
{
    use HashTableTasksTrait;

    /**
     * Create the index if it doesnt exist to run some test.
     *
     * @return void
     */
    public function testCreateHashTable()
    {
        $this->getDI()->getDb()->query('DROP TABLE leads_settings');

        $hashTable = $this->createModuleAction([
            'Test\Model\Leads', //model
        ]);

        $this->assertTrue((bool) preg_match('/Hash table for Module Created/i', $hashTable));
    }

    /**
     * Confirm adde settings
     *
     * @return void
     */
    public function testSetASettingsForAModule()
    {
        $lead = Leads::findFirst();

        $this->assertTrue($lead->set('company', $this->faker->text));
    }

    /**
     * Confirm get a settings
     *
     * @return void
     */
    public function testGetASettingsForAModule()
    {
        $lead = Leads::findFirst();

        $this->assertTrue(!empty($lead->get('company')));
    }
}
