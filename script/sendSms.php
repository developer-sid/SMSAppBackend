<?php
/**
 * Created by SiD
 * Date: 03/03/15
 * Time: 9:51 PM
 */

require_once dirname(__DIR__) . '/bootstrap.php';

function connect()
{
    global $config, $thumper_registry;

    $config['logger']->info('SendSms.Connected');   // write to log file

    // create rabbit connection
    $connections = array(
        'default' => new PhpAmqpLib\Connection\AMQPLazyConnection($config['rabbit_host'], $config['rabbit_port'], $config['rabbit_user'], $config['rabbit_pass'], $config['rabbit_vhost'])
    );
    $thumper_registry = new Thumper\ConnectionRegistry($connections, 'default');

    $fn = function($msg)
    {
        global $config;

        $config['logger']->info('SendSms.Message consumed');    // write to log file

        $request = json_decode($msg, true);  // decode consumed message

        /*
            SMS gateway api call
        */

        unset($request, $config);

    };

    // create consuemer object
    $consumer = new Thumper\Consumer($thumper_registry->getConnection());
    $consumer->setExchangeOptions(array(
        'name' => 'smsapp.exchange',
        'type' => 'topic'));
    $consumer->setQueueOptions(array('name' =>  'smsapp.send.sms.queue'));  // queue
    $consumer->setQos(array(
        'prefetch_size' => null,
        'prefetch_count' => 1,
        'global' => null
    ));
    $consumer->setRoutingKey('smsapp.send.sms.key');    // routing key
    $consumer->setCallback($fn);
    $consumer->consume(0);
}


while(true) {

    try {
        connect();
    } catch (Exception $e) {
        $config['logger']->err('SendSms.Connection to rabbit lost', array($e->getMessage()));   // write to log file
    }

    //for some reason we are here lets wait and repeat
    $config['logger']->warn('SendSms.Sleeping for ' . $config['rabbit_reconnection_time']); // write to log file
    sleep($config['rabbit_reconnection_time']);
}
