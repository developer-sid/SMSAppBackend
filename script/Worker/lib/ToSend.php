<?php
/**
 * Created by SiD
 * Date: 04/03/15
 * Time: 2:11 PM
 */

namespace Worker\lib;


/**
 * @Entity @Table(name="to_send")
 **/
class ToSend{

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $pid;

    /** @Column(type="string") **/
    protected $Mobile;

    /** @Column(type="string") **/
    protected $MobiGroup;

    /** @Column(type="string") **/
    protected $Company;

    /** @Column(type="integer") **/
    protected $fk_MediaID;

    /** @Column(type="integer") **/
    protected $prioritylevel;

    public function getPid(){
        return $this->pid;
    }

    public function getMobile(){
        return $this->Mobile;
    }

    public function setMobile($mobile){
        $this->Mobile = $mobile;
    }

    public function getMobiGroup(){
        return $this->MobiGroup;
    }

    public function setMobiGroup($mobiGroup){
        $this->MobiGroup = $mobiGroup;
    }

    public function getCompany(){
        return $this->Company;
    }

    public function setCompany($company){
        $this->Company = $company;
    }

    public function getfk_MediaID(){
        return $this->fk_MediaID;
    }

    public function setfk_MediaID($fkMediaID){
        $this->fk_MediaID = $fkMediaID;
    }

    public function getprioritylevel(){
        return $this->prioritylevel;
    }

    public function setprioritylevel($priorityLevel){
        $this->prioritylevel = $priorityLevel;
    }
}