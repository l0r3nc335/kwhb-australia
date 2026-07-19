<?php

namespace App\Services\Myob\DataMaps;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

abstract class DataMapper
{
    public abstract function fields(): array;
    public abstract function computedFields(): array;

    public function map(Model $model, object $data): Model
    {
        $allFields = array_merge(array_keys($this->fields()), array_keys( $this->computedFields()));
        $fields = collect($this->fields());
        $computedFields = collect($this->computedFields());

        foreach ($allFields as $field) {
            if (in_array($field, $fields->keys()->toArray())) {
                $value = optional($data)->{$this->fields()[$field]};
                if (!empty($value)) {
                    $model->{$field} = $value;
                }
            } elseif (in_array($field, $computedFields->keys()->toArray())) {
                $computation = $computedFields->toArray()[$field]['computation'];
                $myobKey = $computedFields->toArray()[$field]['key'];
                $model->{$field} = $this->{$computation}($data, $myobKey);
            }
        }

        $model->save();

        return $model->refresh();
    }

    protected function parseDateOnly(object $data, string $key): string
    {
        return Carbon::parse($data->{$key}, 'Australia/Sydney')->format('Y-m-d');
    }

    protected function concatFields(object $data, string $key, string $separator = ' '): string
    {
        $keyArr = explode('.', $key);
        $values = [];

        foreach ($keyArr as $ka) {
            $value = $data->{$ka};
            if (!empty($value)) {
                $values[] = $value;
            }
        }

        return implode($separator, $values);
    }

    protected function getNestedArrayField(object $data, string $key)
    {
        $keyArr = explode('.', $key);

        if (isset($data->{$keyArr[0]}) && is_array($data->{$keyArr[0]}) && count($data->{$keyArr[0]}) > 0) {
            return optional($data->{$keyArr[0]}[0])->{$keyArr[1]};
        }

        return null;
    }
}
