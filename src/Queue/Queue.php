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
    const EXCHANGE_PREFIX = 'baka_exchange';
    const EXCHANGE_TYPE = 'direct';


    private static bool $passive = false;
    private static bool $durable = true;
    private static bool $exclusive = false;
    private static bool $auto_delete = false;
    private static bool $noWait = false;

    /**
     * Dechare an exchange on the channel.
     *
     * @param string $name
     * @param string $type
     * @param bool $force
     *
     * @return void
     */
    public static function declareExchange(string $name, bool $force = false) : void
    {
        $queue = Di::getDefault()->get('queue');

        $channel = $queue->channel();

        if ($force) {
            $channel->exchange_delete($name);
        }

        $channel->exchange_declare(
            $name,
            self::EXCHANGE_TYPE,
            self::getPassive(),
            self::getDurable(),
            self::getExclusive(),
            self::getAutoDelete(),
            self::getNoWait()
        );

        $channel->close();
    }

    /**
     * Dechare a queue on the channel.
     *
     * @param string $name
     * @param string $exchange
     * @param bool $force
     * @param int $delay
     *
     * @return void
     */
    public static function declareQueue(string $name, string $exchange, bool $force = false, int $delay = 0) : void
    {
        $queue = Di::getDefault()->get('queue');

        $channel = $queue->channel();

        if ($force) {
            $channel->queue_delete($name);
        }

        $args = new AMQPTable();
        $args->set('x-dead-letter-exchange', $exchange);

        if ($delay > 0) {
            $args->set('x-message-ttl', $delay);
        }

        /*
            name: $name
            passive: false
            durable: true // The queue will survive server restarts.
            exclusive: false // The queue can be accessed in other channels.
            auto_delete: false // The queue won't be deleted once the channel is closed.
            nowait: false // The client should not wait for a reply.
        */

        $channel->queue_declare(
            $name,
            self::getPassive(),
            self::getDurable(),
            self::getExclusive(),
            self::getAutoDelete(),
            self::getNoWait(),
            $args
        );

        $channel->close();
    }

    /**
     * Creates all the exchanges and queues for a given name.
     *
     * @param string $queueName
     * @param bool $force
     *
     * @return void
     */
    public static function createFlow(string $queueName, bool $force = false) : void
    {
        $delay = (int) envValue('QUEUE_RETRY_DELAY', 0);

        if ($delay > 0) {
            self::createFlowWithDelay($queueName, $delay, $force);
            return;
        }

        $queue = Di::getDefault()->get('queue');

        $channel = $queue->channel();

        $exchange = self::getExchangeName($queueName);

        if ($force) {
            $channel->exchange_delete($exchange);
            $channel->queue_delete($queueName);
        }

        self::declareExchange($exchange, $force);

        self::declareQueue($queueName, $exchange, $force);

        $channel->queue_bind($queueName, $exchange, $queueName);
    }

    public static function createFlowWithDelay(string $queueName, int $delay, bool $force = false) : void
    {
        $queue = Di::getDefault()->get('queue');

        $channel = $queue->channel();

        $mainExchange = self::getExchangeName($queueName);
        $nackHandleExchange = "{$mainExchange}.nack_handle";
        $requeueHandleExchange = "{$mainExchange}.requeue_handle";

        $waitQueueName = "{$queueName}.wait_queue";

        if ($force) {
            $channel->exchange_delete($mainExchange);
            $channel->exchange_delete($nackHandleExchange);
            $channel->exchange_delete($requeueHandleExchange);

            $channel->queue_delete($queueName);
            $channel->queue_delete($waitQueueName);
        }

        self::declareExchange($mainExchange, $force);
        self::declareExchange($nackHandleExchange, $force);
        self::declareExchange($requeueHandleExchange, $force);

        self::declareQueue($queueName, $nackHandleExchange, $force);
        self::declareQueue($waitQueueName, $requeueHandleExchange, $force, $delay);

        $channel->queue_bind($queueName, $mainExchange, $queueName);
        $channel->queue_bind($queueName, $requeueHandleExchange, $queueName);
        $channel->queue_bind($waitQueueName, $nackHandleExchange, $queueName);
    }

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

        $msg = new AMQPMessage($msg, [
            'delivery_mode' => 2
        ]);

        self::createFlow($name);

        $exchange = self::getExchangeName($name);

        $channel->basic_publish($msg, $exchange, $name);

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
    public static function process(string $queueName, callable $callback, bool $force = false) : void
    {
        $queue = Di::getDefault()->get('queue');
        Di::getDefault()->get('log')->info('Starting Queue ' . $queueName);

        /**
         * Use Swoole Coroutine.
         */
        $channel = $queue->channel();

        self::createFlow($queueName, $force);

        //Fair dispatch https://lukasmestan.com/rabbitmq-broken-pipe-or-closed-connection/
        $prefetchSize = null;    // message size in bytes or null, otherwise error
        $prefetchCount = 1;      // prefetch count value
        $applyPerChannel = null; // can be false or null, otherwise error

        $channel->basic_qos($prefetchSize, $prefetchCount, $applyPerChannel);

        /*
            queueName: Queue from where to get the messages.
            consumer_tag: Consumer identifier.
            no_local: Don't receive messages published by this consumer.
            no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue.
            nowait: The client should not wait for a reply.
            callback: A PHP Callback.
        */
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $queue->close();
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
     * Set queue $noWait config value.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function setNoWait(bool $value) : void
    {
        self::$noWait = $value;
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

    /**
     * Get queue $noWait config value.
     *
     * @return bool
     */
    public static function getNoWait() : bool
    {
        return self::$noWait;
    }

    /**
     * Get the exchange name fot a given queue.
     *
     * @return string
     */
    public static function getExchangeName($queueName) : string
    {
        return self::EXCHANGE_PREFIX . ".{$queueName}";
    }
}
