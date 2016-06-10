<?php

namespace App\Providers;

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
            $tipped = $data["tipped"];
            $user = \Session::get("user");
            $ejercicio = Carbon::now()->year;
            $numPedidos = Pedido::where("codcli", $user->codcli)->where("tipped", $tipped)->where("ejeped", $ejercicio)
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
