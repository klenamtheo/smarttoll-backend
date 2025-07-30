<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$conn = new mysqli("localhost:3306", "root", "Creditable@1", "flutter_app");

$data = json_decode(file_get_contents("php://input"));

$ghana_card_id = $data->ghana_card_id ?? null;
$amount = $data->amount ?? null;
$transaction_ref = $data->transaction_ref ?? null;
$status = $data->status ?? "success"; // Default to success

if (!$ghana_card_id || !$amount || !$transaction_ref) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Store transaction
$stmt = $conn->prepare("INSERT INTO transactions (ghana_card_id, amount, transaction_ref, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sdss", $ghana_card_id, $amount, $transaction_ref, $status);

if ($stmt->execute()) {
    // Update user balance
    $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE ghana_card_id = ?");
    $update->bind_param("ds", $amount, $ghana_card_id);
    $update->execute();

    echo json_encode(["success" => true, "message" => "Transaction stored and balance updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to store transaction"]);
}
?>
