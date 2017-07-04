<?php

namespace Pina;

class Mail extends Request
{

    private static $config = [];
    
    public static function send($handler, $data = [])
    {
        if (empty(static::$config)) {
            static::$config = Config::load('mail');
        }
        #Place::init();
        
        self::push(new MailHandler($handler, Request::module(), $data));
        self::run();
        self::pop();
    }

    public static function to($address, $name = '')
    {
        self::top()->to($address, $name);
    }

    public static function cc($address, $name = '')
    {
        self::top()->cc($address, $name);
    }

    public static function bcc($address, $name = '')
    {
        self::top()->bcc($address, $name);
    }

    public static function attachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        self::top()->attachment($path, $name, $encoding, $type, $disposition);
    }

    public static function stringAttachment($string, $filename = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        self::top()->stringAttachment($path, $filename, $encoding, $type, $disposition);
    }

    public static function mail($mailer)
    {

        if (empty(static::$config)) {
            return;
        }
        
        if (empty($mailer)) {
            return;
        }

        if (static::$config['mode'] == 'smtp') {
            $mailer->isSMTP();
            $mailer->Host = static::$config['smtp']['host'];
            if (static::$config['smtp']['user']) {
                $mailer->SMTPAuth = true;
                $mailer->Username = static::$config['smtp']['user'];
                $mailer->Password = static::$config['smtp']['pass'];
            }
            $mailer->SMTPSecure = static::$config['smtp']['secure'];
            $mailer->Port = static::$config['smtp']['port'];
        } else {
            $mailer->isMail();
        }

        $mailer->setFrom(static::$config['from']['address'], !empty(static::$config['from']['name'])?static::$config['from']['name']:'');
        if (!empty(static::$config['reply']['address'])) {
            $mailer->addReplyTo(static::$config['reply']['address'], !empty(static::$config['reply']['name'])?static::$config['reply']['name']:'');
        }
        
        $mailer->CharSet = App::charset();

        if ($mailer->AltBody) {
            $mailer->isHTML(true);
        }
        
        if(!$mailer->send()) {
            Log::error("mail", "error send email to ".json_encode($mailer, JSON_UNESCAPED_UNICODE));
            return false;
        }
        
        return true;
    }

}