<?php
/**
 * Created by SiD
 * Date: 04/03/15
 * Time: 2:11 PM
 */

namespace Worker\lib;


/**
 * @Entity @Table(name="campaignfiles")
 **/
class CampaignFile{

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $campid;

    /** @Column(type="string") **/
    protected $CampName;

    /** @Column(type="integer") **/
    protected $userid;

    /** @Column(type="integer") **/
    protected $currentqueue;

    /** @Column(type="integer") **/
    protected $fk_MediaID;

    /** @Column(type="string") **/
    public $importStatus;

    /** @Column(type="string") **/
    public $logMessage;

    public function getCampid(){
        return $this->campid;
    }

    public function setCampid($campId){
        $this->campid = $campId;
    }

    public function getCampName(){
        return $this->CampName;
    }

    public function setCampName($campName){
        $this->CampName = $campName;
    }

    public function getfk_MediaID(){
        return $this->fk_MediaID;
    }

    public function getuserid(){
        return $this->userid;
    }

    public function getImportStatus(){
        return $this->importStatus;
    }

    public function setImportStatus($importStatus){
        $this->importStatus = $importStatus;
    }

    public function getLogMessage(){
        return $this->logMessage;
    }

    public function setLogMessage($logMessage){
        $this->logMessage = $logMessage;
    }
}