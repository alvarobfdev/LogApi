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
        $pedidos = Pedido::where("codcli", $user->codcli);

        $validFilters = Pedido::$validationFilters;

        $pedidos = $this->getFilteredBuilder($validFilters, $pedidos);

        if($request->has("limit")) {
            $limit = $request->get("limit");
            $this->limitPerPage = ($limit < $this->maxLimit) ? $limit : $this->maxLimit;
        }
        return $pedidos->select(Pedido::$showable)->paginate($this->limitPerPage);
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

            if (!$request->has("pedidos")) {
                return response('Bad Input', 405);
            }

            foreach($request->get("pedidos") as $pedido) {

                if($response = $this->validateInput($pedido, Pedido::$validation)) {
                    return $response;
                }



                $pedidoDb = $this->fillPedidoDb(new Pedido(), $pedido, Pedido::$validation);

                if($user->isAdmin == 1) {

                    if($response = $this->validateInput($pedido, Pedido::$adminValidation))
                        return $response;

                    $pedidoDb = $this->fillPedidoDb($pedidoDb, $pedido, Pedido::$adminValidation);
                }

                foreach($pedido["linped"] as $linea) {

                    if($response = $this->validateInput($linea, LineasPedido::$validation))
                        return $response;

                    $pedidoDb = $this->fillLineaPedidoDb($pedidoDb, $linea, LineasPedido::$validation);

                }

                $pedidoDb->save();
            }
        }
        catch(\Exception $e) {
            return response('Internal Server Error. '.$e->getMessage(), 500);
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
            $pedido = Pedido::where("codcli", $user->codcli)->where("_id", $id);
            if ($pedido->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }
            return $pedido->select(Pedido::$showable)->first();
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
            $pedidoDb = Pedido::where("codcli", $user->codcli)->where("_id", $id);
            if ($pedidoDb->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }

            if(!$request->has("pedido")) {
                $response['errors'] = ["Pedido object not found!"];
                return response(json_encode($response), 405);
            }
            $pedido = $request->get("pedido");


            $validator = \Validator::make($pedido, Pedido::$validationUpdate);
            if ($validator->fails()) {
                $response['errors'] = $validator->errors();
                return response(json_encode($response), 405);
            }





        }
        catch(\Exception $e) {
            return response('Internal Server Error.', 500);
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
        //
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

    private function fillLineaPedidoDb($pedidoDb, $linea, $validation){
        $lineaDb = new LineasPedido();
        foreach($linea as $index => $value) {

            if (array_key_exists($index, $validation) && !is_array($value)) {
                $lineaDb->$index = $value;
            }

        }
        $pedidoDb->lineas()->associate($lineaDb);

        return $pedidoDb;

    }




}
