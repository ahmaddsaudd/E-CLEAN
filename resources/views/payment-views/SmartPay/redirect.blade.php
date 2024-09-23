<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Payment Gateway</title>
</head>
<body>
<center>
    <form method="post" name="redirect" action="https://mti.bankmuscat.com:6443/transaction.do?command=initiateTransaction"> 
        <input type="hidden" name="encRequest" value="{{ $encryptedData }}">
        <input type="hidden" name="access_code" value="{{ $accessCode }}">
    </form>
    <script language="javascript">document.redirect.submit();</script>
</center>
</body>
</html>
