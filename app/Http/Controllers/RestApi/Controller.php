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
    protected $limitPerPage = 15;
    protected $maxLimit = 50;
}