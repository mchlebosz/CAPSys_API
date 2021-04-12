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

if ($school_id == null) {
    $query = "SELECT s.idSchool,
    s.name,
    st.name as 'type_name',
    st.idType,
    u.idUser,
    u.firstname,
    u.lastname,
    s.address,
    s.city,
    s.country,
    s.photo,
    s.description
FROM schools s
      INNER JOIN users u ON s.prinicpalUserId = u.idUser
      INNER JOIN schoolsType st ON s.idType = st.idType";
    $stmt = $connection->prepare($query);
} else {
    $query = "SELECT s.idSchool,
    s.name,
    st.name as 'type_name',
    st.idType,
    u.idUser,
    u.firstname,
    u.lastname,
    s.address,
    s.city,
    s.country,
    s.photo,
    s.description
FROM schools s
      INNER JOIN users u ON s.prinicpalUserId = u.idUser
      INNER JOIN schoolsType st ON s.idType = st.idType
WHERE s.idSchool = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $school_id);
}

$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;


if ($num > 1) {
    $output = [];
    while ($row = $result->fetch_assoc()) {
        $temp = ["id" => $row["idSchool"], "name" => $row["name"], "type" => $row["type_name"], "type_id" => $row["idType"], "principal" => ["id" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]], "address" => ["street" => $row["address"], "city" => $row["city"], "country" => $row["country"]], "description" => $row["description"], "photo" => $row["photo"]];

        array_push($output, $temp);
    }

    http_response_code(200);
    print json_encode($output);
} else if ($num == 1) {
    $row = $result->fetch_assoc();
    $output = ["id" => $row["idSchool"], "name" => $row["name"], "type" => $row["type_name"], "type_id" => $row["idType"],"principal" => ["id" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]], "address" => ["street" => $row["address"], "city" => $row["city"], "country" => $row["country"]], "description" => $row["description"], "photo" => $row["photo"]];

    http_response_code(200);
    print json_encode($output);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "School not found."));
}
