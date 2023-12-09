<?php

namespace App\Http\Controllers\API_WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function sectionImage()
    {
        $data = [
            'https://djakarta-miningclub.com/wp-content/uploads/slider56/YP409460.jpeg',
            'https://djakarta-miningclub.com/wp-content/uploads/2023/09/56-DMC_03Acara_460-1.jpg',
            'https://djakarta-miningclub.com/wp-content/uploads/2023/09/DSC4058-scaled.jpg',
            'https://djakarta-miningclub.com/wp-content/uploads/2023/07/DSC03504-1.jpg',
            'https://djakarta-miningclub.com/wp-content/uploads/2023/07/AYM08873.jpg',
            'https://djakarta-miningclub.com/wp-content/uploads/2022/12/DSC00152.jpg'
        ];
        $response['status'] = 200;
        $response['message'] = 'Success';
        $response['payload'] = $data;

        return response()->json($response);
    }
}
