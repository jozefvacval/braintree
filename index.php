<?php

include('config.php');

if (isset($_POST['cvv'])) {


    $result = Braintree_Transaction::sale([
        'amount' => $_POST['amount'],
        'paymentMethodNonce' => $_POST['nonce'],
        'merchantAccountId' => $btSettings['merchant_account_id'],
        'creditCard' => [
            'number' => $_POST['number'],
            'expirationDate' => $_POST['expiration_date'],
            'cvv' => $_POST['cvv']
        ],
        'customer' => ['email' => 'customer@example.com',
            'company' => 'Company name'
        ],
        'deviceData' => $_POST['device_data'],
        /**
         *  if needed:
         */
        'options' => ['skipAdvancedFraudChecking' => true],

    ]);

    if ($result->success) {
        echo "Success";
        /**
         * submit for settlement after success:
         */
        Braintree_Transaction::submitForSettlement($result->transaction->id);
    } else {
        echo "Fail";
    }
}


?>
<html>
<head>
    <meta charset="utf-8">
    <title>Braintree card payment implementation</title>
    <meta name="description" content="Braintree card payment implementation">
    <meta name="author" content="Jozef Vacval">
</head>

<body>
<form autocomplete="off" id="checkout" method="post">
    Amount: <input autocomplete="off" data-braintree-name="amount" id="amount" name="amount" value="1"> <br/>
    Card: <input autocomplete="off" data-braintree-name="number" id="number" name="number" value="4000000000000010"> <br/>
    Expiration: <input autocomplete="off" data-braintree-name="expiration_date" id="expiration_date" name="expiration_date" value="01/23"> <br/>
    CVV: <input autocomplete="off" data-braintree-name="cvv" id="cvv" name="cvv" value="123"> <br/>
    <input type="button" name="pay" id="pay" value="Pay">
    <input type="hidden" name="nonce" id="nonce" value="">
    <input type="hidden" name="device_data" id="device_data" value="">
</form>
<input type="hidden" name="token" id="token" value="<?= Braintree_ClientToken::generate(); ?>">

<script src="assets/jquery.js"></script>
<script src="https://js.braintreegateway.com/js/braintree-2.32.1.min.js"></script>
<script>

    $(document).ready()
    {
        $(document).on('click', '#pay', function () {
            var client = new braintree.api.Client({
                clientToken: $('#token').val(),
            });

            braintree.setup($('#token').val(), 'custom', {
                dataCollector: {
                    kount: {environment: 'production'}
                },
                onReady: function (braintreeInstance) {
                    var form = document.getElementById('checkout');
                    var deviceDataInput = form['device_data'];
                    deviceDataInput.value = braintreeInstance.deviceData;
                }
            });

            client.verify3DS({
                amount: $('#amount').val(),
                creditCard: {
                    number: $('#number').val(),
                    expirationDate: $('#expiration_date').val(),
                    cvv: $('#cvv').val()
                }
            }, function (err, response) {
                if (!err) {
                    $('#nonce').val(response.nonce);
                    $('#checkout').submit();
                } else {
                    // Alert message:
                    alert(err.message);
                }
            });
        });
    }

</script>
</body>
</html>
