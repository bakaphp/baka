<?php

use Baka\Database\Contracts\CustomFilters\CustomFilterTrait;
use Baka\Database\CustomFilters\CustomFilters;

class CustomFiltersTest extends PhalconUnitTestCase
{
    use CustomFilterTrait;

    /**
     * Create the index if it doesnt exist to run some test.
     *
     * @return void
     */
    public function testCreateFilter()
    {
        $params = [
            'system_modules_id' => 1,
            'apps_id' => 1,
            'companies_id' => 1,
            'companies_branch_id' => 1,
            'users_id' => 1,
            'name' => 'Test Filter',
            'sequence_logic' => '1 AND 2',
            'description' => $this->faker->text,
            'criterias' => [
                [
                    'comparator' => '=',
                    'value' => $this->faker->email,
                    'field' => 'email',
                ],
                'and',
                [
                    'comparator' => '>',
                    'value' => 1,
                    'field' => 'leads_owner_id',
                ],
            ]
        ];

        $filter = $this->processFilter($params);
        $this->processsCriterias($filter, $params['criterias']);

        $this->assertTrue($filter instanceof CustomFilters);
    }

    /**
     * Update a filter.
     *
     * @return void
     */
    public function testUpdateFilter()
    {
        $customFilter = CustomFilters::findFirst();

        $params = [
            'id' => $customFilter->getId(),
            'system_modules_id' => 1,
            'apps_id' => 1,
            'companies_id' => 1,
            'companies_branch_id' => 1,
            'name' => 'Test Filter2',
            'sequence_logic' => '1 AND 2',
            'description' => $this->faker->text,
            'criterias' => [
                [
                    'comparator' => '=',
                    'value' => $this->faker->email,
                    'field' => 'email',
                ],
                'and',
                [
                    'comparator' => '>',
                    'value' => 1,
                    'field' => 'leads_owner_id',
                ],
            ]
        ];

        $filter = $this->processFilter($params);
        $this->updateCriterias($filter, $params['criterias']);

        $this->assertTrue($filter instanceof CustomFilters);
        $this->assertTrue($filter->getId() == $customFilter->getId());
    }

    /**
     * Get a generate query
     *
     * @return void
     */
    public function testGenerateFilterSql()
    {
        $customFilter = CustomFilters::findFirst();

        $this->assertTrue(!empty($customFilter->getQuery()));
    }
}
