<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 1/8/16
 * Time: 11:37
 */

namespace App\Http\Controllers\WebApp;

class PedidosController extends Controller
{

    public function getLast($page = 1) {
        $pedidos = ApiClient::getPedidos(["orderby"=>'updated_at', 'order'=>'desc', 'page'=>$page]);
        return $pedidos;
    }

    public function getByClient($client, $page=1) {

        $pedidos = ApiClient::getPedidos(["filter"=>["codcli="=>$client], "orderby"=>'updated_at', 'order'=>'desc', 'page'=>$page]);
        return $pedidos;
    }
}