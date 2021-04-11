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

$user_id = $data->user_id;
$name = $data->name;
$surname = $data->surname;
$birthdate = $data->birthdate;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$query = "UPDATE users
SET firstname = ?,
    lastname  = ?,
    birthdate = ?
WHERE idUser = ?;";

$stmt = $connection->prepare($query);
$stmt->bind_param("sssi", $name, $surname, $birthdate, $user_id);


if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(array("message" => "User data updated."));
} else {

    http_response_code(500);
    echo json_encode(array("message" => "Updating user data failed failed."));
}
