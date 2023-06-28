<?php

namespace Pina\Controls;

use Pina\App;
use Pina\CSRF;

class ActionButton extends LinkedButton
{
    public function __construct()
    {
        $this->addClass('pina-action');
        $this->setLink('#');
        $classId = uniqid('btn');
        $this->addClass($classId);
        $this->includeScripts($classId);
    }

    public function setHandler($resource, $method, $params = [])
    {
        $this->setDataAttribute('resource', $resource);
        $this->setDataAttribute('method', $method);
        $this->setDataAttribute('params', http_build_query($params));
        $this->setDataAttribute('csrf-token', CSRF::token());
    }

    public function setSuccess($success)
    {
        $this->setDataAttribute('success', $success);
        return $this;
    }

    protected function includeScripts($classId)
    {
        App::assets()->addScriptContent($this->makeScript($classId));
    }

    protected function makeScript($classId)
    {
        return <<<HEREDOC
<script>
    $(".$classId").on("success", function(event, packet, status, xhr) {
        if (!PinaRequest.handleRedirect(xhr)) {
            var target = $(self).attr("data-success") ? $(self).attr("data-success") : document.location.pathname;
            document.location = target + "?changed=" + Math.random();
        }
    });
    </script>
HEREDOC;
    }

}