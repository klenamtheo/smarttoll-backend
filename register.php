<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

$conn = new mysqli("mysql.railway.internal:3306", "root", "azahvcXSSjjpUmAUKyInCnkIFBLbIFCB", "railway");

$data = json_decode(file_get_contents("php://input"));
$ghana_card_id = $data->ghana_card_id;
$password = password_hash($data->password, PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT * FROM users WHERE ghana_card_id=?");
$check->bind_param("s", $ghana_card_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Account already exists"]);
} else {
    $query = $conn->prepare("INSERT INTO users (ghana_card_id, password) VALUES (?, ?)");
    $query->bind_param("ss", $ghana_card_id, $password);
    $query->execute();

    echo json_encode(["success" => true, "message" => "Account created"]);
}
?>
