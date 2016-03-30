<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/3/16
 * Time: 11:37
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = "paises";

    public static function getPais($pais) {
        return self::where("pais", $pais)->first()->nombre;
    }
}