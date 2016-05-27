<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/3/16
 * Time: 11:34
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $connection = 'mysql';

    protected $table = "paispro";

    public static function getProvincia($provin) {
        return self::where("provin", $provin)->first()->nombre;
    }

}