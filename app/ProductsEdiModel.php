<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 21/3/16
 * Time: 10:48
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ProductsEdiModel extends Model
{
    protected $connection = 'mysql';

    protected $table = "productos_edi";


}