<?php

namespace Baka\Mail\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Jobs\Job;
use Baka\Mail\Message;
use Phalcon\Di;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mail extends Job implements QueueableJobInterface
{
    protected object $config;
    protected array $options;
    protected Swift_Message $message;

    /**
     * Setup the mail job construct.
     *
     * @param Swift_Message $message
     * @param array $options
     */
    public function __construct(Swift_Message $message, array $options = [])
    {
        $this->message = $message;
        $this->config = $options['config'];
        $this->options = $options['options'];
    }

    /**
     * Handle sending the email.
     *
     * @return bool
     */
    public function handle()
    {
        $username = $this->config->email['username'];
        $password = $this->config->email['password'];
        $host = $this->config->email['host'];
        $port = $this->config->email['port'];

        //if get the the auth we need ot overwrite it
        if (isset($this->options['auth'])) {
            $auth = $this->options['auth'];
            $username = $auth['username'];
            $password = $auth['password'];

            //overwrite host
            if (array_key_exists('host', $auth)) {
                $host = $auth['host'];
            }

            //overwrite port
            if (array_key_exists('port', $auth)) {
                $port = $auth['port'];
            }
        }

        //email configuration
        $transport = Swift_SmtpTransport::newInstance($host, $port);

        $transport->setUsername($username);
        $transport->setPassword($password);

        $swift = Swift_Mailer::newInstance($transport);
        $failures = [];
        if ($swift->send($this->message, $failures)) {
            Di::getDefault()->get('log')->info('Email successfully sent to', [$this->message->getTo()]);
        } else {
            Di::getDefault()->get('log')->error('Email error sending ', [$failures]);
        }

        return true;
    }
}
