<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 31/3/16
 * Time: 11:54
 */

namespace App\Http\Controllers;


use App\CtsqlExport;

class ClienteController extends Controller
{
    public function getIndex() {
        return view("admin.clientes.index");
    }

    public function getObtener() {
        $cod_cliente = \Request::get("cod_cliente");
        $cliente = CtsqlExport::ctsqlExport("SELECT * FROM clientes where codcli =".$cod_cliente);
        $albaranes = CtsqlExport::ctsqlExport(
            "SELECT * FROM albaran WHERE codemp = 1 AND coddel=1 AND codcli=$cod_cliente ORDER BY fecalb DESC LIMIT 0, 10"
        );

        $pedidos = CtsqlExport::ctsqlExport(
            "SELECT * FROM pedidos WHERE codemp=1 AND coddel=1 and codcli=$cod_cliente ORDER BY fecped DESC LIMIT 0, 10"
        );

        $result['cliente'] = json_decode($cliente[0]);
        $result['albaranes'] = json_decode($albaranes[0]);
        $result['pedidos'] = json_decode($pedidos[0]);

        return json_encode($result);

    }


}