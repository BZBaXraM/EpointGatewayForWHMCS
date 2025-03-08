<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function epoint_MetaData()
{
    return [
        'DisplayName' => 'Epoint Payment Gateway',
        'APIVersion' => '1.0',
    ];
}

function epoint_config()
{
    return [
        "FriendlyName" => [
            "Type" => "System",
            "Value" => "Epoint.az",
        ],
        "privateKey" => [
            "FriendlyName" => "Private Key",
            "Type" => "text",
            "Size" => "40",
            "Description" => "Enter your private key from Epoint.az",
        ],
        "publicKey" => [
            "FriendlyName" => "Public Key",
            "Type" => "text",
            "Size" => "40",
            "Description" => "Enter your public key from Epoint.az",
        ],
        "successUrl" => [
            "FriendlyName" => "Success URL",
            "Type" => "text",
            "Size" => "100",
            "Description" => "URL where users are redirected after successful payment",
        ],
        "errorUrl" => [
            "FriendlyName" => "Error URL",
            "Type" => "text",
            "Size" => "100",
            "Description" => "URL where users are redirected after failed payment",
        ],
        "language" => [
            "FriendlyName" => "Language",
            "Type" => "dropdown",
            "Options" => "az,en,ru",
            "Description" => "Select the language for the Epoint payment page",
            "Default" => "en",
        ],

    ];
}
function epoint_link($params)
{
    $privateKey = $params['privateKey'];
    $publicKey = $params['publicKey'];
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currency = $params['currency'];
    $successUrl = $params['successUrl'];
    $errorUrl = $params['errorUrl'];
    $language = !empty($params['language']) ? $params['language'] : 'en'; // Default to English. Change to 'az' or 'ru' if needed.

    $jsonData = [
        'public_key' => $publicKey,
        'amount' => $amount,
        'currency' => $currency,
        'order_id' => $invoiceId,
        'description' => "Payment for Invoice #{$invoiceId}",
        'success_redirect_url' => $successUrl,
        'error_redirect_url' => $errorUrl,
        'language' => $language,
    ];

    $data = base64_encode(json_encode($jsonData));
    $signature = base64_encode(sha1("{$privateKey}{$data}{$privateKey}", true));

    $postData = [
        'data' => $data,
        'signature' => $signature
    ];

    $ch = curl_init('https://epoint.az/api/1/request');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Execute cURL request
    $response = curl_exec($ch);

    // Capture cURL errors
    if ($response === false) {
        $curlError = curl_error($ch);
        curl_close($ch);
        return "cURL Error: " . $curlError;
    }

    // Get HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (isset($result['redirect_url'])) {
            header("Location: " . $result['redirect_url']);
            exit;
        } else {
            return "API response error: Missing redirect_url";
        }
    }

    return "Payment initialization failed. HTTP Code: $httpCode, Response: $response";
}
