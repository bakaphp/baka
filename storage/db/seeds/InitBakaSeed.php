<?php

use Baka\Test\Support\Models\Leads;
use Faker\Factory;
use Phalcon\Security\Random;
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
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $faker->email,
                'apps_id' => 1,
                'leads_owner_id' => 1,
                'companies_id' => 1,
                'companies_branch_id' => 1,
                'users_id' => 1,
                'is_active' => 1,
                'system_modules_id' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0,
            ],
        ];

        for ($i = 0; $i < rand(10, 50) ; $i++) {
            $data[] = [
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'email' => $faker->email,
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
        $data = [
            [
                'name' => 'CRM',
                'description' => substr($faker->text, 0, 50),
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0,
            ]
        ];

        $posts = $this->table('apps');
        $posts->insert($data)
            ->save();

        $data = [
            [
                'name' => 'baka',
                'slug' => 'baka',
                'model_name' => Leads::class,
                'menu_order' => 1,
                'browse_fields' => '{}',
                'apps_id' => 1,
                'parents_id' => 0,
                'use_elastic' => 1,
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('system_modules');
        $table->insert($data)
                  ->save();

        $data = [
            [
                'name' => 'text',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $table = $this->table('custom_fields_types');
        $table->insert($data)
                  ->save();

        $random = new Random();

        //add default languages
        $data = [
            [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => 'Anonymous',
                'lastname' => 'Anonymous',
                'default_company' => 1,
                'displayname' => 'anonymous',
                'system_modules_id' => 2,
                'user_last_login_try' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],
            [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'nobody' . time(),
                'system_modules_id' => 2,
                'user_level' => 1,
                'user_last_login_try' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],
            [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'mcmnobody',
                'system_modules_id' => 2,
                'user_level' => 3,
                'user_last_login_try' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],
            [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'nobodymc' . time(),
                'system_modules_id' => 2,
                'user_last_login_try' => 0,
                'user_level' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ], [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'nobody' . time(),
                'system_modules_id' => 2,
                'user_level' => 2,
                'user_last_login_try' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ], [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'nobody' . time(),
                'system_modules_id' => 2,
                'user_level' => 1,
                'user_last_login_try' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],  [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'nobody' . time(),
                'system_modules_id' => 2,
                'user_last_login_try' => 0,
                'user_level' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ],  [
                'user_activation_email' => $random->uuid(),
                'email' => $faker->email,
                'password' => password_hash('bakatest123567', PASSWORD_DEFAULT),
                'firstname' => $faker->firstName,
                'lastname' => $faker->lastName,
                'default_company' => 1,
                'displayname' => 'mcsomethingn',
                'system_modules_id' => 2,
                'user_last_login_try' => 0,
                'user_level' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'user_active' => 1,
                'is_deleted' => 0
            ]
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
