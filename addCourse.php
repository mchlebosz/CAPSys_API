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

$course_id = $data->course_id;
$school_id = $data->school_id;
$programme = $data->programme;
$description = $data->description;
$start_date = $data->start_date;
$end_date = $data->end_date;

if ($course_id != null) {
    $query = "UPDATE courses
    SET idSchool             = ?,
        programme            = ?,
        programmeDescription = ?,
        startDate            = ?,
        endDate              = ?
    WHERE idCourse = ?;";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("issssi", $school_id, $programme, $description, $start_date, $end_date, $course_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Course modified."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Modyfying course failed."));
    }
} else {
    $query = "INSERT INTO courses (idSchool, programme, programmeDescription, startDate, endDate)
    VALUES (?, ?, ?, ?, ?);";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("issss", $school_id, $programme, $description, $start_date, $end_date);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Course added.", "id" => $stmt->insert_id));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Adding course failed."));
    }
}
