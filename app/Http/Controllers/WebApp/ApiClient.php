<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 2/8/16
 * Time: 8:44
 */

namespace App\Http\Controllers\WebApp;


use Illuminate\Http\Request;

class ApiClient
{
    public static $AUTH_KEY = "bzDfZM0EBnsPiYTwhJlq2G3arUyiNNR4N8y2wrWrKvriBTRzpYKpDVuTipwR";
    public static $AUTH_TOKEN = "F3TrtxuSkBiITeuRPz7rUOF5PaPE9zql6NYP3ZH0F9RmdVK7evK7LdmruYrY";
    //Example: http://www.example.com
    public static $HOST = "http://localhost/logivalApi/public/api/v1";
    public static $URI = "/api/v1";
    private static $me = null;
    private static $lastResult = 0;

    /**
     * @return ApiClient|null
     */
    public static function getInstance() {
        if(self::$me == null) {
            self::$me = new ApiClient();
        }
        return self::$me;
    }

    public static function getClientes($params = array(), &$status = 200) {

        //$url = $me->getEncodedUrl("clientes", $params);
        //$result = $me->callApiUrl($url);
        $result = self::getResultFromApi("clientes", "GET", $params);
        $status = self::$lastResult;
        return $result;
    }

    public static function getPedidos($params = array(), &$status = 200) {
        $result = self::getResultFromApi("pedidos", "GET", $params);
        $status = self::$lastResult;
        return $result;
    }

    public static function updatePedido($idPedido, &$status = 200) {
        $result = self::getResultFromApi("pedidos", "PUT", array(), $idPedido);
        $status = self::$lastResult;
        return $result;
    }

    private function getRequest($seccion, $method = 'GET', $params = array(), $id = "") {
        $request = \Request::create(self::$URI."/$seccion/$id", $method, $params);
        $request->headers->set('Content-type', 'application/json');
        $request->headers->set('Auth-Key', self::$AUTH_KEY);
        $request->headers->set('Auth-Token', self::$AUTH_TOKEN);
        if($method == "GET")
            \Request::replace($request->input());
        return $request;
    }

    private function getRequestTokenUrl($requestObject) {
        return self::$HOST."/".$requestObject;
    }

    private function getUrlParams($params = array()) {


        $urlParams = "";

        foreach($params as $index=>$param) {
            $urlParams .= "&".rawurlencode($index)."=".rawurlencode($param);
        }

        $urlParams = ltrim($urlParams, '&');

        return $urlParams;
    }

    private function getEncodedUrl($requestObject, $params = array()) {

        $requestTokenUrl = $this->getRequestTokenUrl($requestObject);
        $urlParams = $this->getUrlParams($params);
        $requestUrl = $requestTokenUrl . "?" . $urlParams;
        return $requestUrl;
    }

    private function callApiUrl($url, $content = "", $method = "GET") {

        clock()->startEvent('call_api_url', 'Calling Api...');
        $aHTTP = array(
            'http' => // The wrapper to be used
                array(
                    'method'  => $method, // Request Method
                    // Request Headers Below
                    'header'  => "Content-type: application/json\r\n" .
                        "Auth-Key: ".self::$AUTH_KEY."\r\n".
                        "Auth-Token: ".self::$AUTH_TOKEN."\r\n",
                    'content' => json_encode($content)
                )
        );

        $context = stream_context_create($aHTTP);

        $contents = file_get_contents($url, false, $context);
        clock()->endEvent('event_name');
        return json_decode($contents);
    }

    /**
     * @param $params
     * @return \Illuminate\Http\Response|mixed
     */
    private static function getResultFromApi($section, $method, $params = array(), $id = "")
    {
        $me = self::getInstance();
        $request = $me->getRequest($section, $method, $params, $id);
        $result = \Route::dispatch($request);
        self::$lastResult = $result->status();
        if(self::isJson($result->original))
            return $result->original;
        else return $result->original->toJson();
    }

    private static function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }


}