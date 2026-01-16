<?php

namespace Pina\Types;

use Pina\App;
use Pina\Arr;
use Pina\BadRequestException;
use Pina\Controls\FormContentControl;
use Pina\Controls\FormControl;
use Pina\Controls\FormStatic;
use Pina\Controls\InputFactoryInterface;
use Pina\Controls\RawHtml;
use Pina\Controls\RecordForm;
use Pina\Controls\RecordFormCompiler;
use Pina\Controls\RecordView;
use Pina\Data\DataRecord;
use Pina\Data\Field;
use Pina\Controls\FieldsetRecordFormCompiler;
use Pina\Controls\FlatRecordFormCompiler;
use Pina\TableDataGateway;

class AttributedFixedRelation extends Relation
{
    public function format($value): string
    {
        return parent::format(array_keys($value));
    }

    public function play($value): string
    {
        $list = '';

        $variants = $this->getVariants();

        foreach ($variants as $variant) {
            $record = new DataRecord($value[$variant['id']], $this->getSchema());

            /** @var RecordView $form */
            $form = App::make(RecordView::class);
            $form->load($record);

            $list .= $this->resolveFormCompiler($form, $record, $variant['title']);
        }

        return $list;
    }

    public function makeControl(Field $field, $value): FormControl
    {
        /** @var FormContentControl $control */
        $control = App::make(FormStatic::class);

        $control->setDescription($field->getDescription());
        $control->setRequired($field->isMandatory());
        $control->setName($field->getName());
        $control->setTitle($field->getTitle());

        if (!$field->isHidden() && !$field->isStatic()) {
            $variants = $this->getVariants();

            $container = new RawHtml();

            foreach ($variants as $variant) {
                $schema = $this->getSchema();
                foreach ($schema as $f) {
                    $name = $field->getName().'[' . $variant['id'] .'][' . $f->getSourceKey().']';
                    $f->setAlias($name);
                }

                $record = new DataRecord([$field->getName() => $value], $schema);

                /** @var RecordForm $form */
                $form = App::make(RecordForm::class);
                $form->load($record);

                $container->append($this->resolveFormCompiler($form, $record, $variant['title']));
            }

            $control->setValue($container);
        }
        return $control;
    }

    protected function resolveFormCompiler(InputFactoryInterface $form, DataRecord $record, $title): RecordFormCompiler
    {
        $schema = $record->getSchema();

        if (count($schema->getFieldNames()) > 1) {
            $schema->setTitle($title);

            /** @var FieldsetRecordFormCompiler $compiler */
            $compiler = App::make(FieldsetRecordFormCompiler::class);
            $compiler->load($record->getSchema(), $form);

            return $compiler;
        }
        foreach ($schema as $f) {
            $f->setTitle($title.' (' . $f->getTitle() .')');
        }
        /** @var FlatRecordFormCompiler $compiler */
        $compiler = App::make(FlatRecordFormCompiler::class);
        $compiler->load($record->getSchema(), $form);

        return $compiler;
    }

    protected function getSchema()
    {
        $schema = $this->makeRelationQuery()->getSchema();
        $schema->forgetField($this->relationField);
        $schema->forgetField($this->directoryField);
        return $schema;
    }

    public function normalize($value, $isMandatory)
    {
        $schema = $this->getSchema();
        $normalized = [];

        $errors = [];
        foreach ($value as $k => $line) {
            try {
                $normalized[$k] = $schema->normalize($line);
            } catch (BadRequestException $e) {
                $es = $e->getErrors();
                foreach ($es as $error) {
                    $error[1] = '[' . $k . '][' . $error[1] . ']';
                    $errors[] = $error;
                }
            }
        }

        if (!empty($errors)) {
            $e = new BadRequestException();
            $e->setErrors($errors);
            throw $e;
        }

        return $normalized;
    }

    public function setData($id, $value)
    {
        $schema = $this->getSchema();
        foreach ($value as $directoryId => $normalized) {
            $this->makeRelationQuery()->whereBy($this->relationField, $id)->whereBy($this->directoryField, $directoryId)->update(Arr::only($normalized, $schema->getFieldNames()));
        }
    }

    public function getData($id)
    {
        $raw = $this->makeRelationQuery()->whereBy($this->relationField, $id)->get();
        return Arr::groupUnique($raw, $this->directoryField);
    }

    protected function makeSelect()
    {
        $input = parent::makeSelect();
        $input->setMultiple(true);
        return $input;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getVariants()
    {
        return $this->makeDirectoryQuery()
            ->selectId()
            ->selectTitle()
            ->cacheStatic($this->cacheSeconds)->get();
    }

    public static function makeTriggers(TableDataGateway $relation, TableDataGateway $firstDirectory, string $firstFK, TableDataGateway $secondDirectory, string $secondFK, array $intersectedFields = [])
    {
        $table = $relation->getTable();
        $first = $firstDirectory->getTable();
        $second = $secondDirectory->getTable();

        $conditions = [];
        foreach ($intersectedFields as $field) {
            $conditions[] = $field .'=NEW.' .$field;
        }
        $where = $conditions ? (' WHERE ' . implode(' AND ', $conditions)) : '';

        return [
            [
                $first,
                'after insert',
                "INSERT IGNORE INTO `{$table}` ({$firstFK}, {$secondFK}) SELECT NEW.id, id FROM `{$second}`" . $where,
            ],
            [
                $first,
                'after delete',
                "DELETE FROM $table WHERE {$firstFK}=OLD.id",
            ],
            [
                $second,
                'after insert',
                "INSERT IGNORE INTO $table ({$firstFK}, {$secondFK}) SELECT id, NEW.id FROM `{$first}`" . $where,
            ],
            [
                $second,
                'after delete',
                "DELETE FROM {$table} WHERE {$secondFK}=OLD.id",
            ]
        ];
    }

}