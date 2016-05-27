<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/5/16
 * Time: 9:06
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AlbaranFisicoEdi extends Model
{
    protected $connection = 'mysql';

    protected $table = "albaran_fisico_edi";
}