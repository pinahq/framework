<?php


namespace Pina\Http;

use Pina\App;
use Pina\BadRequestException;
use Pina\Controls\Control;
use Pina\Response;
use Pina\TableDataGateway;
use Pina\Data\Schema;
use Pina\Data\DataRecord;
use Pina\Controls\ButtonRow;
use Pina\Controls\RecordForm;
use Pina\Controls\RecordView;

use function Pina\__;

abstract class CollectionEndpoint extends FixedCollectionEndpoint
{
    public function getSchema()
    {
        return $this->makeQuery()->getSchema()->forgetField('id');
    }

    /** @return Schema */
    public function getCreationSchema()
    {
        return $this->getSchema()->forgetStatic();
    }

    /**
     * @param string $event
     * @param int $id
     */
    protected function trigger($event, $id)
    {
    }

    /**
     * Возвращает именование коллекции или элемента
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function title($id)
    {
        if ($id) {
            return $this->composer->getItemTitle($this->getDataRecord($id));
        }
        return parent::title($id);
    }

    /**
     * @param $id
     * @return Control
     * @throws \Exception
     */
    public function show($id)
    {
        $record = $this->getDataRecord($id);

        $this->composer->show($this->location, $record);

        return $this->resolveRecordView($record)
            ->wrap($this->makeSidebarWrapper());
    }

    /**
     * @param $id
     * @return DataRecord
     * @throws \Exception
     */
    protected function getDataRecord($id): DataRecord
    {
        $item = $this->makeShowQuery()->findOrFail($id);
        return new DataRecord($item, $this->getSchema());
    }

    /**
     * @return Control
     * @throws \Exception
     */
    public function create()
    {
        $record = $this->getNewDataRecord();
        $this->composer->create($this->location);
        return $this->makeCreateForm($record)->wrap($this->makeSidebarWrapper());
    }

    /**
     * @return DataRecord
     * @throws \Exception
     */
    protected function getNewDataRecord(): DataRecord
    {
        return new DataRecord([], $this->getCreationSchema());
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function store()
    {
        $data = $this->request()->all();

        $id = $this->normalizeAndStore($data, $this->getCreationSchema());

        $this->trigger('created', $id);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    /**
     * @param string $id
     * @return Response
     * @throws \Exception
     */
    public function update($id)
    {
        $data = $this->request()->all();

        $id = $this->normalizeAndUpdate($data, $this->getSchema(), $id);

        $this->trigger('updated', $id);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    public function updateSortable()
    {
        $ids = $this->request()->get('id');

        $this->makeQuery()->reorder($ids);

        return Response::ok()->emptyContent();
    }


    /**
     * @param array $data
     * @param Schema $schema
     * @return mixed|string|null
     * @throws \Exception
     */
    protected function normalizeAndStore($data, $schema)
    {
        $normalized = $this->normalize($data, $schema);

        $id = $this->makeQuery()->insertGetId($normalized);

        if (empty($id)) {
            $primaryKey = $this->getPrimaryKey($schema);
            if ($primaryKey) {
                $id = $normalized[$primaryKey] ?? null;
            }
        }

        $schema->onUpdate($id, $normalized);

        return $id;
    }

    /**
     * @param array $data
     * @param Schema $schema
     * @param string $id
     * @throws \Exception
     */
    protected function normalizeAndUpdate($data, $schema, $id)
    {
        $normalized = $this->normalize($data, $schema, $id);

        $this->makeQuery()->whereId($id)->update($normalized);

        $primaryKey = $this->getPrimaryKey($schema);
        if ($primaryKey) {
            $id = $normalized[$primaryKey] ?? $id;
        }

        $schema->onUpdate($id, $normalized);

        return $id;
    }

    /**
     * @param array $data
     * @param Schema $schema
     * @param string|null $id
     * @return array
     * @throws \Exception
     */
    protected function normalize($data, Schema $schema, $id = null)
    {
        $normalized = $schema->normalize($data);

        $uniqueKeys = $schema->getUniqueKeys();
        $pk = $schema->getPrimaryKey();
        if ($pk) {
            $uniqueKeys[] = $schema->getPrimaryKey();
        }
        foreach ($uniqueKeys as $fields) {
            $query = $this->makeQuery();
            if ($id) {
                $query->whereNotId($id);
            }
            foreach ($fields as $field) {
                $query->whereBy($field, $normalized[$field] ?? '');
            }
            if ($query->exists()) {
                $ex = new BadRequestException();
                $ex->setErrors([[__("Данное значение уже используется"), $field]]);
                throw $ex;
            }
        }

        return $normalized;
    }

    /**
     * @param Schema $schema
     * @return mixed|null
     */
    protected function getPrimaryKey($schema)
    {
        $primaryKey = $schema->getPrimaryKey();
        if (empty($primaryKey)) {
            return null;
        }
        if (count($primaryKey) > 1) {
            return null;
        }
        return array_shift($primaryKey);
    }

    /**
     * @return Control
     * @throws \Exception
     */
    protected function makeFilterForm()
    {
        /** @var RecordForm $form */
        $form = parent::makeFilterForm();
        $form->getButtonRow()->append($this->makeResetButton());
        $form->getButtonRow()->append($this->makeCreateButton());
        return $form;
    }

    /**
     * @return Control
     */
    protected function resolveRecordView(DataRecord $data)
    {
        $display = $this->query()->get('display');
        $component = $display == 'edit' ? $this->makeEditForm($data) : $this->makeViewForm($data);
        return $component;
    }

    /**
     * @return Control
     */
    protected function makeEditForm(DataRecord $data)
    {
        /** @var RecordForm $form */
        $form = App::make(RecordForm::class);
        $form->setMethod('put')->setAction($this->location->link('@'));
        $form->getButtonRow()->append($this->makeCancelButton());
        $form->load($data);
        return $form;
    }

    /**
     * @return Control
     */
    protected function makeViewForm(DataRecord $data)
    {
        return $this->makeRecordView($data)->after($this->makeViewButtonRow());
    }

    /**
     * @return Control
     */
    protected function makeCreateForm(DataRecord $data)
    {
        return $this->makeRecordForm($this->base->link('@'), 'post', $data);
    }

    protected function makeIndexButtons()
    {
        $buttons = parent::makeIndexButtons();
        $buttons->setMain($this->makeCreateButton()->setStyle('primary'));
        return $buttons;
    }

    protected function makeCancelButton()
    {
        return $this->makeLinkedButton(__('Отменить'), $this->location->link('@'));
    }

    protected function makeResetButton()
    {
        return $this->makeLinkedButton(__('Сбросить'), $this->base->link('@'));
    }

    protected function makeCreateButton()
    {
        return $this->makeLinkedButton(__('Добавить'), $this->base->link('@/create'));
    }

    /**
     * @return ButtonRow
     */
    protected function makeViewButtonRow()
    {
        /** @var ButtonRow $row */
        $row = App::make(ButtonRow::class);
        $row->addClass('mb-5');
        $row->setMain($this->makeEditLinkButton());
        return $row;
    }

    protected function makeEditLinkButton()
    {
        return $this->makeLinkedButton(__('Редактировать'), $this->location->link('@', ['display' => 'edit']), 'primary');
    }

    /**
     * @return TableDataGateway
     */
    protected function makeShowQuery()
    {
        return $this->addShowQueryColumns($this->makeQuery());
    }


    /**
     * @param TableDataGateway $query
     * @return TableDataGateway
     */
    protected function addShowQueryColumns($query)
    {
        return $this->addDefaultQueryColumns($query);
    }


}