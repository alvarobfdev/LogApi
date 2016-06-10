<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 24/5/16
 * Time: 11:41
 */

namespace App\RestApiModels;


class Pedido extends Model
{

    public static $validation = [
        'tipped' => 'required|in:E,S',
        'numped' => 'required|unique_pedido',
        'linped' => 'required|array',
        'fecent' => 'date_format:Y-m-d',
        'codter' => 'string',
        'nomter' => 'required',
        'dirter' => 'required',
        'pobter' => 'required',
        'provter' => 'required',
        'cpter' => 'required',
        'tlfter' => 'required|array|not_empty_array',
        'portes' => 'required|in:P,S,N,D',
        'serpar' => 'required|in:S,N',
        'reserv' => 'required|in:S,N',
    ];

    public static $adminValidation = [
        'codcli' => 'required|integer',
    ];

    public static $validationUpdate = [
        'tipped' => 'in:E,S',
        'numped' => 'string',
        'linped' => 'array',
        'fecent' => 'date_format:Y-m-d',
        'nomter' => 'string',
        'dirter' => 'string',
        'pobter' => 'string',
        'provter' => 'string',
        'cpter' => 'string',
        'tlfter' => 'array|not_empty_array',
        'portes' => 'in:P,S,N,D',
        'serpar' => 'in:S,N',
        'reserv' => 'in:S,N',
    ];

    public static $validationFilters = [
        'tipped' => 'in:E,S',
        'numped' => 'string',
        'fecent' => 'date_format:Y-m-d',
        'nomter' => 'string',
        'dirter' => 'string',
        'pobter' => 'string',
        'provter' => 'string',
        'cpter' => 'string',
        'portes' => 'in:P,S,N,D',
        'limit' => 'integer',
        'page' => 'integer',

    ];

    public static $showable = [
        'tipped',
        'numped',
        'linped',
        'fecent',
        'nomter',
        'dirter',
        'pobter',
        'provter',
        'cpter',
        'tlfter',
        'portes',
        'serpar',
        'reserv'
    ];


    public function lineasPedido() {
        return $this->embedsMany('App\RestApiModels\LineasPedido');
    }
}