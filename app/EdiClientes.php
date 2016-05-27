<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 26/4/16
 * Time: 9:32
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class EdiClientes extends Model
{
    protected $connection = 'mysql';

    protected $table = "edi_clientes";
    public $timestamps = false;
}