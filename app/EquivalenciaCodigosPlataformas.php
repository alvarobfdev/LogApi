<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 19/4/16
 * Time: 12:48
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class EquivalenciaCodigosPlataformas extends Model
{
    protected $connection = 'mysql';

    protected $table = "equivalencia_codigos_plataformas";
}