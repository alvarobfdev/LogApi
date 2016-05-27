<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 19/4/16
 * Time: 13:00
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AlbaranEdiPalets extends Model
{
    protected $connection = 'mysql';

    protected $table = "albaran_edi_palets";
}