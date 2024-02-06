<?php


namespace Pina\Http;


use Pina\App;
use Pina\Controls\ActionButton;
use Pina\Controls\LinkedButton;
use Pina\Controls\RecordForm;
use Pina\Controls\SidebarWrapper;
use Pina\Controls\TableView;
use Pina\Data\DataRecord;

use Pina\Data\DataTable;

class RichEndpoint extends Endpoint
{
    protected function makeLinkedButton($title, $link, $style = ''): LinkedButton
    {
        /** @var LinkedButton $button */
        $button = App::make(LinkedButton::class);
        $button->setLink($link);
        $button->setTitle($title);
        if ($style) {
            $button->setStyle($style);
        }
        return $button;
    }

    protected function makeActionButton($title, $resource, $method, $params = [], $style = ''): ActionButton
    {
        /** @var ActionButton $button */
        $button = App::make(ActionButton::class);
        $button->setTitle($title);
        $button->setHandler($resource, $method, $params);
        if ($style) {
            $button->setStyle($style);
        }
        return $button;
    }


    protected function makeRecordForm($action, $method, DataRecord $data): RecordForm
    {
        /** @var RecordForm $form */
        $form = App::make(RecordForm::class);
        $form->setAction($action);
        $form->setMethod($method);
        $form->load($data);
        return $form;
    }

    protected function makeTableView(DataTable $data): TableView
    {
        return App::make(TableView::class)->load($data);
    }

    /**
     *
     * @return SidebarWrapper
     */
    protected function makeSidebarWrapper(): SidebarWrapper
    {
        return App::make(SidebarWrapper::class);
    }

}