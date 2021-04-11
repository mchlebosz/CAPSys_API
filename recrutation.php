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
    $query = 'SELECT r.idRecrutation,
    c.idSchool,
    c.startDate AS "CourseStart",
    c.endDate   AS "CourseEnd",
    r.startDate AS "RecrutationStart",
    r.endDate   AS "RecrutationEnd",
    r.vacancies,
    u.idUser,
    u.firstname,
    u.lastname
FROM recrutation r
left JOIN recrutationCandidates rc ON r.idRecrutation = rc.idRecrutation
      LEFT JOIN users u ON rc.idUser = u.idUser
      INNER JOIN courses c ON r.idRecrutation = c.idCourse
group by idUser;';
    $stmt = $connection->prepare($query);
} else if ($course_id != null) {
    $query = 'SELECT r.idRecrutation,
    c.idSchool,
    c.startDate AS "CourseStart",
    c.endDate   AS "CourseEnd",
    r.startDate AS "RecrutationStart",
    r.endDate   AS "RecrutationEnd",
    r.vacancies,
    u.idUser,
    u.firstname,
    u.lastname
FROM recrutation r
      left JOIN recrutationCandidates rc ON r.idRecrutation = rc.idRecrutation
      LEFT JOIN users u ON rc.idUser = u.idUser
      INNER JOIN courses c ON r.idRecrutation = c.idCourse
      where c.idCourse = ?
group by idUser;';
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $course_id);
} else {
    $query = 'SELECT r.idRecrutation,
    c.idSchool,
    c.startDate AS "CourseStart",
    c.endDate   AS "CourseEnd",
    r.startDate AS "RecrutationStart",
    r.endDate   AS "RecrutationEnd",
    r.vacancies,
    u.idUser,
    u.firstname,
    u.lastname
FROM recrutation r
      left JOIN recrutationCandidates rc ON r.idRecrutation = rc.idRecrutation
      LEFT JOIN users u ON rc.idUser = u.idUser
      INNER JOIN courses c ON r.idRecrutation = c.idCourse
      where c.idSchool = ?
group by idUser;';
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $school_id);
}

$stmt->execute();
$result = $stmt->get_result();
$num = $result->num_rows;


if ($num > 1) {
    $output = [];
    $current_recrutation = null;
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        if ($row["idRecrutation"] != $current_recrutation) {
            ($current_recrutation != null ? array_push($output, $temp) : null);
            array_push($rows, $row["idRecrutation"]);
            $current_recrutation = $row["idRecrutation"];
            $temp = ["idRecrutation" => $row["idRecrutation"], "idSchool" => $row["idSchool"], "courseStart" => $row["CourseStart"], "CourseEnd" => $row["CourseEnd"], "recrutationStart" => $row["RecrutationStart"], "recrutationEnd" => $row["RecrutationEnd"], "vacancies" => $row["vacancies"], "requirements" => [], "students" => [["idUser" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]]]];
            if ($temp["students"]["idUser"] == null) {
                $temp["students"] = [];
            }
        } else {
            array_push($temp["students"], ["idUser" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]]);
        }
    }
    array_push($output, $temp);

    foreach ($rows as $recrutationID) {
        $key = array_search($recrutationID, array_column($output, 'idRecrutation'));

        $req = $connection->prepare("SELECT r.idRecrutation, description
        FROM recrutation r
                 left JOIN recrutationRequerements rr ON r.idRecrutation = rr.idRecrutation
        where rr.idRecrutation = ?
        GROUP BY 2");
        $req->bind_param("i", $recrutationID);
        $req->execute();
        $req_result = $req->get_result();

        while ($row = $req_result->fetch_assoc()) {
            array_push($output[$key]["requirements"], $row["description"]);
        }
    }






    http_response_code(200);
    print json_encode($output);
} else if ($num == 1) {
    $output = [];
    $first_row = true;
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        if ($first_row) {
            $first_row = false;
            array_push($rows, $row["idRecrutation"]);
            $output = ["idRecrutation" => $row["idRecrutation"], "idSchool" => $row["idSchool"], "courseStart" => $row["CourseStart"], "CourseEnd" => $row["CourseEnd"], "recrutationStart" => $row["RecrutationStart"], "recrutationEnd" => $row["RecrutationEnd"], "vacancies" => $row["vacancies"], "requirements" => [], "students" => [["idUser" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]]]];
            if ($output["students"]["idUser"] == null) {
                $output["students"] = [];
            }
        } else {
            array_push($output["students"], ["idUser" => $row["idUser"], "firstname" => $row["firstname"], "lastname" => $row["lastname"]]);
        }
    }

    foreach ($rows as $recrutationID) {
        $key = array_search($recrutationID, array_column($output, 'idRecrutation'));

        $req = $connection->prepare("SELECT r.idRecrutation, description
        FROM recrutation r
                 left JOIN recrutationRequerements rr ON r.idRecrutation = rr.idRecrutation
        where rr.idRecrutation = ?
        GROUP BY 2");
        $req->bind_param("i", $recrutationID);
        $req->execute();
        $req_result = $req->get_result();

        while ($row = $req_result->fetch_assoc()) {
            array_push($output[$key]["requirements"], $row["description"]);
        }
    }

    http_response_code(200);
    print json_encode($output);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Course not found."));
}
