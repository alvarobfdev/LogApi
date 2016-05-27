<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 24/5/16
 * Time: 11:47
 */

namespace App\RestApiModels;


class LineasPedido extends Model
{
    public static $validation = [
        'codart' => 'required_without:codean',
        'cantid' => 'required|numeric',
        'codean' => 'required_without:codart',
        'bultos' => 'numeric|integer'
    ];
}