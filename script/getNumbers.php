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

    $config['logger']->info('GetNumbers.Connected');     // write to log file

    // rabbit connection
    $connections = array(
        'default' => new PhpAmqpLib\Connection\AMQPLazyConnection($config['rabbit_host'], $config['rabbit_port'], $config['rabbit_user'], $config['rabbit_pass'], $config['rabbit_vhost'])
    );
    $thumper_registry = new Thumper\ConnectionRegistry($connections, 'default');

    $fn = function($msg)
    {
        global $config, $campaignFileRepo, $entityManager, $toSendRepo, $thumper_registry, $MediaInfoRepo;

        $config['logger']->info('GetNumbers.Message consumed'); // write to log file

        $request = json_decode($msg, true); // decode consumed message

        // get the mobile numbers from db
        $mobileNumbers = $toSendRepo->findBy(
            array(
                'Company' => $request['userId']
            )
        );

        if(!empty($mobileNumbers)){

            // get sms data
            $mediaData = $MediaInfoRepo->find($request['mediaId']);

            if(!empty($mediaData)){

                // update status
                $mediaData->logMessage = 'Process started :|';
                $mediaData->processStatus = 'Processing';
                $entityManager->persist($mediaData);
                $entityManager->flush();

                $messageContent = $mediaData->getPathORcotnet();    // sms content
                $type = $mediaData->getType();  // message type

                foreach($mobileNumbers as $number){
                    $mobileNumber = $number->getMobile();

                    $config['logger']->info('GetNumbers.Producing message for ' . $mobileNumber); // write to log file

                    // create producer object
                    $producer = new Thumper\Producer($thumper_registry->getConnection());

                    // create json object with data
                    $json = json_encode(array(
                                'mobileNumber'      =>  $mobileNumber,
                                'messageContent'    =>  $messageContent,
                                'type'              =>  $type,
                                'mediaId'           =>  $request['mediaId']
                            ));

                    // produce another message
                    $producer->setExchangeOptions(array(
                            'name' => 'smsapp.exchange',
                            'type' => 'topic'));
                    $producer->setQueueOptions(array('name' =>  'smsapp.send.sms.queue')); // queue
                    $producer->publish($json, 'smsapp.send.sms.key');

                    unset($mobileNumber, $m, $json, $producer);
                }

                // update status
                $mediaData->logMessage = 'Process successfully completed :)';
                $mediaData->processStatus = 'Done';
                $entityManager->persist($mediaData);
                $entityManager->flush();

                unset($messageContent, $type);
            }
        }
        unset($mobileNumbers);
    };

    // create consuer object
    $consumer = new Thumper\Consumer($thumper_registry->getConnection());
    $consumer->setExchangeOptions(array(
        'name' => 'smsapp.exchange',
        'type' => 'topic'));
    $consumer->setQueueOptions(array('name' =>  'smsapp.get.numbers.queue'));   // queue info
    $consumer->setQos(array(
        'prefetch_size' => null,
        'prefetch_count' => 1,
        'global' => null
    ));
    $consumer->setRoutingKey('smsapp.get.numbers.key'); // routing key
    $consumer->setCallback($fn);
    $consumer->consume(0);
}


while(true) {

    try {
        connect();
    } catch (Exception $e) {
        $config['logger']->err('GetNumbers.Connection to rabbit lost', array($e->getMessage()));     // write to log file
    }
    //for some reason we are here lets wait and repeat
    $config['logger']->warn('GetNumbers.Sleeping for ' . $config['rabbit_reconnection_time']);
    sleep($config['rabbit_reconnection_time']);
}
