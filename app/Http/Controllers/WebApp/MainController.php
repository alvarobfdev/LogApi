<?php

namespace App\Http\Controllers\WebApp;

/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 26/7/16
 * Time: 8:54
 */
class MainController extends Controller
{
    public function getIndex() {
               return view("webapp.home");
    }

    public function getTemplate() {
        $seccion = \Request::get("seccion");
        $page = \Request::get("page");

        return view("webapp.$seccion.$page");
    }
}