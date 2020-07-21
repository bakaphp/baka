<?php

namespace Baka\Test\Integration\Database;

use Baka\Test\Support\Models\LeadsNormal as Leads;
use PhalconUnitTestCase;

class HashTableTest extends PhalconUnitTestCase
{
    /**
     * Confirm adde settings.
     *
     * @return void
     */
    public function testSetASettingsForAModule()
    {
        $lead = Leads::findFirst();

        $this->assertTrue($lead->set('company', $this->faker->text));
    }

    /**
     * Confirm get a settings.
     *
     * @return void
     */
    public function testGetASettingsForAModule()
    {
        $lead = Leads::findFirst();

        $this->assertTrue(!empty($lead->get('company')));
    }
}
