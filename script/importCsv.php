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

    $config['logger']->info('ImportCsv.Connected');  // write to log file

    // rabbit connection
    $connections = array(
        'default' => new PhpAmqpLib\Connection\AMQPLazyConnection($config['rabbit_host'], $config['rabbit_port'], $config['rabbit_user'], $config['rabbit_pass'], $config['rabbit_vhost'])
    );
    $thumper_registry = new Thumper\ConnectionRegistry($connections, 'default');

    $fn = function($msg)
    {
        global $config, $campaignFileRepo, $entityManager, $toSendRepo;

        $config['logger']->info('ImportCsv.Message consumed');   // write to log file

        $request = json_decode($msg, true); // decode consumed message

        // get the file data
        if($data = $campaignFileRepo->find($request['id'])){

            // update process status
            $data->logMessage = 'Process started :|';
            $data->importStatus = 'Processing';
            $entityManager->persist($data);
            $entityManager->flush();

            $csvFile = $data->getCampName();

            if(!empty($csvFile)){

                // get values from db
                $fkMediaID = $data->getfk_MediaID();
                $userId = $data->getuserid();
                $priorityLevel = $request['priority'];
                $expFile = explode('.', $csvFile);
                $mobiGroup = $expFile[0];

                if(($handle = fopen($config['csv_folder'] . $csvFile, "r")) !== FALSE){ // open csv file

                    while(($numbers = fgetcsv($handle, 1000, ",")) !== FALSE){  // read contents

                        foreach($numbers as $number){

                            if(!empty($number)){

                                if(substr($number, 0, 2) != '91'){
                                    $number = '91' . $number;   // add country code with mobile number
                                }
                                // check the mobile number already in db
                                $numCheck = $toSendRepo->findOneBy(
                                    array(
                                        'Company'   =>  $userId,
                                        'Mobile'    =>  $number
                                    )
                                );

                                if(empty($numCheck)){
                                    // save to db table
                                    $event = new Worker\lib\ToSend();
                                    $event->setMobile($number);
                                    $event->setMobiGroup($mobiGroup);
                                    $event->setCompany($userId);
                                    $event->setfk_MediaID($fkMediaID);
                                    $event->setprioritylevel($priorityLevel);
                                    $entityManager->persist($event);
                                    $entityManager->flush();
                                    unset($event, $number);
                                }
                            }
                        }
                    }
                    unset($handle, $numbers, $prorityLevel);

                    // importing process completed
                    $data->logMessage = 'Process successfully completed :)';
                    $data->importStatus = 'Done';
                    $entityManager->persist($data);
                    $entityManager->flush();
                }
                else{
                    // error in opening csv file
                    $data->logMessage = 'Error in opening the file :(';
                    $data->importStatus = 'Failed';
                    $entityManager->persist($data);
                    $entityManager->flush();
                }
            }
            else{
                // import failed
                $data->logMessage = 'Empty file name :(';
                $data->importStatus = 'Failed';
                $entityManager->persist($data);
                $entityManager->flush();
            }
            unset($csvFile, $data);
        }

        unset($config, $campaignFileRepo, $entityManager, $request);
    };

    // create conusmer object
    $consumer = new Thumper\Consumer($thumper_registry->getConnection());
    // set exchange details
    $consumer->setExchangeOptions(array(
        'name' => 'smsapp.exchange',
        'type' => 'topic'));
    $consumer->setQueueOptions(array('name' =>  'smsapp.import.csv.queue'));    // queue details
    $consumer->setQos(array(
        'prefetch_size' => null,
        'prefetch_count' => 1,
        'global' => null
    ));
    $consumer->setRoutingKey('smsapp.import.csv.key');  // routing key
    $consumer->setCallback($fn);
    $consumer->consume(0);
}


while(true) {

    try {
        connect();
    } catch (Exception $e) {
        $config['logger']->err('ImportCsv.Connection to rabbit lost', array($e->getMessage()));  // write to log file
    }

    //for some reason we are here lets wait and repeat
    $config['logger']->warn('ImportCsv.Sleeping for ' . $config['rabbit_reconnection_time']);    // write to log file
    sleep($config['rabbit_reconnection_time']);
}
