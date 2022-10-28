<?php

namespace App\Helpers;

use App\Repositories\CompanyRegPayInvoice;
use App\Repositories\PaymentInvoice;

class XenditInvoice
{
    /**
     * @param $payment_id
     * @param $users_id
     * @param object $data
     * @return false
     */
    public static function saveInvoice($payment_id, $users_id, $data)
    {
        if (empty($data)) return false;
        $id = $data['id'];
        $external_id = $data['external_id'];
        $status = $data['status'];
        $merchant_name = $data['merchant_name'];
        $merchant_profile_picture_url = $data['merchant_profile_picture_url'];
        $amount = $data['amount'];
        $payer_email = $data['payer_email'];
        $description = $data['description'];
        $expiry_date = $data['expiry_date'];
        $invoice_url = $data['invoice_url'];
        $currency = $data['currency'];

        $save = new PaymentInvoice();
        $save->payment_code = $external_id;
        $save->payment_id = $payment_id;
        $save->users_id = $users_id;
        $save->transaction_id = $id;
        $save->status = $status;
        $save->ammount = $amount;
        $save->payer_email = $payer_email;
        $save->description = $description;
        $save->expiry_date = $expiry_date;
        $save->invoice_url = $invoice_url;
        $save->currency = $currency;
        $save->save();

        if ($save->id) {
            return $save->id;
        } else {
            return false;
        }
    }

    public static function saveInvoiceRegSponsor($payment_id, $company_id, $data)
    {
        if (empty($data)) return false;
        $id = $data['id'];
        $external_id = $data['external_id'];
        $status = $data['status'];
        $merchant_name = $data['merchant_name'];
        $merchant_profile_picture_url = $data['merchant_profile_picture_url'];
        $amount = $data['amount'];
        $payer_email = $data['payer_email'];
        $description = $data['description'];
        $expiry_date = $data['expiry_date'];
        $invoice_url = $data['invoice_url'];
        $currency = 'USD';

        $save = new CompanyRegPayInvoice();
        $save->payment_code = $external_id;
        $save->company_reg_pay_id = $payment_id;
        $save->company_id = $company_id;
        $save->transaction_id = $id;
        $save->status = $status;
        $save->ammount = $amount;
        $save->payer_email = $payer_email;
        $save->description = $description;
        $save->expiry_date = $expiry_date;
        $save->invoice_url = $invoice_url;
        $save->currency = $currency;
        $save->save();

        if ($save->id) {
            return $save->id;
        } else {
            return false;
        }
    }
}
