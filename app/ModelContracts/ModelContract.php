<?php
namespace CodeDay\Clear\ModelContracts;

abstract class ModelContract implements \Serializable, \IteratorAggregate, \JsonSerializable
{
    protected $result_data;
    protected $result_documentation;

    static function getFields() {}

    public function __construct(\Eloquent $model = null, $permissions = [], $sparse = false)
    {
        $this->buildDocumentation($permissions);

        if (isset($model)) {
            $this->buildData($model, $permissions, $sparse);
        }
    }

    public static function Model(\Eloquent $model, $permissions = [], $sparse = false)
    {
        return new static($model, $permissions, $sparse);
    }

    public static function Collection(\Illuminate\Database\Eloquent\Collection $collection, $permissions = [], $contract_name = null)
    {
        $models = [];
        foreach ($collection as $model) {
            if (!$contract_name) {
                $reflector = new \ReflectionObject($model);
                $contract_name = '\\CodeDay\\Clear\\ModelContracts\\'.$reflector->getShortName();
            }
            $models[] = $contract_name::Model($model, $permissions, true);
        }
        return $models;
    }

    /**
     * Populates all documentation from the model contract
     *
     * @param $permissions Array of permission names which the requester has
     */
    private function buildDocumentation($permissions)
    {
        foreach (static::getFields() as $key => $field) {
            $result_documentation[$key] = [
                'key'           => $key,
                'name'          => isset($field['name']) ? $field['description'] : null,
                'description'   => isset($field['description']) ? $field['description'] : null,
                'example'       => isset($field['example']) ? $field['example'] : null,
                'type'          => isset($field['type']) ? $field['type'] : null,
                'sparse'        => isset($field['sparse']) ? true : false,
                'requires'      => isset($field['requires']) ? $field['requires'] : [],
                'is_visible'    => !isset($field['requires'])
                                   || (count($field['requires']) > 0
                                   || self::isFieldVisibleByPermissions($field['requires'], $permissions))
            ];
        }
    }

    /**
     * Populates all data from the model contract
     *
     * @param $permissions Array of permission names which the requester has
     * @param $sparse Whether a sparse result is requested (e.g. for embedding into another model)
     */
    private function buildData($model, $permissions, $sparse)
    {
        foreach (static::getFields() as $key => $field) {
            // Make sure the field is visible with these permissions if it has a restriction set
            if (isset($field['requires']) && count($field['requires']) > 0
                && !self::isFieldVisibleByPermissions($field['requires'], $permissions)) {
                continue;
            }

            // If we're requesting a sparse checkout and it's marked as rich, we'll ignore the field
            if ($sparse && isset($field['rich'])) {
                continue;
            }

            $value = $field['value']($model, $permissions, $sparse);

            if (is_object($value) && get_class($value) === 'Generator') { // Check if it's a generator
                $value = iterator_to_array($value);
            }

            // Add the field
            $this->result_data[$key] = $value;
        }
    }

    /**
     * Checks if any permission in granted_permissions overlaps with the allowed permissions.
     *
     * @param $allowed_permissions Array of permission names, any of which would allow access to the field
     * @param $granted_permissions Array of permission names which the requester has
     */
    private static function isFieldVisibleByPermissions($allowed_permissions, $granted_permissions) {
        return count(array_intersect($allowed_permissions, $granted_permissions)) > 0;
    }

    private function getPrimaryKeyValue($model) {
        $primary_key = isset($model->primaryKey) ? $model->primaryKey : 'id';
        return $model->$primary_key;
    }


    public function __get($key) {
        return $this->result_data[$key];
    }

    public function __isset($key) {
        return isset($this->result_data[$key]);
    }

    public function serialize()
    {
        return serialize($this->result_data);
    }

    public function unserialize($data)
    {
        $this->result_data = unserialize($data);
    }

    public function jsonSerialize()
    {
        return $this->result_data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->result_data);
    }
}