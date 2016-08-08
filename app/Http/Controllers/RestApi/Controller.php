<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 17:05
 */

namespace App\Http\Controllers\RestApi;


use App\RestApiModels\Filter;
use App\RestApiModels\Model;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;

class Controller extends \App\Http\Controllers\Controller
{
    protected $limitPerPage = 15;
    protected $maxLimit = 50;
    protected $orderby = null;
    protected $order = null;
    const VALID_INPUT = 1;


    /**
     * @var Filter array
     */
    protected $filters = [];
    protected $orFilters = [];
    protected $orQuery = null;


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

        if($request->has("orFilter")) {
            if(!is_array($request->get("orFilter"))) {
                $response['errors'] = ["Filter input must be an array"];
                return response(json_encode($response), 405);
            }

            $filters = $request->get("orFilter");
            foreach($filters as $index => $value) {
                $this->addOrFilter($index, $value);
            }
        }

        if($request->has("orderby")) {
            $this->orderby = $request->get("orderby");
            $this->order = "ASC";
            if($request->has("order") && $request->get("order") == "desc") {
                $this->order = "DESC";
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
                $value = $filter->getValue();
                $builder = $builder->where($filter->getIndex(), $filter->getOperator(), $value);
            }
        }

        if(count($this->orFilters) > 0) {
            $builder = $builder->where(function($query) use ($validFilters) {
                foreach($this->orFilters as $filter) {
                    /* @var $filter Filter*/
                    if(array_key_exists($filter->getIndex(), $validFilters)) {
                        if($this->orQuery != null)
                            $query = $this->orQuery;
                        $this->orQuery = $query->orWhere($filter->getIndex(), $filter->getOperator(), $filter->getValue());
                    }
                }

            });
        }
        return $builder;
    }

    private function addOrFilter($index, $value) {

        list($operator, $index) = $this->getOperatorIndex($index);
        $this->orFilters[] = new Filter($index, $operator, $value);
    }

    private function addFilter($index, $value) {

        list($operator, $index) = $this->getOperatorIndex($index);
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

    protected function getBuildedCollection($class, $collection, $user, $request) {
        $validFilters = $class::$validationFilters;
        $clientes = $this->getFilteredBuilder($validFilters, $collection);
        if($user->isAdmin == 1){
            $validFilters = $class::$adminValidationFilters;
            $clientes = $this->getFilteredBuilder($validFilters, $clientes);
        }

        if($request->has("limit")) {
            $limit = $request->get("limit");
            $this->limitPerPage = ($limit < $this->maxLimit) ? $limit : $this->maxLimit;
        }

        if($this->orderby) {
            $clientes = $clientes->orderby($this->orderby, $this->order);
        }

        if($user->isAdmin == 1) {
            return $clientes->paginate($this->limitPerPage);
        }

        else return $clientes->select($class::$showable)->paginate($this->limitPerPage);
    }

    /**
     * @param $index
     * @return array
     */
    private function getOperatorIndex($index)
    {
        $name = $index;
        $operator = substr($index, -1);
        $index = substr($index, 0, -1);

        $operator2 = substr($name, -2);

        if ($operator2 == "<e" || $operator2 == ">e" || $operator2 == "=L") {
            $index = substr($name, 0, -2);
            if ($operator2 == "<e")
                $operator = "<=";
            if ($operator2 == ">e")
                $operator = ">=";
            if($operator2 == "=L") {
                $operator = "like";
            }
            return array($operator, $index);
        }
        return array($operator, $index);
    }
}