<?php

namespace Pina\Data;

use Exception;
use Pina\BadRequestException;
use Pina\InternalErrorException;
use Pina\Paging;
use Pina\SQL;
use Pina\TableDataGateway;

use Pina\Types\DirectoryType;
use Pina\Types\Relation;
use Pina\Types\SearchType;

use function Pina\__;

/**
 * Абстрактная коллекция предоставляющая типовой интерфейс к таблице или выборке.
 * Опирается на строителя запросов и его схему.
 */
abstract class DataCollection
{
    /** @return TableDataGateway */
    abstract protected function makeQuery();

    /**
     * Основная схема просмотра и редактирования карточки
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->makeQuery()->getSchema();
    }

    /**
     * Схема списка (таблицы)
     * @return Schema
     */
    public function getListSchema(): Schema
    {
        return $this->makeQuery()->getSchema()->forgetDetailed();
    }

    /**
     * Схема формы создания элемента
     * @return Schema
     */
    public function getCreationSchema($context = []): Schema
    {
        $schema = $this->getSchema()->forgetStatic();
        foreach ($context as $key => $value) {
            $schema->forgetField($key);
        }
        return $schema;
    }

    public function getVariantAvailableSchema()
    {
        $schema = $this->getSchema();
        $schema->forgetStatic();

        $r = new Schema();
        foreach ($schema as $field) {
            if ($field->getType() instanceof Relation) {
                continue;
            }

            if (is_subclass_of($field->getType(), DirectoryType::class)) {
                $r->addField($field);
            }
        }

        return $r;
    }

    /**
     * Схема фильтров коллекции
     * @return Schema
     */
    public function getFilterSchema($context = []): Schema
    {
        $schema = new Schema();
        if ($this->getSchema()->hasSearchable()) {
            $schema->add('search', __('Поиск'), SearchType::class);
        }
        $schema->merge($this->getSchema()->forgetNotFiltrable());
        $schema->setStatic(false)->setNullable()->setMandatory(false);
        foreach ($context as $k => $v) {
            $schema->forgetField($k);
        }
        return $schema;
    }

    /**
     * Делает выборку по указанным фильтрам и возвращает таблицу с данными
     * @param array $filters
     * @return DataTable
     * @throws Exception
     */
    public function getList(array $filters, int $page = 0, int $perPage = 0, array $context = []): DataTable
    {
        $query = $this->makeListQuery($filters, $context);
        $paging = null;
        if ($perPage) {
            $paging = new Paging($page, $perPage);
            $query->paging($paging);
        }
        $schema = $this->getListSchema();
        $schema->fieldset(array_keys($context))->setHidden();
        $schema->restrictPrimaryKeyContext(array_keys($context));
        return new DataTable($query->get(), $schema, $paging);
    }

    /**
     * Выбирает одну запись по первичному ключу
     * @param string $id
     * @return DataRecord
     * @throws \Exception
     */
    public function getRecord(string $id, array $context = []): DataRecord
    {
        $query = $this->makeRecordQuery();
        $schema = $this->getSchema();

        if ($context) {
            $contextSchema = $this->getSchema()->fieldset(array_keys($context))->makeSchema();
            $query->whereFilters($context, $contextSchema);
        }

        $slug = $schema->getSlug();
        $primaryKey = $schema->getPrimaryKey();
        if ($slug || empty($primaryKey)) {
            return new DataRecord($query->whereSlug($id)->firstOrFail(), $this->getSchema());
        }

        foreach ($primaryKey as $k => $pkElement) {
            if (!isset($context[$pkElement])) {
                continue;
            }
            unset($primaryKey[$k]);
        }
        $primaryKey = array_values($primaryKey);

        $count = count($primaryKey);
        $idParts = explode(',', $id, $count);
        foreach ($primaryKey as $k => $pkElement) {
            $query->whereBy($pkElement, $idParts[$k]);
        }
        $item = $query->firstOrFail();
        return new DataRecord($item, $this->getSchema());
    }

    public function getNextId($id, array $context = [])
    {
        $query = $this->makeListQuery([], $context);
        $pk = $query->getSinglePrimaryKey($context);
        $query->whereGreaterThan($pk, $id);
        return $query->min($query->getAlias(). '.`' .$pk.'`');
    }

    public function getPreviousId($id, array $context = [])
    {
        $query = $this->makeListQuery([], $context);
        $pk = $query->getSinglePrimaryKey($context);
        $query->whereLessThan($pk, $id);
        return $query->max($query->getAlias(). '.`' .$pk.'`');
    }

    /**
     * Инициализирует новую запись со значениями по умолчанию под вставку
     * @param array $context
     * @return DataRecord
     * @throws \Exception
     */
    public function getNewRecord(array $data, array $context): DataRecord
    {
        return new DataRecord($data, $this->getCreationSchema($context));
    }

