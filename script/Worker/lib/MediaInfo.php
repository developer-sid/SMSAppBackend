<?php
/**
 * Created by SiD
 * Date: 04/03/15
 * Time: 2:11 PM
 */

namespace Worker\lib;


/**
 * @Entity @Table(name="mediainfo")
 **/
class MediaInfo{

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $MediaId;

    /** @Column(type="string") **/
    protected $PathORcotnet;

    /** @Column(type="integer") **/
    protected $Type;

    /** @Column(type="integer") **/
    protected $user_id;

    /** @Column(type="string") **/
    public $processStatus;

    /** @Column(type="string") **/
    public $logMessage;

    public function getMediaId(){
        return $this->MediaId;
    }

    public function setMediaId($mediaId){
        $this->MediaId = $mediaId;
    }

    public function getPathORcotnet(){
        return $this->PathORcotnet;
    }

    public function getType(){
        return $this->Type;
    }

    public function getuser_id(){
        return $this->user_id;
    }

    public function setuser_id($userId){
        $this->user_id = $userId;
    }

    public function getProcessStatus(){
        return $this->processStatus;
    }

    public function setProcessStatus($processStatus){
        $this->processStatus = $processStatus;
    }

    public function getLogMessage(){
        return $this->logMessage;
    }

    public function setLogMessage($logMessage){
        $this->logMessage = $logMessage;
    }
}