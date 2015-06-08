<?php

namespace Pina\Response;

class RedirectResponse extends Response
{

    public function fail()
    {
        $this->location($_SERVER["HTTP_REFERER"]);
        exit;
    }

    public function result($name, $value)
    {
        
    }

    public function fetch($handler = '', $first = true)
    {
        $this->header('Pina-Response: Redirect');
        $this->contentType('text/html');
        $this->location($_SERVER["HTTP_REFERER"]);
    }
    
    /* 
     * Браузер при наличии кода 201 и Location не совершает перенаправление 
     * Поэтому подменяем для запросов редиректом код 201 на 302
     */
    public function created($url)
    {
        $this->found($url);
    }
    

    /* 
     * По умолчанию запрос с редиректом отправляет пользователя на предыдущую 
     * страницу.
     * Но этого не надо делать, если в процессе выполнения запроса уже был 
     * отправлен заголовок Location.
     * Поэтому отправляем заголовок Location только один раз. При повторных
     * отправках - блокируем.
     */
    public function location($url)
    {
        static $hasSent = false;
        
        if (!$hasSent) {
            $this->header('Location: ' . $url);
            $hasSent = true;
        }
    }


}
