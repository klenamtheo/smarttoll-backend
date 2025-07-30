<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Connect to DB
$conn = new mysqli("localhost:3306", "root", "Creditable@1", "flutter_app");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input);

// Check if JSON is valid
if (!$data || !isset($data->ghana_card_id) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Invalid input", "raw_input" => $input, ]);
    exit;
}

$ghana_card_id = $data->ghana_card_id;
$password = $data->password;

// Prepare and run query
$query = $conn->prepare("SELECT password FROM users WHERE ghana_card_id = ?");
$query->bind_param("s", $ghana_card_id);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
    $query->bind_result($hash);
    $query->fetch();

    if (password_verify($password, $hash)) {
        echo json_encode(["success" => true, "message" => "Login successful"]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}

$query->close();
$conn->close();
?>
