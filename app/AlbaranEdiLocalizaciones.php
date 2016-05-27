<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 28/4/16
 * Time: 10:48
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AlbaranEdiLocalizaciones extends Model
{
    protected $connection = 'mysql';

    protected $table = "albaran_edi_localizaciones";
}