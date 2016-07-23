<?php
/**
 * Created by SiD 
 * Date: 04/03/15
 * Time: 12:31 PM
 */


require_once dirname(__DIR__) . '/backend/script/vendor/autoload.php';
require_once dirname(__DIR__) . '/backend/script/Worker/config/config.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
//use Doctrine\DBAL\DriverManager;

//use Worker\lib\ToSend;

$config =  Worker\lib\Registry::getInstance();

$paths = array("/entities");
$isDevMode = false;

$docConfig = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($config['database'], $docConfig);

$toSendRepo = $entityManager->getRepository('Worker\lib\ToSend');
$campaignFileRepo = $entityManager->getRepository('Worker\lib\CampaignFile');
$MediaInfoRepo = $entityManager->getRepository('Worker\lib\MediaInfo');

$thumper_registry = new stdClass();