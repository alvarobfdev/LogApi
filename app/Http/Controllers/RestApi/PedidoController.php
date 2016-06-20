<?php

namespace App\Http\Controllers\RestApi;

use App\RestApiModels\LineasPedido;
use App\RestApiModels\Pedido;
use App\RestApiModels\User;
use Illuminate\Http\Request;

class PedidoController extends Controller
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

        $validator = \Validator::make($this->filters, Pedido::$validationFilters);

        if($validator->fails()) {
            $response['errors'] = $validator->errors();
            return response(json_encode($response), 405);
        }

        $user = \Session::get("user");

        if($user->isAdmin == 1) {
            $pedidos = Pedido::where("codcli", "!=", "");
        }
        else $pedidos = Pedido::where("codcli", $user->codcli);

        $validFilters = Pedido::$validationFilters;
        $pedidos = $this->getFilteredBuilder($validFilters, $pedidos);

        if($user->isAdmin == 1){
            $validFilters = Pedido::$adminValidationFilters;
            $pedidos = $this->getFilteredBuilder($validFilters, $pedidos);
        }

        if($request->has("limit")) {
            $limit = $request->get("limit");
            $this->limitPerPage = ($limit < $this->maxLimit) ? $limit : $this->maxLimit;
        }

        if($user->isAdmin == 1) {
            return $pedidos->paginate($this->limitPerPage);
        }

        else return $pedidos->select(Pedido::$showable)->paginate($this->limitPerPage);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $user = \Session::get("user");
            $codcli = $user->codcli;

            if (!$request->has("pedidos")) {
                return response('Bad Input', 405);
            }

            $pedidosToSave = [];

            foreach($request->get("pedidos") as $pedido) {

                if($user->isAdmin == 1) {
                    $codcli = $pedido["codcli"];
                    if ($response = $this->validateInput($pedido, Pedido::$adminValidation))
                        return $response;
                }

                if($response = $this->validateInput($pedido, Pedido::$validation, ["codcli" => $codcli])) {
                    return $response;
                }

                $pedidoDb = $this->fillPedidoDb(new Pedido(), $pedido, Pedido::$validation);

                if($user->isAdmin == 1)
                    $pedidoDb = $this->fillPedidoDb($pedidoDb, $pedido, Pedido::$adminValidation);


                foreach($pedido["linped"] as $linea) {

                    if($response = $this->validateInput($linea, LineasPedido::$validation, ["codcli"=>$codcli]))
                        return $response;

                    $lineaPedido = $this->fillLineaPedidoDb(new LineasPedido(), $linea, LineasPedido::$validation);

                    if($user->isAdmin == 1)
                        $lineaPedido = $this->fillLineaPedidoDb($lineaPedido, $linea, LineasPedido::$adminValidation);

                    $pedidoDb->lineasPedido()->create($lineaPedido->toArray());

                }

                $pedidosToSave[] = $pedidoDb;

            }

            foreach($pedidosToSave as $pedidoDb) {
                $pedidoDb->save();
            }

            return $pedidosToSave;
        }
        catch(\Exception $e) {
            return response('Internal Server Error on '. $e->getFile(). ' at line '.$e->getLine(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = \Session::get("user");

            $pedido = Pedido::where("_id", $id);

            if(!$user->isAdmin){
                $pedido = $pedido->where("codcli", $user->codcli);
            }

            if ($pedido->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }

            if($user->isAdmin == 1) {
                return $pedido->first();
            }
            else {
                return $pedido->select(Pedido::$showable)->first();
            }
        }
        catch(\Exception $e) {
            return response('Internal Server Error.', 500);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = \Session::get("user");
            $codcli = $user->codcli;

            if(!$request->has("pedido")) {
                $response['errors'] = ["Pedido object not found!"];
                return response(json_encode($response), 405);
            }
            $pedido = $request->get("pedido");

            if($user->isAdmin == 1) {
                $codcli = $pedido["codcli"];
            }

            $pedidoDb = Pedido::where("codcli", $codcli)->where("_id", $id);
            if ($pedidoDb->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }

            $numPed = $pedidoDb->first()->numped;

            if($response = $this->validateInput($pedido, Pedido::$validationUpdate, ["prevNumped"=>$numPed]))
                return $response;

            $pedidoDb = $this->fillPedidoDb($pedidoDb->first(), $pedido, Pedido::$validationUpdate);

            if($user->isAdmin == 1) {

                if($response = $this->validateInput($pedido, Pedido::$adminValidationUpdate, ["prevNumped"=>$numPed]))
                    return $response;

                $pedidoDb = $this->fillPedidoDb($pedidoDb, $pedido, Pedido::$adminValidationUpdate);
            }

            if(array_key_exists("linped", $pedido)) {
                $pedidoDb->lineasPedido()->dissociate();
                $pedidoDb->save();
                foreach($pedido["linped"] as $linea) {
                    $lineaPedido = $this->fillLineaPedidoDb(new LineasPedido(), $linea, LineasPedido::$validation);
                    if($user->isAdmin == 1)
                        $lineaPedido = $this->fillLineaPedidoDb($lineaPedido, $linea, LineasPedido::$adminValidation);

                    $pedidoDb->lineasPedido()->associate($lineaPedido);
                }
            }
            $pedidoDb->save();
            if($user->isAdmin == 1) {
                return $pedidoDb;
            }
            else {
                return $pedidoDb->where("_id", $id)->select(Pedido::$showable)->first();
            }

        }
        catch(\Exception $e) {
            return response('Internal Server Error on '.$e->getMessage().$e->getFile(). ' at line '.$e->getLine(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = \Session::get("user");
            $codcli = $user->codcli;
            if ($user->isAdmin == 1) {
                Pedido::destroy($id);
            } else {
                $pedido = Pedido::where("_id", $id)->where("codcli", $codcli)->first();
                if(!$pedido) {
                    $response['errors'] = ["Wrong id!"];
                    return response(json_encode($response), 405);
                }
            }
            return ["success" => 1];
        }
        catch(\Exception $e) {
            return response('Internal Server Error on '.$e->getMessage().$e->getFile(). ' at line '.$e->getLine(), 500);

        }
    }

    private function fillPedidoDb($pedidoDb, $pedidoRequest, $validation) {

        foreach($pedidoRequest as $index=>$value) {
            if((array_key_exists($index, $validation) && !is_array($value)) ||
                $index == "tlfter"
            ) {
                $pedidoDb->$index = $value;
            }
        }

        return $pedidoDb;
    }

    private function fillLineaPedidoDb($lineaDb, $linea, $validation){

        foreach($linea as $index => $value) {

            if (array_key_exists($index, $validation) && !is_array($value)) {
                $lineaDb->$index = $value;
            }

        }
        return $lineaDb;

    }




}
