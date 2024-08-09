<?php
// callback.php
require_once 'config.php';
require_once 'database.php';

$callbackData = json_decode(file_get_contents('php://input'), true);

if ($callbackData && isset($callbackData['response'])) {
    $response = $callbackData['response'];
    
    $db = new Database();
    $sql = "UPDATE payments SET 
            status = '" . $db->escapeString($response['Status']) . "',
            mpesa_receipt_number = '" . $db->escapeString($response['MpesaReceiptNumber']) . "',
            result_code = '" . $db->escapeString($response['ResultCode']) . "',
            result_desc = '" . $db->escapeString($response['ResultDesc']) . "'
            WHERE checkout_request_id = '" . $db->escapeString($response['CheckoutRequestID']) . "'";
    
    if ($db->query($sql)) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payment']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid callback data']);
}