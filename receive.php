<?php
    /**
     * RabbitMQ Consumer. to run type in terminal 'php receive.php'
     * System is waiting for messages until closed by 'CTRL+C'
     */
    
    require_once __DIR__ . '/vendor/autoload.php';

    use PhpAmqpLib\Connection\AMQPStreamConnection;

    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

    $channel = $connection->channel();
    $channel->queue_declare('sms_queue', false, false, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

    $callback = function ($msg) {
        echo " [x] Received ", $msg->body, "\n";
    };

    $channel->basic_consume('sms_queue', '', false, true, false, false, $callback);
    while (count($channel->callbacks)) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
?>