<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/3/16
 * Time: 11:39
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class FormaPagos extends Model
{
    protected $table = "forpagos";

    public static function getFormaPago($forpag) {
        return self::where("forpag", $forpag)->first()->descri;
    }
}