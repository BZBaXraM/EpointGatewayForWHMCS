<?php

require("../../../init.php");

use WHMCS\Database\Capsule;

$privateKey = 'set-key-here'; // Your secret key from ePoint.az

$data = $_POST['data'] ?? '';
$signature = $_POST['signature'] ?? '';

file_put_contents('/tmp/epoint_callback.log', date('Y-m-d H:i:s') . " - RAW POST: " . print_r($_POST, true), FILE_APPEND);

$sgnString = $privateKey . $data . $privateKey;
$calculatedSignature = base64_encode(sha1($sgnString, true));

if ($signature !== $calculatedSignature) {
    file_put_contents('/tmp/epoint_callback.log', "Invalid signature\n", FILE_APPEND);
    header("HTTP/1.1 403 Forbidden");
    exit("Invalid signature");
}

$responseData = json_decode(base64_decode($data), true);
file_put_contents('/tmp/epoint_callback.log', "Decoded data: " . print_r($responseData, true), FILE_APPEND);

$invoiceId = $responseData['order_id'] ?? null;
$status = $responseData['status'] ?? null;
$amount = $responseData['amount'] ?? null;

if (!$invoiceId || !$status) {
    file_put_contents('/tmp/epoint_callback.log', "Missing order_id or status\n", FILE_APPEND);
    exit("Invalid data received");
}

if ($status === 'success') {
    $invoiceId = intval($invoiceId);

    try {
        $invoice = Capsule::table('tblinvoices')->where('id', $invoiceId)->first();

        if ($invoice) {
            addInvoicePayment(
                $invoiceId,
                $responseData['transaction'] ?? '',
                $amount,
                0,
                'epoint'
            );

            file_put_contents('/tmp/epoint_callback.log', "Invoice #$invoiceId marked as paid.\n", FILE_APPEND);
            echo "OK";
        } else {
            file_put_contents('/tmp/epoint_callback.log', "Invoice #$invoiceId not found.\n", FILE_APPEND);
            exit("Invoice not found");
        }
    } catch (Exception $e) {
        file_put_contents('/tmp/epoint_callback.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        exit("Error processing payment");
    }
} else {
    file_put_contents('/tmp/epoint_callback.log', "Payment failed for invoice #$invoiceId. Status: $status\n", FILE_APPEND);
    exit("Payment failed");
}
