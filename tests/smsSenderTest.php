<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Programmers4u\gatesms\sms\SmsXmlSender;

$env = file_get_contents(dirname(__FILE__).'/.env');
if($env) {
    $env = json_decode($env);
} else {
    copy(dirname(__FILE__).'/.env.template',dirname(__FILE__).'/.env');
}

final class smsSenderTest extends TestCase
{

    public function testFields(): void
    {
        $sms = new SmsXmlSender;
        //$res = $sms->sendSms();
        //$this->assertFalse($res);
    }
    
    public function testSendSMS(): void {
        /*global $env;
        $sms = new SmsSender();
        $sms->test = 1;
        $sms->modemID = $env->selfnumber;
        $sms->login = $env->login;
        $sms->pass = $env->pass;
        $sms->to = $env->tonumber;
        $sms->AddMsg($env->message);
        $res = $sms->sendSms();
        
        $out = $this->resultToArray($res);
        $this->assertEquals('002',$out[0]['Status']);   
        */ 
    }

    private function resultToArray(string $result): array {
        $out = [];
        $res = explode(',',$result);
        foreach($res as $r) {
            $v = explode(":",$r);
            array_push($out,[ trim($v[0]) => trim($v[1]) ]);
        }
        return $out;
    }
}