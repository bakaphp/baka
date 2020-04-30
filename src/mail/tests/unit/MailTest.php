<?php

class AuthTest extends PhalconUnitTestCase
{
    /**
     * Test normal email.
     *
     * @return boolean
     */
    public function testSimpleEmail()
    {
        //send email
        $this->_getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Normal Email Queue')
            ->content('normal email send via queue')
            ->send();
    }

    /**
     * Test html email.
     *
     * @return boolean
     */
    public function testTemplateMail()
    {
        //send email
        $this->_getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Template Email queue')
            ->params(['name' => 'Max'])
            ->template() // email.volt
            ->send();
    }

    /**
     * test the smtp configuration.
     *
     * @return boolean
     */
    public function testEmailSmtpConfig()
    {
        //send email
        $this->_getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Template Email queue')
            ->params(['name' => 'dfad'])
            ->smtp(['username' => 'max@mctekk.com', 'password' => 'nosenose'])
            ->template() // email.volt
            ->send();
    }

    /**
    * Test normal email.
    *
    * @return boolean
    */
    public function testSimpleEmailNow()
    {
        //send email
        $mailer = $this->_getDI()->get('mail');

        $mailer->to('bakaphpmail@getnada.com')
            ->subject('Test Normal Email')
            ->content('Normal email sendnow')
            ->sendNow();

            $this->assertEmpty(
                $mailer->getFailedRecipients()
            );
    }
}
