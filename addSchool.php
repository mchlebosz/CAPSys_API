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

if ($school_id != null) {
    $query = "UPDATE schools
    SET prinicpalUserId = ?,
        name            = ?,
        idType          = ?,
        address         = ?,
        city            = ?,
        country         = ?,
        photo           = ?,
        description     = ?
    WHERE idSchool = ?;";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("isisssssi", $data->principal_id, $data->name, $data->type_id, $data->address, $data->city, $data->country, $data->photo, $data->description, $data->school_id);

    if ($stmt->execute()) {

        http_response_code(200);
        echo json_encode(array("message" => "School updated."));
    } else {

        http_response_code(500);
        echo json_encode(array("message" => "Updating school failed."));
    }
} else {
    print($principalID);
    $query = "INSERT INTO schools (prinicpalUserId, name, idType, address, city, country, photo, description)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?);";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("isisssss", $data->principal_id, $data->name, $data->type_id, $data->address, $data->city, $data->country, $data->photo, $data->description);

    if ($principalID = "" || $name = "" || $typeID = "" || $city = "" || $county = "") {
        http_response_code(400);
        echo json_encode(array("message" => "Incorrect body content. Missing reuqired values"));
        exit();
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "School added.", "id" => $stmt->insert_id));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Adding school failed."));
    }
}
