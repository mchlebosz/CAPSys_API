<?php
require "./database.php";

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$db = new Database;
$connection = $db->createConnection();

$data = json_decode(file_get_contents("php://input"));


$query = "INSERT INTO accounts (username, mail, password) VALUES (?, ?, ?)";

$stmt = $connection->prepare($query);
$stmt->bind_param('sss', $data->username, $data->mail, password_hash($data->password, PASSWORD_BCRYPT));

if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(array("message" => "User registered."));

} else {

    http_response_code(400);
    echo json_encode(array("message" => "User NOT registered."));
}
