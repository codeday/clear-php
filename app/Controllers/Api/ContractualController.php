<?php
namespace CodeDay\Clear\Controllers\Api;

abstract class ContractualController extends \Controller {
    protected function getContract($modelOrIterable) {
        $contract = null;

        // Check if this is actually an array of models
        if (self::isIterable($modelOrIterable)) {
            $ret = [];
            foreach ($modelOrIterable as $m) {
                $ret[] = $this->getObjectContract($m);
            }
            $contract = $ret;
        } else {
            $contract = $this->getObjectContract($modelOrIterable);
        }

        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        $response->setContent(json_encode($contract));
        return $response;
    }

    private function getObjectContract($model, $fields = null)
    {
        if (!isset($fields)) {
            $fields = $this->fields;
        }

        $contract_obj = [];

        // Re-map the appropriate fields
        foreach ($fields as $key => $value) {

            if (is_int($key) && is_string($value)) {
                $contract_obj[$value] = self::normalizeModelValue($model->$value);
            } else if (is_string($key) && is_string($value)) {
                $contract_obj[$key] = self::normalizeModelValue($model[$value]);
            } else if (is_string($key) && is_array($value)) {
                $contract_obj[$key] = $this->getObjectContract($model, $value);
            }
        }

        return (Object)$contract_obj;
    }

    private static function normalizeModelValue($value)
    {

        if ($value instanceof \Eloquent) {
            return $value->{$value->getKeyName()};
        } elseif (self::isIterable($value)) {
            $arr = [];
            foreach ($value as $obj) {
                $arr[] = $obj->{$obj->getKeyName()};
            }
            return $arr;
        } else {
            return $value;
        }
    }

    private static function isIterable($var) {
        return (is_array($var) || $var instanceof \Traversable);
    }
} 