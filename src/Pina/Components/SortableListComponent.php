<?php

namespace Pina\Components;

use Pina\App;
use Pina\Controls\ListItem;
use Pina\Controls\UnorderedList;
use Pina\CSRF;
use Pina\Html;
use Pina\Controls\Card;
use Pina\ResourceManagerInterface;
use Pina\StaticResource\Script;

use function Pina\__;

/**
 * @deprecated see \Pina\Controls\SortableListView
 */
class SortableListComponent extends ListData
{

    protected $method = '';
    protected $resource = '';
    protected $params = '';

    public function build()
    {
        $list = $this->makeList();
        $list->setDataAttribute('method', $this->method);
        $list->setDataAttribute('resource', $this->resource);
        $list->setDataAttribute('params', $this->params);
        $csrfAttributes = CSRF::tagAttributeArray($this->method);
        if (!empty($csrfAttributes['data-csrf-token'])) {
            $list->setDataAttribute('csrf-token', $csrfAttributes['data-csrf-token']);
        }

        $data = $this->getHtmlData();
        foreach ($data as $line) {
            $list->append($this->makeListItem($line));
        }

        $container = new Card;
        $container->append($list);
        $this->append($container);

        $this->resources()->append((new Script())->setSrc('/static/default/js/pina.sortable.js'));
    }

    public function setHandler($resource, $method, $params)
    {
        $this->method = $method;
        $this->resource = ltrim($resource, '/');
        $this->params = htmlspecialchars(http_build_query($params), ENT_COMPAT);
        return $this;
    }

    public function makeDefaultSchema()
    {
        $schema = new Schema();
        $schema->add('id', __("Номер"));
        $schema->add('title', __('Наименование'));
        $schema->add('subtitle', __('Комментарий'));
        $schema->add('link', __('Ссылка'));
        $schema->add('icon', __('Иконка'));
        $schema->add('class', __('Класс'));
        $schema->add('icon_class', __('Класс иконки'));
        return $schema;
    }

    /**
     * @return UnorderedList
     */
    protected function makeList()
    {
        return $this->control(UnorderedList::class)->addClass('feeds')->addClass('ui-sortable')->addClass(
            'pina-sortable'
        );
    }

    /**
     * @param array $line
     * @return ListItem
     */
    protected function makeListItem($line)
    {
        /** @var ListItem $item */
        $item = $this->control(ListItem::class)->addClass('draggable')->addClass('ui-sortable-handle');
        $item->setDataAttribute('id', $line['id']);

        $item->addClass(isset($line['class']) ? $line['class'] : '');

        $iconColor = !empty($line['icon_class']) ? $line['icon_class'] : 'bg-light-info';
        $icon = Html::tag('div', Html::tag('i', '', ['class' => 'fas ' . $line['icon']]), ['class' => $iconColor]);
        $muted = '';
        if (!empty($line['subtitle'])) {
            $muted = Html::tag('span', $line['subtitle'], ['class' => 'text-muted']);
        }
        $link = Html::a($line['title'], $line['link']);
        $item->setText($icon . $muted . $link);
        return $item;
    }

    /**
     *
     * @return ResourceManagerInterface
     */
    protected function resources()
    {
        return App::container()->get(ResourceManagerInterface::class);
    }

}
