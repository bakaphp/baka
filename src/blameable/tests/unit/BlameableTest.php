<?php

use Test\Model\Leads;
use Test\Model\Audits;

class BlameableTest extends PhalconUnitTestCase
{
    /**
     * Create the index if it doesnt exist to run some test.
     *
     * @return void
     */
    public function testModelAddBehavior()
    {
        $lead = new Leads();

        $this->assertTrue(is_object($lead));
    }

    /**
     * Create a new record and confirm we have the audit recor.
     *
     * @return void
     */
    public function testAuditCreation()
    {
        //create a lead
        $lead = new Leads();
        $lead->users_id = 1;
        $lead->companies_id = 1;
        $lead->firstname = $this->faker->name;
        $lead->lastname = $this->faker->lastname;
        $lead->email = $this->faker->email;
        $lead->phone = $this->faker->phoneNumber;
        $lead->leads_owner_id = 1;
        $lead->leads_status_id = 1;
        $lead->save();

        $leadId = $lead->getId();

        $auditRecord = Audits::findFirst([
            'conditions' => 'entity_id = ?0 and model_name = ?1',
            'bind' => [$leadId, get_class($lead)]
        ]);

        //we have an audit of this record
        $this->assertTrue($leadId == $auditRecord->entity_id);

        //its marked as created
        $this->assertTrue($auditRecord->type == 'C');
    }

    /**
     * Confirm the audit of the record after an update.
     *
     * @return void
     */
    public function testAuditUpdate()
    {
        $lead = Leads::findFirst();
        $lead->firstname = $this->faker->name;
        $lead->email = $this->faker->email;
        $lead->leads_owner_id = 2;
        $lead->update();

        $auditRecord = Audits::findFirst([
            'conditions' => 'entity_id = ?0 and model_name = ?1',
            'bind' => [$lead->getId(), get_class($lead)]
        ]);

        //we have an audit of this record
        $this->assertTrue($lead->getId() == $auditRecord->entity_id);

        //its marked as update
        $this->assertTrue($auditRecord->type == 'U');
    }

    /**
     * Confirm the audit of the record after an update.
     *
     * @return void
     */
    public function testAuditDelete()
    {
        $lead = Leads::findFirst();
        $leadId = $lead->getId();
        $lead->delete();

        //filter it by delete , because the first record is the same as the previous test
        $auditRecord = Audits::findFirst([
            'conditions' => 'entity_id = ?0 and model_name = ?1 and type = ?2' ,
            'bind' => [$lead->getId(), get_class($lead), 'D']
        ]);

        //we have an audit of this record
        $this->assertTrue($leadId == $auditRecord->entity_id);

        //its marked as deleted
        $this->assertTrue($auditRecord->type == 'D');
    }
}
