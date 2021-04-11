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

$school_id = $_GET["school_id"];
$course_id = $_GET["course_id"];

if ($school_id == null && $course_id == null) {
    $query = "SELECT idCourse, c.idSchool , name as 'schoolName', programme as 'name', programmeDescription, startDate, endDate
    FROM courses c
             INNER JOIN schools s ON c.idSchool = s.idSchool";
    $stmt = $connection->prepare($query);
} else if ($course_id != null) {
    $query = "SELECT idCourse, c.idSchool, name as 'schoolName', programme as 'name', programmeDescription, startDate, endDate
    FROM courses c
             INNER JOIN schools s ON c.idSchool = s.idSchool
    where idCourse = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $course_id);
} else {
    $query = "SELECT idCourse, c.idSchool, name as 'schoolName', programme as 'name', programmeDescription, startDate, endDate
    FROM courses c
             INNER JOIN schools s ON c.idSchool = s.idSchool
    where c.idSchool = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $school_id);
}

$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;


if ($num > 1) {
    $output = [];
    while ($row = $result->fetch_assoc()) {
        array_push($output, $row);
    }

    http_response_code(200);
    print json_encode($output);
} else if ($num == 1) {
    $row = $result->fetch_assoc();


    http_response_code(200);
    print json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Course not found."));
}
