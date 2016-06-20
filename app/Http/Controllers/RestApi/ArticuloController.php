<?php

namespace App\Http\Controllers\RestApi;

use App\RestApiModels\Articulo;
use App\RestApiModels\LineasPedido;
use App\RestApiModels\Pedido;
use App\RestApiModels\User;
use Illuminate\Http\Request;

class ArticuloController extends Controller
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

        $validator = \Validator::make($this->filters, Articulo::$validationFilters);

        if($validator->fails()) {
            $response['errors'] = $validator->errors();
            return response(json_encode($response), 405);
        }

        $user = \Session::get("user");

        if($user->isAdmin == 1) {
            $articulos = Articulo::where("codcli", "!=", "");
        }
        else $articulos = Articulo::where("codcli", $user->codcli);

        $validFilters = Articulo::$validationFilters;

        $articulos = $this->getFilteredBuilder($validFilters, $articulos);

        if($user->isAdmin == 1){
            $validFilters = Pedido::$adminValidationFilters;
            $articulos = $this->getFilteredBuilder($validFilters, $articulos);
        }


        if($request->has("limit")) {
            $limit = $request->get("limit");
            $this->limitPerPage = ($limit < $this->maxLimit) ? $limit : $this->maxLimit;
        }

        if($user->isAdmin == 1) {
            return $articulos->paginate($this->limitPerPage);
        }
        else {
            return $articulos->select(Articulo::$showable)->paginate($this->limitPerPage);

        }
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

            if (!$request->has("articulos")) {
                return response('Bad Input', 405);
            }

            $articulosToSave = [];

            foreach($request->get("articulos") as $articulo) {

                if($user->isAdmin == 1) {
                    $codcli = $articulo["codcli"];
                    if ($response = $this->validateInput($articulo, Articulo::$adminValidation))
                        return $response;
                }

                if($response = $this->validateInput($articulo, Articulo::$validation, ["codcli" => $codcli])) {
                    return $response;
                }

                $articuloDb = $this->fillArticuloDb(new Articulo(), $articulo, Articulo::$validation);

                if($user->isAdmin == 1)
                    $articuloDb = $this->fillArticuloDb($articuloDb, $articulo, Articulo::$adminValidation);



                $articulosToSave[] = $articuloDb;

            }

            foreach($articulosToSave as $articuloDb) {
                $articuloDb->codcli = $codcli;
                $articuloDb->save();
            }

            return $articulosToSave;
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

            $articulo = Articulo::where("_id", $id);

            if(!$user->isAdmin){
                $articulo = $articulo->where("codcli", $user->codcli);
            }

            if ($articulo->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }

            if($user->isAdmin == 1) {
                return $articulo->first();
            }
            else {
                return $articulo->select(Articulo::$showable)->first();
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

            if(!$request->has("articulo")) {
                $response['errors'] = ["Articulo object not found!"];
                return response(json_encode($response), 405);
            }
            $articulo = $request->get("articulo");
            $user = \Session::get("user");
            $codcli = $user->codcli;

            $articuloDb = Articulo::where("_id", $id);


            if($user->isAdmin == 1){
                $codcli = $articulo["codcli"];
            }
            else {
                $articuloDb = $articuloDb->where("codcli", $codcli);
            }

            if ($articuloDb->count() < 1) {
                $response['errors'] = ["Wrong id"];
                return response(json_encode($response), 405);
            }

            $codart = $articuloDb->first()->codart;
            $codean = $articuloDb->first()->codean;


            if($response = $this->validateInput($articulo, Articulo::$validationUpdate, [
                "codcli"=>$codcli,
                "prevCodart"=>$codart,
                "prevCodean" => $codean
            ])) {
                return $response;
            }

            if($user->isAdmin == 1) {
                if($response = $this->validateInput($articulo, Articulo::$validationAdminUpdate, [
                    "codcli"=>$codcli,
                    "prevCodart"=>$codart,
                    "prevCodean" => $codean
                ])) {
                    return $response;
                }
            }

            $articuloDb = $this->fillArticuloDb($articuloDb->first(), $articulo, Articulo::$validationUpdate);

            if($user->isAdmin == 1) {
                $articuloDb = $this->fillArticuloDb($articuloDb, $articulo, Articulo::$validationAdminUpdate);
            }

            $articuloDb->save();

            if($user->isAdmin == 1) {
                return $articuloDb;
            }
            else {
                return $articuloDb->where("_id", $id)->select(Articulo::$showable)->first();
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
                Articulo::destroy($id);
            } else {
                $articulo = Articulo::where("_id", $id)->where("codcli", $codcli)->first();
                if(!$articulo) {
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

    private function fillArticuloDb($articuloDb, $articuloRequest, $validation) {

        foreach($articuloRequest as $index=>$value) {
            if((array_key_exists($index, $validation) && !is_array($value))) {
                $articuloDb->$index = $value;
            }
        }
        return $articuloDb;
    }
}
