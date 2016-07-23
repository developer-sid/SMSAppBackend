<?php
/**
 * Created by SiD 
 * Date: 06/03/15
 * Time: 10:14 AM
 */

namespace Worker\lib;


class Rabbit{

    private static $registry;
    private static $connections;
    private static $connection;
    private static $channel;

    private static function connect(){
        global $config;

        self::$connection = new \PhpAmqpLib\Connection\AMQPLazyConnection($config['rabbit_host'], $config['rabbit_port'], $config['rabbit_user'], $config['rabbit_pass'], $config['rabbit_vhost']);
        self::$channel = self::$connection->channel();

        self::$connections = array(
            'default' => self::$connection
        );

        self::$registry = new \Thumper\ConnectionRegistry(self::$connections, 'default');
    }

    public static function importNumbersFromCsv($options){
        global $config;

        self::connect();

        $producer = new \Thumper\Producer(self::$registry->getConnection());

        $json = json_encode($options);

        $producer->setExchangeOptions(array(
            'name' => 'smsapp.exchange',
            'type' => 'topic'));
        $producer->setQueueOptions(array(
            'name'  =>  'smsapp.import.csv.queue'
        ));
        $producer->publish($json, 'smsapp.import.csv.key');

        self::disconnect();
        unset($producer);
    }

    public static function sendSms($options){
        global $config;

        self::connect();

        $producer = new \Thumper\Producer(self::$registry->getConnection());

        $json = json_encode($options);

        $producer->setExchangeOptions(array(
            'name' => 'smsapp.exchange',
            'type' => 'topic'));
        $producer->setQueueOptions(array(
            'name'  =>  'smsapp.get.numbers.queue'
        ));
        $producer->publish($json, 'smsapp.get.numbers.key');

        self::disconnect();
        unset($producer);
    }

    public static function disconnect(){
        self::$channel->close();
        self::$connection->close();
    }

}