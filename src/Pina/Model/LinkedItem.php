<?php


namespace Pina\Model;


class LinkedItem implements LinkedItemInterface
{
    protected $title = '';
    protected $link = '';

    public function __construct($title, $link)
    {
        $this->title = $title;
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getTitle()
    {
        return $this->title;
    }

}