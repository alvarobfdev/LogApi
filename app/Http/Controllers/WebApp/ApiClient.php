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

    /**
     * @return ApiClient|null
     */
    public static function getInstance() {
        if(self::$me == null) {
            self::$me = new ApiClient();
        }
        return self::$me;
    }

    public static function getClientes($params = array()) {

        //$url = $me->getEncodedUrl("clientes", $params);
        //$result = $me->callApiUrl($url);
        $result = self::getResultFromApi("clientes", "GET", $params);
        return $result;
    }

    public static function getPedidos($params = array()) {
        $result = self::getResultFromApi("pedidos", "GET", $params);
        return $result;
    }

    private function getRequest($seccion, $method = 'GET', $params = array()) {
        $request = \Request::create(self::$URI."/$seccion", $method, $params);
        $request->headers->set('Content-type', 'application/json');
        $request->headers->set('Auth-Key', self::$AUTH_KEY);
        $request->headers->set('Auth-Token', self::$AUTH_TOKEN);
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
    private static function getResultFromApi($section, $method, $params)
    {
        $me = self::getInstance();
        $request = $me->getRequest($section, $method, $params);
        $result = \Route::dispatch($request);
        return $result->original->toJson();
    }


}