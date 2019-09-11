<?php

include('config.php');

$amount = 100; // fixed amount 100

if (isset($_POST['nonce'])) {

    $result = Braintree_Transaction::sale([
        'amount' => $amount,
        'paymentMethodNonce' => $_POST['nonce'],
        'merchantAccountId' => $btSettings['merchant_account_id'], // from config
        'customer' => ['email' => 'customer@example.com',
            'company' => 'Company name'
        ],
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
        echo "Fail: " . $result->message;
    }

}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Braintree card payment implementation 3D secure 2.0 Javascript v3 SDK</title>
    <meta name="description" content="Braintree card payment implementation 3D secure 2.0 Javascript v3 SDK">
    <meta name="author" content="Jozef Vacval">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
<h1>3D Secure 2.0 implementation</h1>

<!-- form for submit -->
<form action="" method="post" id="frm">
    <input type="hidden" id="nonce" name="nonce" value="">
</form>

<form action="javascript:void(0)" class="container">
    <div class="row">
        <div class="col-xs-12">
            <table class="table">
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Number (successful with no challenge)</td>
                    <td>4000000000001000</td>
                </tr>
                <tr>
                    <td>Number (successful with challenge)</td>
                    <td>4000000000001091</td>
                </tr>
                <tr>
                    <td>Number (unsuccessful with challenge)</td>
                    <td>4000000000001109</td>
                </tr>
                <tr>
                    <td>Expiration Date (for sandbox testing, year must be exactly 3 years in the future)</td>
                    <td>12/22</td>
                </tr>
                <tr>
                    <td>CVV</td>
                    <td>123</td>
                </tr>
            </table>
        </div>
    </div>


    <div id="hosted-fields">
        Card number:
        <div id="hf-number" class="form-control"></div>
        Expiration date:
        <div id="hf-date" class="form-control"></div>
        CVV:
        <div id="hf-cvv" class="form-control"></div>
        <input disabled="disabled" id="pay-btn" class="btn btn-success" type="submit" value="Loading...">
    </div>


</form>


<script src="assets/jquery.js"></script>
<script src="https://js.braintreegateway.com/web/3.52.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.52.0/js/hosted-fields.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.52.0/js/three-d-secure.min.js"></script>


<script>
    var hf, threeDS;

    function start() {
        getClientToken();
    }

    function getClientToken() {
        $.getJSON("generateToken.php", function (data) {
            onFetchClientToken(data.client_token);
        });
    }

    function setupComponents(clientToken) {
        return Promise.all([
            braintree.hostedFields.create({
                authorization: clientToken,
                styles: {
                    input: {
                        'font-size': '14px',
                        'font-family': 'monospace'
                    }
                },
                fields: {
                    number: {
                        selector: '#hf-number',
                        placeholder: '4111 1111 1111 1111'
                    },
                    cvv: {
                        selector: '#hf-cvv',
                        placeholder: '123'
                    },
                    expirationDate: {
                        selector: '#hf-date',
                        placeholder: '12 / 2020'
                    }
                }
            }),
            braintree.threeDSecure.create({
                authorization: clientToken,
                version: 2
            })
        ]);
    }

    function onFetchClientToken(clientToken) {
        return setupComponents(clientToken).then(function (instances) {
            hf = instances[0];
            threeDS = instances[1];

            setupForm();
        }).catch(function (err) {
            alert(err.message)
        });
    }

    function setupForm() {
        enablePayNow();
    }

    function enablePayNow() {
        $('#pay-btn').val('Pay Now');
        $('#pay-btn').prop("disabled", false);
    }

    $(document).on('click', '#pay-btn', function (event) {
        $(this).prop("disabled", true);
        $(this).val('Processing...');


        hf.tokenize().then(function (payload) {
            return threeDS.verifyCard({
                onLookupComplete: function (data, next) {
                    next();
                },
                amount: <?= $amount ?>,
                nonce: payload.nonce,
                bin: payload.details.bin,
            })
        }).then(function (payload) {
            if (!payload.liabilityShifted) {
                console.log('Liability did not shift', payload);
                $('#frm').submit();
                return;
            }

            console.log('verification success:', payload);
            $('#nonce').val(payload.nonce);
            $('#frm').submit();

        }).catch(function (err) {
            alert(err.message)
            enablePayNow();
        });
    });

    start();

</script>
</body>
</html>
