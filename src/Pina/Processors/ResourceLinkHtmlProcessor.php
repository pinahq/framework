<?php


namespace Pina\Processors;


use Pina\Arr;
use Pina\Data\Schema;
use Pina\Html;
use Pina\Http\Location;
use Pina\Url;

class ResourceLinkHtmlProcessor
{
    /** @var Location */
    private $location;

    /** @var string */
    private $field = '';
    private $title = '';

    /**
     * @param Schema $schema Задает схему данных
     * @param Location $location Ссылка на коллекцию, относительно нее будут строиться вложенные ссылки на элементы
     * @param string[] $fields Перечень полей, которые нужно преобразовать в ссылки. Если не указан, преобразуются все
     */
    public function __construct(Location $location, $field, $title)
    {
        $this->location = $location;
        $this->field = $field;
        $this->title = $title;
    }

    public function __invoke($processed, $raw)
    {
        list($preg, $map) = Url::preg($this->field);
        $processed[$this->field] = Html::a($this->title, $this->location->link($this->field, Arr::only($raw, $map)));
        return $processed;
    }
}