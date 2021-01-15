<?php

namespace Baka\Test\Integration\Mail;

use PhalconUnitTestCase;

class MailTest extends PhalconUnitTestCase
{
    /**
     * Test normal email.
     *
     * @return bool
     */
    public function testSimpleEmail()
    {
        //send email

        $this->getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Normal Email Queue')
            ->content('normal email send via queue')
            ->send();
    }

    /**
     * Test html email.
     *
     * @return bool
     */
    public function testTemplateMail()
    {
        //send email

        $this->getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Template Email queue')
            ->params(['name' => 'Max'])
            ->template('email.volt') // email.volt
            ->send();
    }

    /**
     * test the smtp configuration.
     *
     * @return bool
     */
    public function testEmailSmtpConfig()
    {
        //send email

        $this->getDI()->get('mail')
            ->to('bakaphpmail@getnada.com')
            ->subject('Test Template Email queue')
            ->params(['name' => 'dfad'])
            ->smtp(['username' => $this->faker->email, 'password' => $this->faker->password])
            ->template() // email.volt
            ->send();
    }

    /**
     * Test normal email.
     *
     * @return bool
     */
    public function testSimpleEmailNow()
    {
        //send email

        $mailer = $this->getDI()->get('mail');

        $mailer->to('info@mctekk.com')
            ->subject('Test Normal Email')
            ->content('Normal email sendnow')
            ->sendNow();

        $this->assertEmpty(
            $mailer->getFailedRecipients()
        );
    }
}
