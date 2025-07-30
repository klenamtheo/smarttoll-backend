<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
$conn = new mysqli("mysql.railway.internal:3306", "root", "azahvcXSSjjpUmAUKyInCnkIFBLbIFCB", "railway");

$data = json_decode(file_get_contents("php://input"));
$ghana_card_id = $data->ghana_card_id;

$query = $conn->prepare("SELECT balance FROM users WHERE ghana_card_id=?");
$query->bind_param("s", $ghana_card_id);
$query->execute();
$query->bind_result($balance);
$query->fetch();

if ($balance !== null) {
    echo json_encode(["success" => true, "balance" => $balance]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
