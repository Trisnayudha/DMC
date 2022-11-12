<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TestController extends Controller
{
    public function test()
    {
        return view('test');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $image = QrCode::format('png')
            ->size(300)->errorCorrection('H')
            ->generate('ABDC');
        $output_file = '/public/upload/qr-code/img-' . time() . '.png';
        $db = '/storage/upload/qr-code/img-' . time() . '.png';
        Storage::disk('local')->put($output_file, $image); //storage/app/public/img/qr-code/img-1557309130.png



        // storage/app/images/file.png
        dd($db);
    }
}
