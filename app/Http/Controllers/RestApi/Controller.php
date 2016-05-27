<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 17:05
 */

namespace App\Http\Controllers\RestApi;


use App\RestApiModels\Model;

class Controller extends \App\Http\Controllers\Controller
{
    protected function checkObligatoryFields($object, Model $classObject) {


        $obligatoryFields = $classObject::$obligatory_post_fields;

        foreach($obligatoryFields as $index=>$obligatoryField) {
            if(is_array($obligatoryField)) {
                if(!in_array($object[$index], $obligatoryField)) {
                    return false;
                }
            }
            else {

                if(substr( $index, 0, 2 ) === "->") {
                    $relation = substr($index, 2);
                    $relatedClass = get_class($classObject->$relation()->getRelated());
                    $reflection = new \ReflectionClass($relatedClass);
                    foreach($object[$obligatoryField] as $relatedObject) {

                        if(!$this->checkObligatoryFields($relatedObject, $reflection->newInstance()))
                            return false;
                    }
                }
                else {
                    if(!array_key_exists($obligatoryField, $object)) {
                        return false;
                    }
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