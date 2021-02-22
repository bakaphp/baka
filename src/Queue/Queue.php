<?php

declare(strict_types=1);

namespace Baka\Queue;

use Phalcon\Di;
use PhpAmqpLib\Message\AMQPMessage;

class Queue
{
    /**
     * default canvas queues system name.
     */
    const EVENTS = 'events';
    const NOTIFICATIONS = 'notifications';
    const JOBS = 'jobs';

    public static bool $passive = false;
    public static bool $durable = true;
    public static bool $exclusive = false;
    public static bool $auto_delete = false;

    /**
     * Send a msg to Queue.
     *
     * @param string $name
     * @param array|object|mixed $msg
     *
     * @return bool
     */
    public static function send(string $name, $msg) : bool
    {
        $queue = Di::getDefault()->get('queue');

        $channel = $queue->channel();

        /*
            name: $queueName
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */

        $channel->queue_declare(
            $name,
            self::getPassive(),
            self::getDurable(),
            self::getExclusive(),
            self::getAutoDelete()
        );

        $msg = new AMQPMessage($msg, [
            'delivery_mode' => 2
        ]);

        $channel->basic_publish($msg, '', $name);
        $channel->close();

        return true;
    }

    /**
     * Process a specify queue.
     *
     * @param string $queueName
     * @param callable $callback
     *
     * @return void
     */
    public static function process(string $queueName, callable $callback) : void
    {
        $queue = Di::getDefault()->get('queue');
        Di::getDefault()->get('log')->info('Starting Queue ' . $queueName);

        /**
         * Use Swoole Coroutine.
         */
        go(function () use ($queue, $queueName, $callback) {
            $channel = $queue->channel();

            $channel->queue_declare($queueName, false, true, false, false);

            //Fair dispatch https://lukasmestan.com/rabbitmq-broken-pipe-or-closed-connection/
            $prefetchSize = null;    // message size in bytes or null, otherwise error
            $prefetchCount = 1;      // prefetch count value
            $applyPerChannel = null; // can be false or null, otherwise error

            $channel->basic_qos($prefetchSize, $prefetchCount, $applyPerChannel);

            /*
                queueName: Queue from where to get the messages
                consumer_tag: Consumer identifier
                no_local: Don't receive messages published by this consumer.
                no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
                exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
                nowait:
                callback: A PHP Callback
            */
            $channel->basic_consume($queueName, '', false, true, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $queue->close();
        });
    }

    /**
     * Set queue $passive config value.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function setPassive(bool $value) : void
    {
        self::$passive = $value;
    }

    /**
     * Set queue $auto_delete config value.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function setAutoDelete(bool $value) : void
    {
        self::$auto_delete = $value;
    }

    /**
     * Set queue $exclusive config value.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function setExclusive(bool $value) : void
    {
        self::$exclusive = $value;
    }

    /**
     * Set queue $durable config value.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function setDurable(bool $value) : void
    {
        self::$durable = $value;
    }

    /**
     * Get queue $passive config value.
     *
     * @return bool
     */
    public static function getPassive() : bool
    {
        return self::$passive;
    }

    /**
     * Get queue $auto_delete config value.
     *
     * @return bool
     */
    public static function getAutoDelete() : bool
    {
        return self::$auto_delete;
    }

    /**
     * Get queue $exclusive config value.
     *
     * @return bool
     */
    public static function getExclusive() : bool
    {
        return self::$exclusive;
    }

    /**
     * Get queue $durable config value.
     *
     * @return bool
     */
    public static function getDurable() : bool
    {
        return self::$durable;
    }
}
