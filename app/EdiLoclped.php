<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 25/4/16
 * Time: 12:02
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class EdiLoclped extends Model
{    protected $connection = 'mysql';

    protected $table = "edi_loclped";
    public $timestamps = false;
}