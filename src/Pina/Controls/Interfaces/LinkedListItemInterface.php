<?php

namespace Pina\Controls\Interfaces;

interface LinkedListItemInterface
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getText();

    /**
     * @return string
     */
    public function getHtmlClass();

    /**
     * Возвращает HTML-тег иконки
     * @return string
     */
    public function getIconHtml();

    /**
     * Возвращает ссылку с элемента списка
     * @return string
     */
    public function getLink();

}