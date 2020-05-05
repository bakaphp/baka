<?php

use Faker\Factory;
use Phinx\Seed\AbstractSeed;

class InitBakaSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $faker = Factory::create();

        $data = [
            [
                'name' => $faker->name,
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'apps_id' => 1,
                'leads_owner_id' => 1,
                'companies_id' => 1,
                'companies_branch_id' => 1,
                'users_id' => 1,
                'is_active' => 1,
                'system_modules_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0,
            ]
        ];

        for ($i = 0; $i < rand(10, 50) ; $i++) {
            $data[] = [
                'name' => $faker->name,
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'apps_id' => 1,
                'leads_owner_id' => 1,
                'companies_id' => rand(1, 2),
                'companies_branch_id' => 1,
                'users_id' => rand(1, 10),
                'is_active' => 1,
                'system_modules_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0,
            ];
        }

        $posts = $this->table('leads');
        $posts->insert($data)
            ->save();
    }
}
