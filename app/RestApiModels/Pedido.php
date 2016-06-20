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
        'ejeped' => 'required|integer',
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
        'serpar' => 'required|boolean',
        'reserv' => 'required|boolean',
        'observ' => 'string'
    ];

    public static $adminValidation = [
        'codcli' => 'required|integer',
        'inddis' => 'required|in:S,D,P,N',
        'totbul' => 'integer',
        'totkil' => 'numeric',
        'totvol' => 'numeric',
        'reembo' => 'numeric',
        'imptot' => 'numeric',
        'transp' => 'string',
        'estado' => 'required|in:P,F,A',
        'envweb' => 'required|boolean',
        'pobdis' => 'string',
        'cpodis' => 'string',
        'nomfis' => 'string',
        'refped' => 'string',
        'valora' => 'required|boolean',
        'apliva' => 'required|in:N,L,T',
        'tipiva' => 'numeric',
        'ejeope' => 'integer',
        'numope' => 'integer',
        'txtven' => 'string',
        'okpick' => 'string'
    ];

    public static $validationUpdate = [
        'ejeped' => 'integer',
        'tipped' => 'in:E,S',
        'numped' => 'string|unique_pedido',
        'linped' => 'array',
        'fecent' => 'date_format:Y-m-d',
        'nomter' => 'string',
        'dirter' => 'string',
        'pobter' => 'string',
        'provter' => 'string',
        'cpter' => 'string',
        'tlfter' => 'array|not_empty_array',
        'portes' => 'in:P,S,N,D',
        'serpar' => 'boolean',
        'reserv' => 'boolean',
    ];

    public static $adminValidationUpdate = [
        'codcli' => 'integer',
        'ejeped' => 'integer',
        'inddis' => 'in:S,D,P,N',
        'totbul' => 'integer',
        'totkil' => 'numeric',
        'totvol' => 'numeric',
        'reembo' => 'numeric',
        'imptot' => 'numeric',
        'transp' => 'string',
        'estado' => 'in:P,F,A',
        'envweb' => 'boolean',
        'pobdis' => 'string',
        'cpodis' => 'string',
        'nomfis' => 'string',
        'refped' => 'string',
        'valora' => 'boolean',
        'apliva' => 'in:N,L,T',
        'tipiva' => 'numeric',
        'ejeope' => 'integer',
        'numope' => 'integer',
        'txtven' => 'string',
        'okpick' => 'string'
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

    public static $adminValidationFilters = [
        'codcli' => 'integer'
    ];


    public static $showable = [
        'tipped',
        'numped',
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