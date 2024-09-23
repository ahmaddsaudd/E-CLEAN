<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmartPay\NON_SEAMLESS_KIT\Crypto;

class SmartPayController extends Controller
{
    public function showCheckoutForm()
    {
        // Sample data - you might retrieve this from a database or other sources
        $orderId = 'ORDER12345';
        $amount = '150.00';
        $currency = 'USD';

        return view('payment-views.SmartPay.checkout', compact('orderId', 'amount', 'currency'));
    }

    public function processPayment(Request $request)
    {
        $workingKey = env('SMART_PAY_WORKING_KEY'); // Retrieve your working key from .env
        $accessCode = env('SMART_PAY_ACCESS_CODE'); // Retrieve your access code from .env
        $crypto = new Crypto();

        // Build merchant data
        $merchantData = http_build_query($request->except('_token'));
        // Encrypt the data
        $encryptedData = $crypto->encrypt($merchantData, $workingKey);

        return view('payment-views.SmartPay.redirect', compact('encryptedData', 'accessCode'));
    }

    public function handleResponse(Request $request)
    {
        $crypto = new Crypto();
        $workingKey = env('SMART_PAY_WORKING_KEY'); // Retrieve your working key from .env

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
