<?php

namespace Pina\Html;

use Exception;
use Pina\Html;

class ZZ
{

    private $tokens = [];

    private $stack = [];
    private $content = '';
    private $tag = '';
    private $options = [];
    private $args = [];

    public function __construct($template)
    {
        $this->tokens = $this->getTokens($template);
    }

    public function run($args)
    {
        $this->args = $args;
        while ($token = $this->next()) {
            switch ($token) {
                case '.':
                    $this->options['class'] = $this->resolve($this->next());
                    break;
                case '#':
                    $this->options['id'] = $this->resolve($this->next());
                    break;
                case '[':
                    $name = $value = $this->resolve($this->next());
                    if ($this->expected('=')) {
                        $this->next();
                        $value = $this->resolve($this->next());
                    }
                    $ending = $this->next();
                    if ($ending != ']') {
                        throw new Exception("Ожидается символ ]");
                    }
                    $this->options[$name] = $value;
                    break;
                case '(':
                    $this->push();
                    $this->begin();
                    break;
                case ')':
                    $this->flush();
                    $deeperContent = $this->content;
                    $this->pop();
                    $this->flush($deeperContent);
                    break;
                case '+':
                    $this->flush();
                    break;
                case '%':
                    $this->flush($this->resolve('%'));
                    break;
                default:
                    $this->tag = $token;
                    break;
            }
        }
        $this->flush();
        return $this->content;
    }

    private function next()
    {
        return array_shift($this->tokens);
    }

    private function resolve($s)
    {
        return $s == '%' ? $this->nextArg() : $s;
    }

    private function nextArg()
    {
        return array_shift($this->args);
    }

    private function expected($token)
    {
        return $this->tokens[0] == $token;
    }

    private function begin()
    {
        $this->startTag();
        $this->content = '';
    }

    private function startTag()
    {
        $this->tag = '';
        $this->options = [];
    }

    private function flush($content = '')
    {
        if ($this->tag || $this->options) {
            $this->content .= $this->tag($content);
            $this->startTag();
        } else {
            $this->content .= $content;
        }
    }

    private function tag($content = '')
    {
        return Html::tag($this->tag ? $this->tag : 'div', $content, $this->options);
    }

    private function push()
    {
        array_push($this->stack, [$this->content, $this->tag, $this->options]);
    }

    private function pop()
    {
        list($this->content, $this->tag, $this->options) = array_pop($this->stack);
    }

    private function getTokens($template)
    {
        $operators = ['.', '#', '[', ']', '(', ')', '%', '+', '='];
        $tokens = [];
        $token = '';
        $pos = 0;
        $len = strlen($template);
        while ($pos < $len) {
            if (in_array($template[$pos], $operators)) {
                if ($token) {
                    $tokens[] = $token;
                }
                $token = '';
                $tokens[] = $template[$pos];
            } else {
                $token .= $template[$pos];
            }
            $pos++;
        }
        if ($token) {
            $tokens[] = $token;
        }
        return $tokens;
    }
}