<?php

namespace Baka\Mail;

use function Baka\appPath;
use Phalcon\Mailer\Manager as ManagerPhalcon;
use Phalcon\Mvc\View\Engine\Volt;

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
        //Set volt template engine and specify the cache path
        $di = $this->getDI();
        $this->setViewEngines([
            '.volt' => function ($view = null) use ($di) {
                $volt = new Volt($view);

                $volt->setOptions([
                    'path' => appPath('/cache/volt/'),
                    'separator' => '_',
                    'always' => $di->has('config') ? !$di->get('config')->application->production : true,
                ]);

                return $volt;
            }
        ]);

        $view = $this->getView();

        $content = $view->render($viewPath, $params);
        return $content;
    }
}
