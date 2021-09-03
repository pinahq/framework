<?php

namespace Pina\Types;

use Exception;
use Pina\Components\SelectedTextComponent;
use Pina\Components\SelectComponent;
use Pina\Controls\FormContentControl;
use Pina\App;
use Pina\Components\Field;
use Pina\Controls\RawHtml;
use Pina\Request;
use Pina\RequestHandler;

/**
 * @deprecated
 */
class ModuleType extends ConfigurableType
{

    protected $resource = null;

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        $parsed = parse_url($this->resource);
        $params = [];
        $query = isset($parsed['query']) ? $parsed['query'] : '';
        parse_str($query, $params);

        if (App::router()->exists($parsed['path'], 'get')) {
            $input = App::make(SelectComponent::class);
            $input->basedOn(App::router()->run($this->resource, 'get'));
            $input->setName($field->getKey());
            $star = $field->isMandatory() ? ' *' : '';
            $input->setTitle($field->getTitle() . $star);
            $input->setValue($value);
            return $input;
        }

        $params = array_merge(['name' => $field->getKey(), 'value' => $value, 'display' => 'select'], $params);

        $content = new FormContentControl();
        $star = $field->isMandatory() ? ' *' : '';
        $content->setTitle($field->getTitle() . $star);
        $handler = new RequestHandler(
            $parsed['path'], 'get', $params
        );
        $handler->set('inline', true);
        $response = Request::internal($handler)->fetchContent();
        $content->setContent($response);
        return $content;
    }

    public function format($value)
    {
        $parsed = parse_url($this->resource);
        $params = [];
        parse_str($parsed['query'], $params);

        if (App::router()->exists($parsed['path'], 'get')) {
            /** @var SelectedTextComponent $input */
            $input = App::make(SelectedTextComponent::class);
            $input->basedOn(App::router()->run($parsed['path'], 'get'));
            $input->setValue($value);
            return $input->drawWithWrappers();
        }

        $params = array_merge(['value' => $value, 'display' => 'text'], $params);

        $content = new RawHtml();
        $handler = new RequestHandler(
            $parsed['path'], 'get', $params
        );
        $handler->set('inline', true);
        $response = Request::internal($handler)->fetchContent();
        $content->setText($response);
        return $content;


//        return $this->makeControlByMode(new Field(), $value, false)->drawWithWrappers();
    }

    protected function makeControlByMode(Field $field, $value, $isEditable)
    {
        throw new Exception("Not implemented");
    }

}
