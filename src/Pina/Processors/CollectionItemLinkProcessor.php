<?php


namespace Pina\Processors;


use Pina\Data\Schema;
use Pina\Html;
use Pina\Http\Location;

/**
 * Преобразует элементы списка в ссылки на странички элементов, ориентируясь на поле id
 */
class CollectionItemLinkProcessor
{
    /** @var Schema */
    private $schema;

    /** @var Location */
    private $location;

    /** @var string[] */
    private $fields;

    /** @var string[] */
    private $context;

    /**
     * @param Schema $schema Задает схему данных
     * @param Location $location Ссылка на коллекцию, относительно нее будут строиться вложенные ссылки на элементы
     * @param string[] $fields Перечень полей, которые нужно преобразовать в ссылки. Если не указан, преобразуются все
     */
    public function __construct(Schema $schema, Location $location, $fields = [], $context = [])
    {
        $this->schema = $schema;
        $this->location = $location;
        $this->fields = $fields;
        $this->context = $context;
    }

    public function __invoke($processed, $raw)
    {
        //собираемся преобразовать все поля схемы
        $linedKeys = $this->schema->getFieldKeys();
        $primaryKey = $this->schema->getPrimaryKey();
        foreach ($primaryKey as $k => $pkElement) {
            if (isset($this->context[$pkElement])) {
                unset($primaryKey[$k]);
            }
        }
        $pk = array_shift($primaryKey);
        if (empty($pk)) {
            $pk = 'id';
        }
        if ($this->fields) {
            //но, если указан конкретный перечень полей, то преобразуем только его
            $linedKeys = array_intersect($linedKeys, $this->fields);
        }
        foreach ($linedKeys as $key) {
            if (!isset($processed[$key])) {
                continue;
            }
            $processed[$key] = Html::a($processed[$key], $this->location->link('@/:id', ['id' => $raw[$pk]]));
        }
        return $processed;
    }
}