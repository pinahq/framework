<?php

namespace Pina;

use Pina\Response\HtmlResponse;
use \PHPMailer;

class Mail extends Request
{

    private static $config = [];
    private static $to = [];
    private static $cc = [];
    private static $bcc = [];
    private static $attachment = [];
    
    public static function send($handler, $data = [])
    {
        if (empty(static::$config)) {
            static::$config = Config::load('mail');
        }
        Place::init();
        
        static::clear();
        
        $module = Request::module();
        $path = ModuleRegistry::getPath($module);
        if (empty($path)) {
            return;
        }
        
        $path .= '/emails/'.$handler;
        
        return static::run($path, $data);
    }
    
    private static function clear()
    {
        static::$to = [];
        static::$cc = [];
        static::$bcc = [];
        static::$attachment = [];
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

    public static function attachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        static::$attachment [] = array(
            'path' => $path,
            'name' => $name,
            'encoding' => $encoding,
            'type' => $type,
            'disposition' => $disposition
        );
    }

    public static function run($handler, $data)
    {
        $data['__method'] = 'get';
        
        array_push(self::$stack, $data);
        
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }
        
        if (!self::runHandler($handler)) {
            array_pop(self::$stack);
            return false;
        }

        if (!empty(self::$stack[$top]['display'])) {
            $handler .= '.' . self::$stack[$top]['display'];
        }

        $response = new HtmlResponse();
        $r = $response->fetchTemplate(self::$results, $handler);

        array_pop(self::$stack);

        return static::mail($r);
        
    }

    private static function mail($content)
    {

        if (empty(static::$config)) {
            return;
        }
        
        if (empty(static::$to)) {
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
        } else {
            $mail->isMail();
        }

        $mail->setFrom(static::$config['from']['address'], !empty(static::$config['from']['name'])?static::$config['from']['name']:'');
        foreach (static::$to as $u) {
            $mail->addAddress($u['address'], $u['name']);
        }
        
        if (!empty(static::$config['reply']['address'])) {
            $mail->addReplyTo(static::$config['reply']['address'], !empty(static::$config['reply']['name'])?static::$config['reply']['name']:'');
        }
        
        foreach (static::$cc as $u) {
            $mail->addCC($u['address'], $u['name']);
        }
        
        foreach (static::$bcc as $u) {
            $mail->addBCC($u['address'], $u['name']);
        }

        foreach (static::$attachment as $a) {
            $mail->addAttachment($a['path'], $a['name'], $a['encoding'], $a['type'], $a['disposition']);
        }

        $mail->CharSet = App::charset();

        $mail->Subject = Place::get('mail_subject');
        $mail->Body = $content;
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