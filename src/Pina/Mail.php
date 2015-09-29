<?php

namespace Pina;

use Pina\Response\HtmlResponse;
use \PHPMailer;

class Mail extends Request
{

    private static $config = array();
    private static $to = array();
    private static $cc = array();
    private static $bcc = array();
    
    private static $content = '';

    public static function send($module, $handler, $data = array())
    {
        if (empty(static::$config)) {
            static::$config = Config::load('mail');
        }
        Place::set('mail_subject', '');
        Place::set('mail_alternative', '');
        
        static::$to = array();
        
        $path = Module::path($module);
        if (empty($path)) {
            return;
        }
        
        $path .= '/emails/'.$handler;
        
        static::run($path, $data);

        return static::mail();
    }

    public static function to($address, $name = '')
    {
        static::$to [] = array('address' => $address, 'name' => $name);
    }

    public static function cc($address, $name = '')
    {
        static::$cc [] = array('address' => $address, 'name' => $name);
    }

    public static function bcc($address, $name = '')
    {
        static::$bcc [] = array('address' => $address, 'name' => $name);
    }

    public static function run($handler, $data)
    {
        $oldResponse = self::$response;
        $oldStack = self::$stack;

        self::$response = new Response\HtmlResponse();
        self::$stack = array();
        $method = 'get';
        
        array_push(self::$stack, $data);
        
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }
        
        self::runHandler($handler);
        
        if (!empty(self::$stack[$top]['display'])) {
            $handler .= '.' . self::$stack[$top]['display'];
        }
        
        $r = self::$response->fetch($handler, true);
        Language::rewrite($r);
        
        array_pop(self::$stack);

        self::$response = $oldResponse;
        self::$stack = $oldStack;

        static::$content = $r;
    }

    private static function mail()
    {

        if (empty(static::$config)) {
            return;
        }

        $mail = new PHPMailer;

        if (static::$config['mode'] == 'smtp') {
            $mail->isSMTP();
            $mail->Host = static::$config['smtp']['host'];
            if (static::$config['smtp']['user']) {
                $mail->SMTPAuth = true;
                $mail->Username = static::$config['smtp']['user'];
                $mail->Password = static::$config['smtp']['pass'];
            }
            $mail->SMTPSecure = static::$config['smtp']['secure'];
            $mail->Port = static::$config['smtp']['port'];
        }

        $mail->setFrom(static::$config['from']['address'], !empty(static::$config['from']['name'])?static::$config['from']['name']:'');
        foreach (static::$to as $u) {
            $mail->addAddress($u['address'], $u['name']);
        }
        
        if (static::$config['reply']['address']) {
            $mail->addReplyTo(static::$config['reply']['address'], !empty(static::$config['reply']['name'])?static::$config['reply']['name']:'');
        }
        
        foreach (static::$cc as $u) {
            $mail->addCC($u['address'], $u['name']);
        }
        
        foreach (static::$bcc as $u) {
            $mail->addBCC($u['address'], $u['name']);
        }
        
        $mail->CharSet = App::charset();

        $mail->Subject = Place::get('mail_subject');
        $mail->Body = static::$content;
        $mail->AltBody = Place::get('mail_alternative');

        if ($mail->AltBody) {
            $mail->isHTML(true);
        }
        
        if(!$mail->send()) {
            Log::error("mail", "error send email to ".json_encode($mail, JSON_UNESCAPED_UNICODE));
            return false;
        }
        
        return true;
    }

}
