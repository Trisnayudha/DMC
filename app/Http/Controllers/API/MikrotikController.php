<?php

namespace App\Http\Controllers\API;

use \RouterOS\Client;
use \RouterOS\Query;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    public function process(){

        // Initiate client with config object
        $client = new Client([
            'host' => '192.168.2.1',
            'user' => 'admin',
            'pass' => '',
            'port' => 8728,
        ]);

        // Create "where" Query object for RouterOS
        $query =
            (new Query('/ip/hotspot/ip-binding/print'))
                ->where('mac-address', '00:00:00:00:40:29');

        // Send query and read response from RouterOS
        $response = $client->query($query)->read();

        var_dump($response);
    }
}
