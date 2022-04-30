<?php

namespace Pina\Data;

use Pina\BadRequestException;
use Pina\Paging;
use Pina\TableDataGateway;

use function Pina\__;

/**
 * Абстрактная коллекция предоставляющая типовой интерфейс к таблице или выборке.
 * Опирается на строителя запросов и его схему.
 */
abstract class DataCollection
{
    /** @return TableDataGateway */
    abstract function makeQuery();

    /**
     * Основная схема просмотра и редактирования карточки
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->makeQuery()->getSchema()->forgetField('id');
    }

    /**
     * Схема списка (таблицы)
     * @return Schema
     */
    public function getListSchema(): Schema
    {
        return $this->makeQuery()->getSchema()->forgetField('id');
    }

    /**
     * Схема формы создания элемента
     * @return Schema
     */
    public function getCreationSchema(): Schema
    {
        return $this->getSchema()->forgetStatic();
    }

    /**
     * Схема фильтров коллекции
     * @return Schema
     */
    public function getFilterSchema(): Schema
    {
        return $this->getSchema()->forgetStatic();
    }

    /**
     * Делает выборку по указанным фильтрам и возвращает таблицу с данными
     * @param array $filters
     * @return DataTable
     * @throws \Exception
     */
    public function getList(array $filters, int $page = 0, int $perPage = 0): DataTable
    {
        $query = $this->makeListQuery($filters);
        $paging = null;
        if ($perPage) {
            $paging = new Paging($page, $perPage);
            $query->paging($paging);
        }
        return new DataTable($query->get(), $this->getListSchema(), $paging);
    }

    /**
     * Выбирает одну запись по первичному ключу
     * @param string $id
     * @return DataRecord
     */
    public function getRecord(string $id): DataRecord
    {
        $item = $this->makeRecordQuery()->findOrFail($id);
        return new DataRecord($item, $this->getSchema());
    }

    /**
     * Инициализирует новую запись со значениями по умолчанию под вставку
     * @param array $context
     * @return DataRecord
     */
    public function getNewRecord(array $context): DataRecord
    {
        return new DataRecord($context, $this->getCreationSchema());
    }

    /**
     * Добавляет в коллекцию элемент и возвращает его идентификатор (если предусмотрен схемой)
     * Если главный ключ составной, вернет первый элемент ключа
     * @param array $data
     * @return string|null
     * @throws \Exception
     */
    public function add(array $data): ?string
    {
        $schema = $this->getCreationSchema();

        $normalized = $this->normalize($data, $schema);

        $id = $this->makeQuery()->insertGetId($normalized);

        if (empty($id)) {
            $primaryKey = $this->getPrimaryKey($schema);
            if ($primaryKey) {
                $id = $normalized[$primaryKey] ?? null;
            }
        }

        return $id;
    }

    /**
     * Редактирует элемент коллекции по его идентификатору и возвращает актуальный идентификатор
     * (идентификатор мог поменяться в процессе редактирования)
     * @param string $id
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function update(string $id, array $data): string
    {
        $schema = $this->getSchema();

        $normalized = $this->normalize($data, $schema, $id);

        $this->makeQuery()->whereId($id)->update($normalized);

        $primaryKey = $this->getPrimaryKey($schema);
        if ($primaryKey) {
            $id = $normalized[$primaryKey] ?? $id;
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
     * @throws \Exception
     */
    protected function normalize(array $data, Schema $schema, ?string $id = null): array
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
     * Возвращает первое поле первичного ключа при наличии
     * @param Schema $schema
     * @return mixed|null
     */
    protected function getPrimaryKey(Schema $schema): ?string
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
     * Формирует типовой запрос на выборку и фильтрацию данных коллекции
     * @param array $filters
     * @return TableDataGateway
     * @throws \Exception
     */
    protected function makeListQuery(array $filters): TableDataGateway
    {
        $schema = $this->getFilterSchema();
        return $this->makeQuery()->whereFilters($filters, $schema);
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