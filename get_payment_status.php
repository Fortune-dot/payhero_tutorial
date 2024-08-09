
<?php
require_once 'config.php';
require_once 'database.php';

$external_reference = $_GET['external_reference'];

$db = new Database();
$sql = "SELECT * FROM payments WHERE external_reference = '" . $db->escapeString($external_reference) . "'";
$result = $db->query($sql);

if ($result && $result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    echo json_encode(['success' => true, 'payment' => $payment]);
} else {
    echo json_encode(['success' => false, 'message' => 'Payment not found']);
}
?>
