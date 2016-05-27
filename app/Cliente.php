<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/3/16
 * Time: 10:39
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $connection = 'mysql';


    protected $table = "clientes";
}