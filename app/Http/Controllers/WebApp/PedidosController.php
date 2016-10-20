<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 1/8/16
 * Time: 11:37
 */

namespace App\Http\Controllers\WebApp;

use App\RestApiModels\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PedidosController extends Controller
{

    protected $datesToConvert = [
        "fecent",
        "fecped"
    ];
    public function getLast($page = 1) {
        $pedidos = ApiClient::getPedidos(["orderby"=>'fecped', 'order'=>'desc', 'page'=>$page], $status);
        if($status == 200)
            $pedidos = $this->transformDates($pedidos, "Y-m-d", "d/m/Y");
        return response($pedidos,  $status);
    }

    public function getByClient($client, $page=1) {
        $pedidos = ApiClient::getPedidos(["filter"=>["codcli="=>$client], "orderby"=>'fecped', 'order'=>'desc', 'page'=>$page], $status);
        if($status == 200)
            $pedidos = $this->transformDates($pedidos, "Y-m-d", "d/m/Y");
        return response($pedidos, $status);
    }

    public function getById($id)
    {
        $pedido = ApiClient::getPedidos(["filter" => ["_id=" => $id], "relations"=>['cliente', 'lineasPedido']], $status);
        if($status == 200)
            $pedido = $this->transformDates($pedido, "Y-m-d", "d/m/Y");
        return response($pedido, $status);
    }

    public function postSave(Request $request, $idPedido = null) {

        $data = $request->all();
        $this->transformPedidoDates($data, "d/m/Y", "Y-m-d");
        $input["pedido"] = $data;
        $request->replace($data);
        $result = ApiClient::updatePedido($idPedido, $status);
        return response($result, $status);
    }

    private function transformDates($pedidos, $dateFormatFrom = 'Y-m-d', $dateFormatTo = 'd/m/Y') {
        $pedidos = json_decode($pedidos);


        foreach($pedidos->data as &$pedido) {
            $pedido = $this->transformPedidoDates($pedido, $dateFormatFrom, $dateFormatTo);
        }
        $pedidos = json_encode($pedidos);
        return $pedidos;
    }

    private function transformPedidoDates($pedido, $dateFormatFrom, $dateFormatTo) {
        foreach($this->datesToConvert as $dateName) {
            if(is_object($pedido) && property_exists($pedido, $dateName)) {
                $pedido->$dateName = Carbon::createFromFormat($dateFormatFrom, $pedido->$dateName)->format($dateFormatTo);

            }
            else if(is_array($pedido) && array_key_exists($dateName, $pedido)) {
                $pedido[$dateName] = Carbon::createFromFormat($dateFormatFrom, $pedido[$dateName])->format($dateFormatTo);

            }
        }
        return $pedido;
    }
}