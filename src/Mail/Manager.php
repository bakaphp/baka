<?php

namespace Baka\Mail;

use Exception;
use Phalcon\Di;
use Phalcon\Mailer\Manager as ManagerPhalcon;

class Manager extends ManagerPhalcon
{
    /**
     *  Overwrite this function.
     *
     *  Create a new Message instance.
     *
     * Events:
     * - mailer:beforeCreateMessage
     * - mailer:afterCreateMessage
     *
     * @return \Phalcon\Mailer\Message
     */
    public function createMessage()
    {
        $eventsManager = $this->getEventsManager();
        if ($eventsManager) {
            $eventsManager->fire('mailer:beforeCreateMessage', $this);
        }

        /** @var $message Message */
        $message = $this->getDI()->get('\Baka\Mail\Message', [$this]);
        if (($from = $this->getConfig('from'))) {
            $message->from($from['email'], isset($from['name']) ? $from['name'] : null);
        }

        if ($eventsManager) {
            $eventsManager->fire('mailer:afterCreateMessage', $this, $message);
        }
        return $message;
    }

    /**
     * Configure MailerManager class.
     *
     * @param array $config
     *
     * @see \Phalcon\Mailer\Manager::registerSwiftTransport()
     * @see \Phalcon\Mailer\Manager::registerSwiftMailer()
     */
    protected function configure(array $config)
    {
        $this->config = $config;

        $this->registerSwiftTransport();
        $this->registerSwiftMailer();
    }

    /**
     * Get the configuration.
     *
     * @return object
     */
    public function getConfigure() : object
    {
        return (object) $this->getConfig();
    }

    /**
     * Renders a view.
     *
     * @param $viewPath
     * @param $params
     * @param null $viewsDir
     *
     * @return string
     */
    public function setRenderView(string $viewPath, array $params) : string
    {
        if (!Di::getDefault()->has('view')) {
            throw new Exception('No view service define , please add a view service to your provider');
        }

        $view = Di::getDefault()->get('view');

        $content = $view->render($viewPath, $params);
        return $content;
    }
}
