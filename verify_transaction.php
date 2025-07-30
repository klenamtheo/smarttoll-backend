<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$data = json_decode(file_get_contents("php://input"), true);
$reference = $data['reference'];
$ghanaCardId = $data['ghana_card_id'];
$amount = $data['amount'];

if (!$reference || !$ghanaCardId || !$amount) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit;
}

// 1. Verify with Paystack
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer sk_test_1cd69efe8d10629f5ae633c8462ab43c696ae1a8",
        "Cache-Control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(["success" => false, "message" => "cURL Error: $err"]);
    exit;
}

$paystackData = json_decode($response, true);

// 2. Check payment status
if ($paystackData['data']['status'] === 'success') {

    // 3. Connect to DB
    $conn = new mysqli("localhost:3306", "root", "Creditable@1", "flutter_app");
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "DB connection failed"]);
        exit;
    }

    // 4. Insert into transactions table
    $stmt = $conn->prepare("INSERT INTO transactions (ghana_card_id, amount, transaction_ref, status) VALUES (?, ?, ?, 'success')");
    $stmt->bind_param("sds", $ghanaCardId, $amount, $reference);
    
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to store transaction: " . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // 5. Update user balance securely
    $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE ghana_card_id = ?");
    $update->bind_param("ds", $amount, $ghanaCardId);

    if (!$update->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to update balance: " . $update->error]);
        $update->close();
        $conn->close();
        exit;
    }

    $update->close();
    $conn->close();

    echo json_encode(["success" => true, "message" => "Transaction verified and stored successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Payment not successful"]);
}
?>
