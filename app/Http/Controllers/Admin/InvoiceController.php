<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingContact\BookingContact;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{


    public function index()
    {
        $invoice = BookingContact::orderby('id', 'desc')->get();
        $data = [
            'invoice' => $invoice
        ];
        return view('admin.invoice.invoice', $data);
    }

    public function detail()
    {
        return view('admin.invoice.invoice-detail');
    }
}
