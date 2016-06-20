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
        'codart' => 'required_without:codean|exist_articulo',
        'cantid' => 'required|numeric',
        'codean' => 'required_without:codart',
        'bultos' => 'numeric|integer'
    ];

    public static $adminValidation = [
        'kilos' => 'numeric',
        'volume' => 'numeric',
        'precio' => 'numeric',
        'tipiva' => 'numeric',
        'edilin' => 'required|boolean',
        'asocia' => 'integer',
        'nopick' => 'integer',
        'lnpick' => 'integer',
        'codkit' => 'string'
    ];



    public function articulo() {
        return $this->hasOne("Articulo", "codart", "codart");
    }
}