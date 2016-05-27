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
        'linped' => 'required:array',
        'fecent' => 'date_format:d/m/Y',
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


    public function lineasPedido() {
        return $this->embedsMany('App\RestApiModels\LineasPedido');
    }
}