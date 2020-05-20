<?php

namespace Baka\Mail;

use Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;
use Phalcon\Queue\Beanstalk\Job;
use Swift_Mime_Message;
use Throwable;

trait JobTrait
{
    /**
     * @description("Email queue")
     *
     * @param({'type'='string', 'name'='queueName', 'description'='name of the queue , default email_queue' })
     *
     * @return void
     */
    public function mailQueueAction($queueName)
    {
        $this->validateConfigurations();

        //call queue
        $queue = new BeanstalkExtended([
            'host' => $this->config->beanstalk->host,
            'prefix' => $this->config->beanstalk->prefix,
        ]);

        //dependent variables
        $config = $this->config;
        $di = \Phalcon\DI\FactoryDefault::getDefault();

        //call queue tube
        $queue->addWorker($queueName[0], function (Job $job) use ($di, $config) {
            try {
                $jobBody = $job->getBody();
                $auth = null;

                //if its a array then we know we are getting more settings
                if (is_array($jobBody) && array_key_exists('auth', $jobBody) && array_key_exists('message', $jobBody)) {
                    $message = $jobBody['message'];
                    $auth = $jobBody['auth'];
                } else {
                    //its just normal email
                    $message = $jobBody;
                }

                //vaalidate
                if (!$message instanceof Swift_Mime_Message) {
                    $this->log->error('Something went wrong with the message we are trying to send ', [$message]);
                    return;
                }

                //email auth settings
                $username = $config->email->username;
                $password = $config->email->password;
                $host = $config->email->host;
                $port = $config->email->port;

                //if get the the auth we need ot overwrite it
                if ($auth) {
                    $username = $auth['username'];
                    $password = $auth['password'];

                    //ovewrite host
                    if (array_key_exists('host', $auth)) {
                        $host = $auth['host'];
                    }

                    //ovewrite port
                    if (array_key_exists('port', $auth)) {
                        $port = $auth['port'];
                    }
                }

                //email configuration
                $transport = \Swift_SmtpTransport::newInstance($host, $port);

                $transport->setUsername($username);
                $transport->setPassword($password);

                $swift = \Swift_Mailer::newInstance($transport);

                $failures = [];
                if ($recipients = $swift->send($message, $failures)) {
                    $this->log->info('EmailTask Message successfully sent to:', [$message->getTo()]);
                } else {
                    $this->log->error('EmailTask There was an error: ', [$failures]);
                }
            } catch (Throwable $e) {
                $this->log->error($e->getMessage());
                echo $e->getMessage() . "\n";
            }

            // It's very important to send the right exit code!
            exit(0);
        });

        // Start processing queues
        $queue->doWork();
    }

    /**
     * Runs all the configuration validations needed
     *
     * @return void
     */
    protected function validateConfigurations()
    {
        //$this->validateQueueNameConfiguration();
        $this->validateBeanstalkConfiguration();
        $this->validateEmailConfiguration();
    }

    /**
     * Validates whether a queue name was provided or not
     *
     * @return void
     */
    protected function validateQueueNameConfiguration()
    {
        if (empty($queueName)) {
            echo "\nYou have to define a queue name.\n\n";
            return;
        }
    }

    /**
     * Validates whether beanstlak is configured or not
     *
     * @return void
     */
    protected function validateBeanstalkConfiguration()
    {
        if (!is_object($this->config->beanstalk)) {
            echo "\nNeed to configure beanstalkd on your phalcon configuration.\n\n";
            return;
        }
    }

    /**
     * Validates whether email is configured or not
     *
     * @return void
     */
    protected function validateEmailConfiguration()
    {
        if (!is_object($this->config->email)) {
            echo "\nNeed to configure email on your phalcon configuration.\n\n";
            return;
        }
    }
}
