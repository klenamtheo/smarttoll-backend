<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$data = json_decode(file_get_contents("php://input"));
$ghana_card_id = $data->ghana_card_id;
$amount = $data->amount * 100; // Convert GHS to pesewas

$fields = [
    'email' => $ghana_card_id . "@ghanatoll.com",
    'amount' => $amount,
    'metadata' => [
        'ghana_card_id' => $ghana_card_id,
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer sk_test_1cd69efe8d10629f5ae633c8462ab43c696ae1a8",  // Replace with your real key
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
?>
