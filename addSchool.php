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

$name = $data->username;
$address = $data->address;
$city = $data->city;
$country = $data->country;
$principalID = $data->principalID;
$photo = $data->photo;
$description = $data->description;
$typeID = $data->typeID;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$query = "INSERT INTO schools (prinicpalUserId, name, idType, address, city, country, photo, description)
VALUES (?, ?, ?, ?, ?, ?, ?, ?);";

$stmt = $connection->prepare($query);
$stmt->bind_param("isisssss", $principalID, $name, $typeID, $address, $city, $county, $photo, $description);

if ($principalID = "" || $name = "" || $typeID = "" || $city = "" || $county = "") {
    http_response_code(400);
    echo json_encode(array("message" => "Incorrect body content. Missing reuqired values"));
    exit();
}

// TODO: nie mam pojęcia czeny fejluje xDDD
if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(array("message" => "School added.", "id" => $stmp->insert_id));
} else {

    http_response_code(500);
    echo json_encode(array("message" => "Adding school failed."));
}
