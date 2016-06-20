<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 17:05
 */

namespace App\Http\Controllers\RestApi;


use App\RestApiModels\Filter;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;

class Controller extends \App\Http\Controllers\Controller
{
    protected $limitPerPage = 15;
    protected $maxLimit = 50;
    const VALID_INPUT = 1;


    /**
     * @var Filter array
     */
    protected $filters = [];

    public function index(Request $request) {


        if($request->has("filter")) {

            if(!is_array($request->get("filter"))) {
                $response['errors'] = ["Filter input must be an array"];
                return response(json_encode($response), 405);
            }

            $filters = $request->get("filter");

            foreach($filters as $index => $value) {
                $this->addFilter($index, $value);
            }
        }
        return false;
    }


    /**
     * @param $validFilters
     * @param Builder $builder
     * @return $this|Builder
     */
    protected function getFilteredBuilder($validFilters, Builder $builder)
    {
        foreach($this->filters as $filter) {
            /* @var $filter Filter */
            if(array_key_exists($filter->getIndex(), $validFilters)) {
                $builder = $builder->where($filter->getIndex(), $filter->getOperator(), $filter->getValue());
            }
        }

        return $builder;
    }

    private function addFilter($index, $value) {

        $operator = substr($index, -1);

        if($operator == ">" || $operator == "<") {
            $index = substr($index, 0, -1);
        }
        else {
            $operator = "=";
        }

        $operator2 = substr($index, -2);

        if($operator2 == "<e" || $operator2 == ">e") {
            $index = substr($index, 0, -2);
            if($operator2 == "<e")
                $operator = "<=";
            if($operator2 == ">e")
                $operator = ">=";
        }


        $this->filters[] = new Filter($index, $operator, $value);
    }


    protected function validateInput($input, $validationArray, $customAttr = array()) {
        $validator = \Validator::make($input, $validationArray, array(), $customAttr);

        if($validator->fails()) {
            $response['errors'] = $validator->errors();
            return response(json_encode($response), 405);
        }

        return false;

    }
}