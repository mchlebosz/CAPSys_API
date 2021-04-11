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

$username = $_GET["username"];

if ($username == null) {
    $query = "SELECT a.idAccount,
    a.username,
    a.mail,
    a.created_at as 'created',
    a.role,
    u.firstname,
    u.lastname,
    u.birthdate,
    u.id as 'user_id'
FROM accounts a
INNER JOIN users u ON a.idAccount = u.idAccount";
    $stmt = $connection->prepare($query);
} else {
    $query = "SELECT a.idAccount,
    a.username,
    a.mail,
    a.created_at as 'created',
    a.role,
    u.firstname,
    u.lastname,
    u.birthdate,
    u.idUser as 'user_id'
FROM accounts a
    INNER JOIN users u ON a.idAccount = u.idAccount
WHERE a.username = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;


if ($num > 1) {
    $output = [];
    while ($row = $result->fetch_assoc()) {
        $temp = ["id" => $row["idAccount"], "username" => $row["username"], "created" => $row["created"], "role" => $row["role"], "firstname" => $row["firstname"], "lastname" => $row["lastname"], "birthdate" => $row["birthdate"], "user_id" =>$row["user_id"]];

        array_push($output, $temp);
    }

    http_response_code(200);
    print json_encode($output);
} else if ($num == 1) {
    $row = $result->fetch_assoc();
    $output = ["id" => $row["idAccount"], "username" => $row["username"], "created" => $row["created"], "role" => $row["role"], "firstname" => $row["firstname"], "lastname" => $row["lastname"], "birthdate" => $row["birthdate"], "user_id" =>$row["user_id"]];

    http_response_code(200);
    print json_encode($output);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Account not found."));
}
