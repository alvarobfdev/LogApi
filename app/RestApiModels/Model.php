<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 24/5/16
 * Time: 12:28
 */

namespace App\RestApiModels;


class Model extends \Moloquent
{
   public static $validation = [];



   public static $adminValidation = [];

   public static $validationUpdate = [];
   public static $adminValidationUpdate = [];
   public static $validationFilters = [];
   public static $adminValidationFilters = [];
   public static $showable = [];



}