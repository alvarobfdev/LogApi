<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 17:05
 */

namespace App\Http\Controllers\RestApi;


class Controller extends \App\Http\Controllers\Controller
{
    protected function checkObligatoryFields($object, $obligatoryFields) {

        foreach($obligatoryFields as $index=>$obligatoryField) {
            if(is_array($obligatoryField)) {

                if (!array_key_exists($index, $object)) {
                    return false;
                }

                if(!$this->checkObligatoryFields($object[$index], $obligatoryFields)) {
                    return false;
                }
            }
            else {
                if(!array_key_exists($obligatoryField, $object)) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function addObligatoryFields($objectPost, $obligatoryFields, \Moloquent &$moloquentObj) {
        foreach($obligatoryFields as $index=>$obligatoryField) {
            if (is_array($obligatoryField)) {

            } else {
                $moloquentObj->$obligatoryField = $objectPost[$obligatoryField];
            }
        }
    }
}