<?php

namespace Programmers4u\gatesms\sms\xml\sender;

/*******************************************************************
*   SKRYPT WEBAPI (sms api) DO WYSYŁANIA SMS GATESMS.EU            *
*   Właścicielem i autorem skryptu jest Marcin Kania               *
*   http://www.gatesms.eu  2002 - 2011 Programmers4u               *                                         
********************************************************************/  
 
class SmsSender {

	public $to; 
    public $from; 
    public $isFlash; 
    public $login; 
    public $pass;
    public $pl; public $code;
    public $wap;
    public $test;
    public $speed;
    public $secure; 
    public $time;
    public $localId;
    public $localIdRandom;
    public $globalId;
    public $debug;
    public $raw; 
    public $part;
	public $modemID;
	
    private $smsConnect;
    private $msg;
    private $counter;
    private $stat;
                
    public function __construct()
    {
    	$this->msg=array();
    	$this->stat=array();
        $this->counter=0;
        $this->localId='';
        $this->isFlash="false";
        $this->from="";
        $this->wap="";
        $this->pl='false';   
        $this->code='gsm7'; //gsm7 | ucs2
        $this->test='false';  
        $this->speed='false';
        $this->secure=false;
        $this->time=time();
        $this->globalId=0;
        $this->debug=0;
        $this->localIdRandom=0;
        $this->raw="false";
        $this->part='';
        $this->modemID="";
        $this->smsConnect=null;
     }
        
     public function setTime($data='') {//eg. 2010-10-06 23:45:00
     	if($data) {
        	$d=explode(" ",$data);
            $h=explode(":",$d[1]);
            $d=explode("-",$d[0]);
            $this->time=mktime($h[0],$h[1],$h[2],$d[1],$d[2],$d[0]);
        };
     }
        
     public function AddMsg($msg) {
     	if($this->to=='') return 106;
     	if($this->localId=='' && $this->localIdRandom==0) return 301;
     	if($this->localIdRandom==1) $this->localId=substr(md5(microtime(true).mt_rand(100000,100000000)),0,10);
     	if($msg=='') return 103;
     	$msg=str_replace(array("+"," "),array("%2B","%20"),$msg);
     	$this->msg[$this->counter]="<msg time=\"".$this->time."\" speed=\"".$this->speed."\" wap=\"".$this->wap."\" pl=\"".$this->pl."\" isflash=\"".$this->isFlash."\" from=\"".$this->from."\" phone=\"".$this->to."\" localid=\"".$this->localId."\" raw=\"".$this->raw."\" modemid=\"".$this->modemID."\" code=\"".$this->code."\" part=\"".$this->part."\">".$msg."</msg>";
        $this->counter++;
     }

     public function AddStat() {
     	if($this->globalId=='' && $this->localId=='') return 301;
     	$id=($this->globalId!='') ? "globalid=\"".$this->globalId."\"" : "localid=\"".$this->localId."\"";  
     	$this->stat[$this->counter]="<status timestamp=\"".$this->time."\" ok=\"true\" ".$id."/>"; 
        $this->counter++;
     }
             
	private function Connection() {        	
     	$url=($this->secure)?"https://gatesms.eu/sms_xmlapi.php": "http://gatesms.eu/sms_xmlapi.php";
        if(!$this->smsConnect) { 
        	$this->smsConnect = curl_init();	
            if($this->secure) {
				curl_setopt($this->smsConnect, CURLOPT_SSL_VERIFYPEER, false); 	   
				curl_setopt($this->smsConnect, CURLOPT_SSL_VERIFYHOST, false);      	
            };
            curl_setopt($this->smsConnect, CURLOPT_POST, 1);
            curl_setopt($this->smsConnect, CURLOPT_USERAGENT, "SMSbot:(www.gatesms.eu)");
            curl_setopt($this->smsConnect, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->smsConnect, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($this->smsConnect, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->smsConnect, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($this->smsConnect, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($this->smsConnect, CURLOPT_HEADER, $this->debug);
            curl_setopt($this->smsConnect, CURLOPT_URL, $url);
        };	
	}
	
	public function callBack() {
		return stripslashes($_POST['body']);
	}
	
	public function GetStat() {
		$out="";
		$POST='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><report>';
		foreach($this->stat as $stat) $POST.=$stat;
		$POST.='</report>';

		$timestamp=time();
		$HASH=md5('POST/sms_xmlapi.php'.md5($POST).'Accept:application/xml'.$timestamp.$this->pass);
		$header=array();
		array_push($header,"X-GT-Auth: ".$this->login.":".$HASH);
		array_push($header,"X-GT-Timestamp: ".$timestamp);
		array_push($header,"Content-Type: application/x-www-form-urlencoded");
		array_push($header,"Accept: application/xml");
		array_push($header,"Expect: 100-continue");
		array_push($header,"X-GT-Action: GET STATUS");
		$fields="body=".urlencode($POST);
			
        if(!$this->smsConnect) $this->Connection();
        curl_setopt($this->smsConnect, CURLOPT_HTTPHEADER, $header);  			         
        curl_setopt($this->smsConnect, CURLOPT_POSTFIELDS, $fields);
        $out=curl_exec($this->smsConnect);
        return ($this->debug) ? htmlspecialchars($out) : $out;
	}

	public function AddHlr($numery) {
		$out="";
		$POST=implode("\n",$numery);
		$timestamp=time();
		$HASH=md5('POST/sms_xmlapi_beta.php'.md5($POST).'Accept:text/plain'.$timestamp.$this->pass);
		$header=array();
		array_push($header,"X-GT-Auth: ".$this->login.":".$HASH);
		array_push($header,"X-GT-Timestamp: ".$timestamp);
		array_push($header,"Content-Type: application/x-www-form-urlencoded");
		array_push($header,"Accept: text/plain");
		array_push($header,"Expect: 100-continue");
		array_push($header,"X-GT-Action: PUT HLR");
		$fields="body=".urlencode($POST);
			
        if(!$this->smsConnect) $this->Connection();
        curl_setopt($this->smsConnect, CURLOPT_HTTPHEADER, $header);  			         
        curl_setopt($this->smsConnect, CURLOPT_POSTFIELDS, $fields);
        $out=curl_exec($this->smsConnect);
        return ($this->debug) ? htmlspecialchars($out) : $out;
	}
        
	public function sendSms() {
		$out="";
		$POST='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<package test="'.$this->test.'">';
		foreach($this->msg as $msg) $POST.=$msg;
		$POST.='</package>';
			
		$timestamp=time();
		$HASH=md5('POST/sms_xmlapi.php'.md5($POST).'Accept:application/xml'.$timestamp.$this->pass);
		$header=array();
		array_push($header,"X-GT-Auth: ".$this->login.":".$HASH);
		array_push($header,"X-GT-Timestamp: ".$timestamp);
		array_push($header,"Content-Type: application/x-www-form-urlencoded");
		array_push($header,"Accept: application/xml");
		array_push($header,"Expect: 100-continue");

        $fields="body=".urlencode($POST);
			
        if(!$this->smsConnect) $this->Connection();
        curl_setopt($this->smsConnect, CURLOPT_HTTPHEADER, $header);  			         
        curl_setopt($this->smsConnect, CURLOPT_POSTFIELDS, $fields);
        $out=curl_exec($this->smsConnect);
        return ($this->debug) ? htmlspecialchars($out) : $out;
	}

	public function ConnectClose() {
    	if($this->smsConnect) curl_close($this->smsConnect);
    }
        
	public function __desctruct() {
    	$this->ConnectClose();
    }
};
?>