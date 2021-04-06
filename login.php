<?php
require "./database.php";

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$db = new Database;
$connection = $db->createConnection();

$data = json_decode(file_get_contents("php://input"));

$username = $data->username;
$password = $data->password;


$query = "SELECT idAccount, password FROM accounts WHERE username = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$num = $stmt->num_rows;


if ($num > 0) {
    $stmt->bind_result($id, $password2);
    $stmt->fetch();

    if (password_verify($password, $password2)) {

        http_response_code(200);
        echo json_encode(array("message" => "Login successful."));

    } else {

        http_response_code(401);
        echo json_encode(array("message" => "Login failed."));
    }
} else {
    http_response_code(404);
    echo json_encode(array("message" => "User not found."));
}