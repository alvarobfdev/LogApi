<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 30/3/16
 * Time: 10:00
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class FacturaWeb extends Model
{
    protected $connection = 'mysql';

    protected $table = "facturas_web";

}