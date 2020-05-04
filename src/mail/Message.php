<?php

namespace Baka\Mail;

use Exception;
use Swift_SmtpTransport;
use Swift_Mailer;

/**
 * Class Message.
 *
 * @package Phalcon\Mailer
 */
class Message extends \Phalcon\Mailer\Message
{
    protected $queueName = 'email_queue';
    protected $viewPath = null;
    protected $params = [];
    protected $viewsDirLocal = null;
    protected $smtp = null;
    protected $auth = false;

    /**
     * Set the body of this message, either as a string, or as an instance of
     * {@link \Swift_OutputByteStream}.
     *
     * @param mixed $content
     * @param string $contentType optional
     * @param string $charset     optional
     *
     * @return $this
     *
     * @see \Swift_Message::setBody()
     */
    public function content($content, $contentType = self::CONTENT_TYPE_HTML, $charset = null)
    {
        if ($this->params) {
            $content = $this->setDynamicContent($this->params, $content);
        }

        $this->getMessage()->setBody($content, $contentType, $charset);

        return $this;
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * Events:
     * - mailer:beforeSend
     * - mailer:afterSend
     *
     * @return int
     *
     * @see \Swift_Mailer::send()
     */
    public function send()
    {
        $eventManager = $this->getManager()->getEventsManager();
        if ($eventManager) {
            $result = $eventManager->fire('mailer:beforeSend', $this);
        } else {
            $result = true;
        }

        if ($result === false) {
            return false;
        }

        $this->failedRecipients = [];

        //send to queue
        $queue = $this->getManager()->getQueue();

        if ($this->auth) {
            $queue->putInTube($this->queueName, [
                'message' => $this->getMessage(),
                'auth' => $this->smtp,
            ]);
        } else {
            $queue->putInTube($this->queueName, $this->getMessage());
        }

    }

    /**
     * Send message instantly, without a queue.
     *
     * @return void
     */
    public function sendNow()
    {
        $config = $this->getManager()->getDI()->getConfig();
        $message = $this->getMessage();

        $username = $config->email->username;
        $password = $config->email->password;
        $host = $config->email->host;
        $port = $config->email->port;

        $transport = (new Swift_SmtpTransport($host, $port))
                        ->setUsername($username)
                        ->setPassword($password);

        $swift = new Swift_Mailer($transport);

        $failures = [];

        $swift->send($message, $failures);
    }

    /**
     * Overwrite the baka SMTP connection for this current email.
     *
     * @param  array  $smtp
     * @return this
     */
    public function smtp(array $params)
    {
        //validate the user params
        if (!array_key_exists('username', $params)) {
            throw new Exception('We need a username');
        }

        if (!array_key_exists('password', $params)) {
            throw new Exception('We need a password');
        }

        $this->smtp = $params;
        $this->auth = true;

        return $this;
    }

    /**
     * Set the queue name if the user wants to shange it.
     *
     * @param string $queuName
     *
     * @return $this
     */
    public function queue(string $queue)
    {
        $this->queueName = $queue;
        return $this;
    }

    /**
     * Set variables to views.
     *
     * @param string $params
     *
     * @return $this
     */
    public function params(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * The local path to the folder viewsDir only this message. (OPTIONAL).
     *
     * @param string $dir
     *
     * @return $this
     */
    public function viewDir(string $dir)
    {
        $this->viewsDirLocal = $dir;
        return $this;
    }

    /**
     * view relative to the folder viewsDir (REQUIRED).
     *
     * @param string $template
     *
     * @return $this
     */
    public function template($template = 'email.volt')
    {
        $this->viewPath = $template;

        $content = $this->getManager()->setRenderView($this->viewPath, $this->params);

        $this->getMessage()->setBody($content, self::CONTENT_TYPE_HTML);

        return $this;
    }

    /**
     * Set content dynamically by params.
     * @param $params
     * @param $content
     * @return string
     */
    public function setDynamicContent(array $params, string $content)
    {
        $processed_content = preg_replace_callback(
            '~\{(.*?)\}~si',
            function ($match) use ($params) {
                return str_replace($match[0], isset($params[$match[1]]) ? $params[$match[1]] : $match[0], $match[0]);
            },
            $content
        );

        return $processed_content;
    }
}
