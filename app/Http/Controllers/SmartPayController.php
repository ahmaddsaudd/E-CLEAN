<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmartPay\NON_SEAMLESS_KIT\Crypto;

class SmartPayController extends Controller
{
    public function showCheckoutForm(Request $request)
    {
        
        $request->validate([
            'order_id' => 'required|string',
            'order_amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);

        $orderId = $request->order_id;
        $amount = $request->order_amount;
        $currency = $request->currency;

        return view('payment-views.SmartPay.checkout', compact('orderId', 'amount', 'currency'));
    }

    public function processPayment(Request $request)
    {
        
        $request->validate([
            'order_id' => 'required|string',
            'order_amount' => 'required|numeric',
            'currency' => 'required|string',
        ]);

        $workingKey = env('SMART_PAY_WORKING_KEY');
        $accessCode = env('SMART_PAY_ACCESS_CODE'); 
        $crypto = new Crypto();

        $merchantData = http_build_query([
            'order_id' => $request->order_id,
            'amount' => $request->order_amount,
            'currency' => $request->currency,
        ]);

        dd($merchantData);

        $encryptedData = $crypto->encrypt($merchantData, $workingKey);

        return view('payment-views.SmartPay.redirect', compact('encryptedData', 'accessCode'));
    }

    public function handleResponse(Request $request)
    {
        $crypto = new Crypto();
        $workingKey = env('SMART_PAY_WORKING_KEY'); 

        $encryptedResponse = $request->input('encResp');
        $decryptedResponse = $crypto->decrypt($encryptedResponse, $workingKey);

        parse_str($decryptedResponse, $dataArr);

        $orderStatus = $dataArr['order_status'] ?? '';

        if ($orderStatus === 'Success') {
            if (
                $dataArr['order_id'] == session('order.order_id') &&
                $dataArr['currency'] == session('order.currency') &&
                round($dataArr['amount'], 2) == round(session('order.amount'), 2)
            ) {
                return view('payments.success', ['order_id' => $dataArr['order_id']]);
            } else {
                return view('payments.failure', ['message' => 'Security Error. Illegal access detected']);
            }
        } else {
            $message = $orderStatus === 'Aborted' ? 'Payment has been aborted' : 'Payment has failed. Please try again.';
            return view('payment-views.SmartPay.failure', ['message' => $message]);
        }
    }
}
