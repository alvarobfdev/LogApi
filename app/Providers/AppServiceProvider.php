<?php

namespace App\Providers;

use App\RestApiModels\Articulo;
use App\RestApiModels\Cliente;
use App\RestApiModels\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {


        \Validator::extend('unique_pedido', function($attribute, $value, $parameters, $validator) {


            $data = $validator->getData();
            $attr = $validator->getCustomAttributes();
            $tipped = $data["tipped"];
            $ejercicio = $data["ejeped"];
            $user = \Session::get("user");
            $codcli = $user->codcli;


            if(array_key_exists("prevNumped", $attr) && $value == $attr["prevNumped"]) {
                return true;
            }

            if($user->isAdmin == 1) {
                $codcli = $data["codcli"];
            }

            $numPedidos = Pedido::where("codcli", $codcli)->where("tipped", $tipped)->where("ejeped", $ejercicio)
                ->where("numped", $value)->count();
            return $numPedidos < 1;

        });

        \Validator::extend('not_empty_array', function($attribute, $value, $parameters, $validator) {
            if(!is_array($value)) {
                return false;
            }

            foreach($value as $item) {
                if(is_string($item) && strlen($item) > 0) {
                    return true;
                }
            }

            return false;
        });

        \Validator::extend('exist_articulo', function($attribute, $value, $parameters, $validator) {

            $attr = $validator->getCustomAttributes();
            $codcli = $attr["codcli"];

            $articulos = Articulo::where("codart", $value)->where("codcli", $codcli)->count();

            return $articulos > 0;
        });

        \Validator::extend('unique_codart', function($attribute, $value, $parameters, $validator) {


            $attr = $validator->getCustomAttributes();
            $codcli = $attr["codcli"];

            if(array_key_exists("prevCodart", $attr) && $value == $attr["prevCodart"]) {
                return true;
            }

            $articulos = Articulo::where("codart", $value)->where("codcli", $codcli)->count();

            return $articulos == 0;
        });

        \Validator::extend('unique_codean', function($attribute, $value, $parameters, $validator) {

            $attr = $validator->getCustomAttributes();

            if(array_key_exists("prevCodean", $attr) && $value == $attr["prevCodean"]) {
                return true;
            }

            $articulos = Articulo::where("codean", $value)->count();

            return $articulos == 0;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}


