<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Connect to database
$conn = new mysqli("localhost:3306", "root", "Creditable@1", "flutter_app");

// Get JSON body
$data = json_decode(file_get_contents("php://input"));
$ghana_card_id = $data->ghana_card_id ?? null;

if (!$ghana_card_id) {
    echo json_encode(["success" => false, "message" => "Ghana Card ID is required"]);
    exit;
}

// Fetch transactions
$stmt = $conn->prepare("SELECT amount, transaction_ref, status, created_at FROM transactions WHERE ghana_card_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $ghana_card_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode([
    "success" => true,
    "transactions" => $transactions
]);
?>
