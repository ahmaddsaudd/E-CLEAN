<!DOCTYPE html>
<html>
<head>
    <title>SmartPay Checkout</title>
</head>
<body>
<center>
    <form method="POST" action="{{ route('smartpay.processPayment') }}">
        @csrf
        <input type="hidden" name="order_id" value="{{ $orderId }}">
        <input type="hidden" name="amount" value="{{ $amount }}">
        <input type="hidden" name="currency" value="{{ $currency }}">
        <button type="submit">Proceed to Payment</button>
    </form>
</center>
</body>
</html>
