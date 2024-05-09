<?php


namespace Pina\Http;


use Pina\App;
use Pina\Composers\CollectionComposer;
use Pina\Controls\ActionButton;
use Pina\Controls\ButtonRow;
use Pina\Controls\EditableTableView;
use Pina\Controls\HandledForm;
use Pina\Controls\LinkedButton;
use Pina\Controls\RawHtml;
use Pina\Controls\RecordForm;
use Pina\Controls\SidebarWrapper;
use Pina\Controls\SubmitButton;
use Pina\Controls\TableView;
use Pina\Controls\Wrapper;
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

    protected function makeEditableTableForm(string $action, string $method, DataTable $data, string $submitTitle, string $cancelTitle, string $name = 'data'): HandledForm
    {
        /** @var HandledForm $form */
        $form = App::make(HandledForm::class);
        $form->setAction($action);
        $form->setMethod($method);

        $form->append($this->makeEditableTableView($data, $name));

        $row = $this->makeButtonRow();
        if ($submitTitle) {
            $row->setMain($this->makeSubmit($submitTitle));
        }
        if ($cancelTitle) {
            $row->append($this->makeLinkedButton($cancelTitle, $this->location->link('@')));
        }

        $form->append($row);

        return $form;
    }

    protected function makeEditableTableView(DataTable $data, string $name = 'data'): EditableTableView
    {
        return App::make(EditableTableView::class)->setName($name)->load($data);
    }

    protected function makeButtonRow(): ButtonRow
    {
        return App::make(ButtonRow::class);
    }

    protected function makeSubmit(string $title): SubmitButton
    {
        return App::make(SubmitButton::class)->setTitle($title);
    }

    protected function makeSidebarWrapper(): SidebarWrapper
    {
        return App::make(SidebarWrapper::class);
    }

    protected function makeAlert($text, $style = 'danger')
    {
        $alert = new Wrapper(".alert alert-" . $style);
        $alert->append(new RawHtml($text));
        return $alert;
    }

    protected function makeCollectionComposer(string $collection, string $creation): CollectionComposer
    {
        /** @var CollectionComposer $composer */
        $composer = App::make(CollectionComposer::class);
        $composer->configure($collection, $creation);
        return $composer;
    }

}