    /**
     * Добавляет в коллекцию элемент и возвращает его идентификатор (если предусмотрен схемой)
     * @param array $data
     * @param array $context
     * @return string|null
     * @throws Exception
     */
    public function add(array $data, array $context = []): string
    {
        $schema = $this->getCreationSchema($context);

        $normalized = $this->normalize($data, $schema, $context);

        $toInsert = array_merge($normalized, $context);
        $id = $this->makeQuery()->insertGetId($toInsert);

        if (empty($id)) {
            $id = $this->resolveId($normalized, $schema, $context);
        }

        $schema->onUpdate($id, $normalized);

        return $id;
    }

    protected function resolveId(array $normalized, Schema $schema, array $context = []): string
    {
        $primaryKey = $schema->getPrimaryKey();
        foreach ($context as $key => $value) {
            if (in_array($key, $primaryKey)) {
                $primaryKey = array_diff($primaryKey, [$key]);
            }
        }

        $singlePrimaryKey = array_shift($primaryKey);

        if (count($primaryKey) > 0) {
            throw new InternalErrorException("Wrong primary key");
        }

        return $normalized[$singlePrimaryKey] ?? '';
    }

    /**
     * Редактирует элемент коллекции по его идентификатору и возвращает актуальный идентификатор
     * (идентификатор мог поменяться в процессе редактирования)
     * @param string $id
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function update(string $id, array $data, array $context = [], array $fields = []): string
    {
        $schema = $this->getSchema();
        if ($fields) {
            $schema = $schema->forgetAllExcept($fields);
        }

        if ($schema->isEmpty()) {
            return $id;
        }

        $normalized = $this->normalize($data, $schema, $context, $id);

        $query = $this->makeQuery();

        $id = $this->filterQueryByPrimaryKey($query, $schema, $id, $normalized, $context);

        $query->update($normalized);

        $schema->onUpdate($id, $normalized);

        return $id;
    }

    /**
     * @param array $data
     * @param array $context
     * @throws Exception
     */
    public function bulkUpdate(array $data, array $context = [])
    {
        if (empty($data) || !is_array($data)) {
            return;
        }

        $schema = $this->getListSchema()->forgetStatic();

        $normalizedData = [];
        foreach ($data as $id => $v) {
            $normalizedData[$id] = $schema->normalize($v);
        }

        foreach ($normalizedData as $id => $normalized) {
            $query = $this->makeQuery();

            $this->filterQueryByPrimaryKey($query, $schema, $id, $normalizedData, $context);

            $query->update($normalized);
        }
    }

    protected function filterQueryByPrimaryKey(SQL $query, Schema $schema, string $id, array $normalized, array $context = [])
    {
        $primaryKey = $schema->getPrimaryKey();

        if (empty($primaryKey)) {
            throw new InternalErrorException("Wrong primary key during collection update");
        }

        $idFound = false;
        foreach ($primaryKey as $pkElement) {
            if (isset($context[$pkElement])) {
                $query->whereBy($pkElement, $context[$pkElement]);
                continue;
            } else {
                if ($idFound) {
                    throw new InternalErrorException("Wrong context configuration during collection update");
                }

                $query->whereBy($pkElement, $id);
                $id = $normalized[$pkElement] ?? $id;
                $idFound = true;
            }
        }

        if (!$idFound) {
            throw new InternalErrorException("Wrong ID configuration during collection update");
        }

        return $id;
    }

    /**
     * Сортирует данные в коллекции в указанном порядке при наличии типового поля сортировки `order`
     * @param array $ids
     * @return void
     */
    public function reorder(array $ids)
    {
        $this->makeQuery()->reorder($ids);
    }

    /**
     * Нормализует данные (переводит из человеко-понятного вида в вид, в котором они будут храниться)
     * В случае невозможности нормализации (например, если данные были введены с ошибками), выкидывает исключение
     * @param array $data
     * @param Schema $schema
     * @param string|null $id
     * @return array
     * @throws Exception
     */
    protected function normalize(array $data, Schema $schema, $context = [], ?string $id = null): array
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
                $query->whereNotId($id, $context);
            }
            foreach ($fields as $field) {
                $query->whereBy($field, $normalized[$field] ?? ($context[$field] ?? ''));
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
     * Формирует типовой запрос на выборку и фильтрацию данных коллекции
     * @param array $filters
     * @return TableDataGateway
     * @throws Exception
     */
    protected function makeListQuery(array $filters, array $context): TableDataGateway
    {
        $schema = $this->getFilterSchema($context);
        $contextSchema = $this->getSchema()->fieldset(array_keys($context))->makeSchema();
        $schema->merge($contextSchema);
        return $this->makeQuery()->whereSearch($filters['search'] ?? '', $this->getSchema())->whereFilters(array_merge($filters, $context), $schema);
    }

    /**
     * Формирует типовой запрос на выборку элемента коллекции
     * @return TableDataGateway
     */
    protected function makeRecordQuery(): TableDataGateway
    {
        return $this->makeQuery();
    }

}