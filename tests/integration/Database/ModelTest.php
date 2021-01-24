<?php

namespace Baka\Test\Integration\Database;

use Baka\Test\Support\Models\LeadsNormal as Leads;
use Baka\Test\Support\Models\Users;
use PhalconUnitTestCase;

class ModelTest extends PhalconUnitTestCase
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
        $lead->system_modules_id = 1;
        $lead->apps_id = $this->getDI()->get('app')->getId();
        $lead->companies_branch_id = 1;
        $lead->users_id = 1;
        $lead->companies_id = 1;
        $lead->leads_owner_id = 1;

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

        $this->assertTrue($lead->updateOrFail());
    }

    public function testGetByIdOrFail()
    {
        $lead = Leads::findFirst();
        $leadNew = Leads::getByIdOrFail($lead->getId());

        $this->assertTrue($lead->getId() == $leadNew->getId());
    }

    public function testFindFirstOrCreate()
    {
        $email = $this->faker->email;
        $lead = Leads::findFirstOrCreate(
            [
                'conditions' => 'email = ?0',
                'bind' => [$email],
            ],
            [
                'email' => $email,
                'firstname' => $this->faker->name,
                'lastname' => $this->faker->lastname,
                'system_modules_id' => 1,
                'apps_id' => $this->getDI()->get('app')->getId(),
                'companies_branch_id' => 1,
                'users_id' => 1,
                'companies_id' => 1,
                'leads_owner_id' => 1,
            ]
        );

        $this->assertTrue($lead->email == $email);
        $this->assertTrue(get_class($lead) == Leads::class);
    }

    public function testUpdateOrCreate()
    {
        $email = $this->faker->email;
        $lead = Leads::updateOrCreate(
            [
                'conditions' => 'email = ?0',
                'bind' => [$email],
            ],
            [
                'email' => $email,
                'firstname' => $this->faker->name,
                'lastname' => $this->faker->lastname,
                'system_modules_id' => 1,
                'apps_id' => $this->getDI()->get('app')->getId(),
                'companies_branch_id' => 1,
                'users_id' => 1,
                'companies_id' => 1,
                'leads_owner_id' => 1,
            ]
        );

        $this->assertTrue($lead->email == $email);
        $this->assertTrue(get_class($lead) == Leads::class);
    }

    public function testCascadeSoftDelete()
    {
        $user = Users::findFirst();
        $user->cascadeSoftDelete();

        $this->assertEmpty($user->getSubscriptions());
    }
}
