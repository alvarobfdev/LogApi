<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 3/8/16
 * Time: 16:34
 */

namespace App\Http\Controllers\WebApp;


use Illuminate\Http\Request;
use App\RestApiModels\User;
use Illuminate\Support\Facades\Response;

class ClientesController extends Controller
{
    public function getCliente($idCliente = null) {

        $filters = [];
        if($idCliente)
            $filters["filter"]["codcli="] = $idCliente;

        $clientes = ApiClient::getClientes($filters);
        return $clientes;
    }

    public function getAutocomplete(Request $request) {

        $query = $request->get("query");


        $params["orFilter"]["nomacc=L"] = "%$query%";
        $params["orFilter"]["nomcli=L"] = "%$query%";
        $params["orFilter"]["codcli="] = "$query";


        $clientes = ApiClient::getClientes($params);
        $clientes = json_decode($clientes);

        $result = [];
        foreach($clientes->data as $cliente) {
            $clienteResult = [];
            $clienteResult['value'] = $cliente->nomcli;
            $clienteResult['data'] = $cliente->codcli;
            $result[] = $clienteResult;
        }

        die(json_encode(["query"=>"Unit","suggestions"=>$result]));

    }
}