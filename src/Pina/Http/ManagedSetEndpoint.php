<?php


namespace Pina\Http;


use Pina\App;
use Pina\Arr;
use Pina\Controls\Control;
use Pina\Controls\HandledForm;
use Pina\Controls\SubmitButton;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Controls\CheckTableView;
use Pina\Processors\KeyMetaProcessor;
use Pina\Response;

use function Pina\__;

abstract class ManagedSetEndpoint extends CollectionEndpoint
{

    public function getListSchema(): Schema
    {
        $schema = $this->getSchema();
        $schema->pushMetaProcessor(new KeyMetaProcessor($schema->getPrimaryKey()));
        return $schema;
    }

    /**
     * @return Control
     */
    protected function makeCollectionView(DataTable $data)
    {
        /** @var CheckTableView $table */
        $table = App::make(CheckTableView::class);
        $table->load($data);

        /** @var HandledForm $form */
        $form = App::make(HandledForm::class);
        $form->setMethod('delete')->setAction($this->location()->link('@'));

        $form->prepend($table);
        $form->append(App::make(SubmitButton::class)->setTitle(__('Удалить'))->setStyle('danger'));

        return $form;
    }

    public function store()
    {
        $data = $this->request()->all();

        $normalized = $this->normalize($data, $this->getCreationSchema());

        $this->makeQuery()->put($normalized);

        return Response::ok()->contentLocation($this->base()->link('@'));
    }

    public function destroy($id = null)
    {
        $data = $this->request()->all();

        $schema = $this->getListSchema();
        $bulkEditKeys = $data['bulk_edit_key'] ?? [];

        $primaryKey = $schema->getPrimaryKey();
        $keyData = Arr::mineKeyData($bulkEditKeys, $primaryKey);

        App::db()->transaction(
            function () use ($keyData) {
                foreach ($keyData as $keyLine) {
                    $query = $this->makeQuery();

                    foreach ($keyLine as $key => $value) {
                        $query->whereBy($key, $value);
                    }

                    $query->delete();
                }
            }
        );

        return Response::ok();
    }
}