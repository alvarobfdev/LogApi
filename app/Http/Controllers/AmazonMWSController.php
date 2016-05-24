<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 19/5/16
 * Time: 13:22
 */

namespace App\Http\Controllers;


class AmazonMWSController extends Controller
{
    public function getTest() {
        $service = new \MarketplaceWebService_Client(
            AWS_ACCESS_KEY_ID,
            AWS_SECRET_ACCESS_KEY,
            array(),
            APPLICATION_NAME,
            APPLICATION_VERSION);
    }
}