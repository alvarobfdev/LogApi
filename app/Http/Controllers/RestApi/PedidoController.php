<?php

namespace App\Http\Controllers\RestApi;

use App\RestApiModels\LineasPedido;
use App\RestApiModels\Pedido;
use App\RestApiModels\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class PedidoController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Session::get("user");

        $validator = \Validator::make($request->all(), Pedido::$validationFilters);

        if($validator->fails()) {
            $response['errors'] = $validator->errors();
            return response(json_encode($response), 405);
        }

        $pedidos = Pedido::where("codcli", $user->codcli);

        foreach(Pedido::$validationFilters as $indexValidation=>$validation) {
            if($request->has($indexValidation) && $indexValidation != "limit" && $indexValidation != "page") {
                $pedidos = $pedidos->where($indexValidation, $request->get($indexValidation));
            }
        }

        if($request->has("limit")) {
            $limit = $request->get("limit");
            $this->limitPerPage = ($limit < $this->maxLimit) ? $limit : $this->maxLimit;
        }

        return $pedidos->paginate($this->limitPerPage);
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


            if (!$request->has("pedidos")) {
                return response('Bad Input', 405);
            }

            foreach($request->get("pedidos") as $pedido) {

                $validator = \Validator::make($pedido, Pedido::$validation);

                if($validator->fails()) {
                    $response['errors'] = $validator->errors();
                    return response(json_encode($response), 405);
                }

                foreach($pedido["linped"] as $linea) {
                    $validator = \Validator::make($linea, LineasPedido::$validation);

                    if($validator->fails()) {
                        $response['errors'] = $validator->errors();
                        return response(json_encode($response), 405);
                    }
                }

            }

            return "OK";

        }
        catch(\Exception $e) {
            return response('Internal Server Error.', 500);

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
        //
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
        //
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
}
