<?php


namespace App\Http\Controllers;


use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;
use  GateApi\Api\DeliveryApi;

class GateControllers extends BaseController
{

    public function order(){
        $apiInstance = new DeliveryApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
            new Client()
        );
    }
}
