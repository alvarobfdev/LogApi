<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/3/16
 * Time: 20:03
 */

namespace App;


class WoocommerceApi
{
    public static $CONSUMER_KEY = "ck_5e7db36b0c8c853e6fb3cc7ddfdaeea643df33dc";
    public static $CONSUMER_SECRET = "cs_fcb19a187a3c5977c835020c2ace3781e55bd89c";
    //Example: http://www.example.com
    public static $HOST = "http://www.thesuntime.com";
    private static $me = null;



    /**
     * @return WoocommerceApi|null
     */
    public static function getInstance() {
        if(self::$me == null) {
            self::$me = new WoocommerceApi();
        }
        return self::$me;
    }


    /**
     * @param array $params
     * @return mixed
     */
    public static function getProducts($params = array())
    {
        $me = self::getInstance();
        $url = $me->getEncodedUrl("GET", "products", $params);
        $respone = file_get_contents($url);
        $products = json_decode($respone);
        return $products;
    }

    public static function countProducts($params = array()) {
        $me = self::getInstance();
        $url = $me->getEncodedUrl("GET", "products/count", $params);
        $respone = file_get_contents($url);
        $products = json_decode($respone);
        return $products->count;
    }

    public static function getOrders($params = array()) {
        $me = self::getInstance();
        $url = $me->getEncodedUrl("GET", "orders", $params);
        $respone = file_get_contents($url);
        $products = json_decode($respone);
        return $products;
    }

    public static function updateProducts($products = array()) {
        $me = self::getInstance();
        $data["products"] = $products;
        $url = $me->getEncodedUrl("POST", "products/bulk");
        $aHTTP = array(
            'http' => // The wrapper to be used
                array(
                    'method'  => 'POST', // Request Method
                    // Request Headers Below
                    'header'  => 'Content-type: application/json',
                    'content' => json_encode($data)
                )
        );

        $context = stream_context_create($aHTTP);
        $contents = @file_get_contents($url, false, $context);
        if($contents === FALSE) {
            var_dump($contents);
            var_dump($aHTTP);
        }
        return json_decode($contents);


    }

    public static function updateOrders($orders = array()) {
        $me = self::getInstance();
        $data["orders"] = $orders;
        $url = $me->getEncodedUrl("POST", "orders/bulk");
        $aHTTP = array(
            'http' => // The wrapper to be used
                array(
                    'method'  => 'POST', // Request Method
                    // Request Headers Below
                    'header'  => 'Content-type: application/json',
                    'content' => json_encode($data)
                )
        );

        $context = stream_context_create($aHTTP);
        $contents = file_get_contents($url, false, $context);
        return json_decode($contents);


    }

    private function getRequestTokenUrl($requestObject) {
        return self::$HOST."/wc-api/v3/".$requestObject;
    }
    private function getEncodedUrl($method = "GET", $requestObject, $params = array()) {

        $requestTokenUrl = $this->getRequestTokenUrl($requestObject);
        $params = $this->getParams($method, $requestObject, $params);
        $urlParams = "";

        foreach($params as $index=>$param) {
            $urlParams .= "&".rawurlencode($index)."=".rawurlencode($param);
        }

        $urlParams = ltrim($urlParams, '&');

        $requestUrl = $requestTokenUrl . "?" . $urlParams;

        return $requestUrl;
    }

    private function getParams($method, $requestObject, $extraParams = array()) {

        $requestTokenUrl = $this->getRequestTokenUrl($requestObject);
        $params['oauth_timestamp'] = time();
        $params['oauth_nonce'] = md5(mt_rand());
        $params['oauth_signature_method'] = "HMAC-SHA1";
        $params['oauth_version'] = "1.0";
        $params['oauth_consumer_key'] = self::$CONSUMER_KEY;

        foreach($extraParams as $index=>$param) {
            $params[$index] = $param;
        }


        $consumerSecret = self::$CONSUMER_SECRET;


        uksort( $params, 'strcmp' );

        $sigBase = $method."&" . rawurlencode($requestTokenUrl)."&";
        $urlParams = "";


        foreach($params as $index=>$param) {
            $urlParams .= "&".rawurlencode($index)."=".rawurlencode($param);
        }

        $urlParams = ltrim($urlParams, '&');


        $sigBase .= rawurlencode($urlParams);


        $sigKey = $consumerSecret . "&";
        $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));
        $params['oauth_signature'] = $oauthSig;
        return $params;
    }
}