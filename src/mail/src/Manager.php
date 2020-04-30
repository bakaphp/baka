<?php

namespace Baka\Mail;

use Phalcon\Mvc\View\Engine\Volt;

/**
 * Class Manager.
 *
 * @package Phalcon\Manager
 */
class Manager extends \Phalcon\Mailer\Manager
{
    protected $queue;

    /**
     *  Overwrite this funciton to use ower mail message.
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
        $this->registerQueue();
    }

    /**
     * Register the queue service.
     *
     * @return BeanstalkExtended
     */
    public function registerQueue()
    {
        $this->queue = $this->getDI()->get('queue');
    }

    /**
     * Get the queue service.
     *
     * @return BeanstalkExtended
     */
    public function getQueue()
    {
        return $this->queue;
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
    public function setRenderView($viewPath, $params)
    {
        //Set volt tempalte enging and specify the cache path
        $this->setViewEngines([
            '.volt' => function ($view = null) {
                $volt = new Volt($view);

                $volt->setOptions([
                    'compiledPath' => APP_PATH . '/cache/volt/',
                    'compiledSeparator' => '_',
                    'compileAlways' => !$this->getDI()->get('config')->application->production,
                ]);

                return $volt;
            }
        ]);

        $view = $this->getView();

        $content = $view->render($viewPath, $params);
        return $content;
    }
}
