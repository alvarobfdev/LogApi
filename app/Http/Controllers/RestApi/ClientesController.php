<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 3/8/16
 * Time: 16:38
 */

namespace App\Http\Controllers\RestApi;


use App\RestApiModels\Cliente;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $response = parent::index($request);
        if($response) {
            return $response;
        }

        $validator = \Validator::make($this->filters, Cliente::$validationFilters);

        if($validator->fails()) {
            $response['errors'] = $validator->errors();
            return response(json_encode($response), 405);
        }

        $user = \Session::get("user");
        if($user->isAdmin == 1) {
            $clientes = Cliente::where("codcli", "!=", "");
        }
        else $clientes = Cliente::where("codcli", $user->codcli);

        return $this->getBuildedCollection(Cliente::class, $clientes, $user, $request);
    }

}