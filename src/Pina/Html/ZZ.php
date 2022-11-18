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

    /**
     * @param string[] $args
     * @return string
     * @throws Exception
     */
    public function run(array $args)
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
                        $value = '';
                        while (!$this->expected(']') && !$this->finished()) {
                            $value .= $this->next();
                        }
                        $value = $this->resolve($value);
                    }
                    $this->readExpected(']');
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

    private function next(): ?string
    {
        return array_shift($this->tokens);
    }

    private function resolve(string $s): string
    {
        return $s == '%' ? $this->nextArg() : $s;
    }

    private function nextArg(): string
    {
        return array_shift($this->args) ?? '';
    }

    /**
     * @param string $token
     * @throws Exception
     */
    private function readExpected(string $token): string
    {
        $next = $this->next();
        if (is_null($next)) {
            throw new Exception("Ожидается символ " . $token . ', но выражение закончилось');
        }
        if ($next != $token) {
            throw new Exception("Ожидается символ " . $token . ' вместо ' . $next);
        }
        return $next;
    }

    private function expected(string $token): string
    {
        return ($this->tokens[0] ?? '') == $token;
    }

    private function finished(): bool
    {
        return empty($this->tokens);
    }

    private function begin(): void
    {
        $this->startTag();
        $this->content = '';
    }

    private function startTag(): void
    {
        $this->tag = '';
        $this->options = [];
    }

    private function flush($content = ''): void
    {
        if ($this->tag || $this->options) {
            $this->content .= $this->tag($content);
            $this->startTag();
        } else {
            $this->content .= $content;
        }
    }

    private function tag($content = ''): string
    {
        return Html::tag($this->tag ? $this->tag : 'div', $content, $this->options);
    }

    private function push(): void
    {
        array_push($this->stack, [$this->content, $this->tag, $this->options]);
    }

    private function pop(): void
    {
        list($this->content, $this->tag, $this->options) = array_pop($this->stack);
    }

    /**
     * Разбивает шаблон на токены
     * @param string $template
     * @return string[]
     */
    private function getTokens(string $template): array
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