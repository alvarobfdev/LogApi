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
    private static $CONSUMER_KEY = "ck_5e7db36b0c8c853e6fb3cc7ddfdaeea643df33dc";
    private static $CONSUMER_SECRET = "cs_fcb19a187a3c5977c835020c2ace3781e55bd89c";
    //Example: http://www.example.com
    private static $HOST = "http://www.thesuntime.com";
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