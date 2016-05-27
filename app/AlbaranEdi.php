<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 28/4/16
 * Time: 12:16
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AlbaranEdi extends Model
{
    protected $connection = 'mysql';

    protected $table = "albaran_edi";
}