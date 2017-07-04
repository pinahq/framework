<?php

namespace Pina;

use Pina\Response\HtmlResponse;
use \PHPMailer;

class MailHandler extends RequestHandler
{
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $attachment = [];
    private $stringAttachment = [];
    
    private $handler;
    
    public function __construct($handler, $module, $data)
    {
        $this->done = false;
        $this->handler = $handler;
        $this->module = $module;
        $this->data= $data;
        $this->layout = 'email';
    }
    
    public function to($address, $name = '')
    {
        $this->to [] = array('address' => $address, 'name' => $name);
    }

    public function cc($address, $name = '')
    {
        $this->cc [] = array('address' => $address, 'name' => $name);
    }

    public function bcc($address, $name = '')
    {
        $this->bcc [] = array('address' => $address, 'name' => $name);
    }

    public function attachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        $this->attachment [] = array(
            'path' => $path,
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type,
            'disposition' => $disposition
        );
    }

    public function stringAttachment($string, $filename = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        $this->stringAttachment [] = array(
            'string' => $string,
            'filename' => $filename,
            'encoding' => $encoding,
            'type' => $type,
            'disposition' => $disposition
        );
    }
    
    public function run()
    {
        $path = $this->module->getPath();
        if (empty($path)) {
            return false;
        }
        
        $path .= '/emails/'.$this->handler;
        
        if (!self::runHandler($path)) {
            return false;
        }
        
        if ($this->done) {
            return false;
        }
        
        $display = $this->param('display');
        if (!empty($display)) {
            $path .= '.' . $display;
        }
        
        $response = new HtmlResponse();
        $r = $response->fetchEmail($this->results, basename($path));
        
        return Mail::mail($this->mailer($r));
        
    }
    
    private function mailer($content)
    {
        if (empty($this->to)) {
            return null;
        }
        
        $mailer = new PHPMailer;
        foreach ($this->to as $u) {
            $mailer->addAddress($u['address'], $u['name']);
        }
        
        foreach ($this->cc as $u) {
            $mailer->addCC($u['address'], $u['name']);
        }
        
        foreach ($this->bcc as $u) {
            $mailer->addBCC($u['address'], $u['name']);
        }

        foreach ($this->attachment as $a) {
            $mailer->addAttachment($a['path'], $a['name'], $a['encoding'], $a['type'], $a['disposition']);
        }

        foreach ($this->stringAttachment as $sa) {
            $mailer->addStringAttachment($sa['string'], $sa['filename'], $sa['encoding'], $sa['type'], $sa['disposition']);
        }

        $mailer->Subject = Place::get('mail_subject');
        $mailer->Body = $content;
        $mailer->AltBody = Place::get('mail_alternative');

        if ($mailer->AltBody) {
            $mailer->isHTML(true);
        }
        
        return $mailer;
    }
}