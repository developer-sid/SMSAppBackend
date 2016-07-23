<?php
/**
 * Created by SiD 
 * Date: 04/03/15
 * Time: 12:15 PM
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$rootPath = realpath(dirname(__DIR__) . '/../../');

$config = array(
    'rabbit_host'               =>  'localhost',
    'rabbit_port'               =>  '5672',
    'rabbit_user'               =>  'sid',
    'rabbit_pass'               =>  'netone23',
    'rabbit_vhost'              =>  '/',
    'rabbit_reconnection_time'  =>  30,
    'logger'                    =>  '',
    'logger_name'               =>  'SmsApp',
    'log_file'                  =>  $rootPath . '/log/worker.log',
    'log_signal'                =>  Logger::DEBUG,
    'database'                  =>  array(
                                        'driver'   => 'pdo_mysql',
                                        'user'     => 'root',
                                        'password' => 'netone23',
                                        'dbname'   => 'SMSapp',
                                        'host'     => 'localhost',
                                        'port'     => '3307',
                                    ),
    'csv_folder'                =>  $rootPath . '/csv/'
);

// create a log channel
$log = new Logger($config['logger_name']);
$log->pushHandler(new StreamHandler($config['log_file'], $config['log_signal']));
$config['logger'] = $log;

$registry = \Worker\lib\Registry::getInstance();
foreach ($config as $k => $v) {
    $registry->offsetSet($k, $v);
}

$log->info('Configuration loaded');