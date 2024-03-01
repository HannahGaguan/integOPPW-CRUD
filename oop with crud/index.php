//OOP principles in PHP version

<?php

class StudRecords {
    private $con;

    public function __construct($host, $username, $password, $database) {
        $this->con = new mysqli($host, $username, $password, $database);
    }

    public function executeQuery($query, $params = []) {
        $stmt = $this->con->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    public function addStudent($data) {
        $query = "INSERT INTO tblstudents (schoolid, first_name, middle_initial, last_name, gender, date_of_birth, course) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->executeQuery($query, array_values($data));
        echo $this->con->affected_rows ? "Recorded" : "Error!";
    }

    public function updateStudent($data) {
        $query = "UPDATE tblstudents SET first_name = ?, middle_initial = ?, last_name = ?, gender = ?, date_of_birth = ?, course = ? WHERE schoolid = ?";
        $params = [
            $data['first_name'],
            $data['middle_initial'],
            $data['last_name'],
            $data['gender'],
            $data['date_of_birth'],
            $data['course'],
            $data['schoolid']
        ];
        $this->executeQuery($query, $params);
        echo $this->con->affected_rows ? "Update Successful" : "Error!";
    }
    
    public function deleteStudent($schoolid) {
        $query = "DELETE FROM tblstudents WHERE schoolid = ?";
        $this->executeQuery($query, [$schoolid]);
        echo $this->con->affected_rows ? "Deleted Successfully" : "Error!";
    }

    public function searchStudent($schoolid) {
        $query = "SELECT * FROM tblstudents WHERE schoolid = ?";
        $stmt = $this->executeQuery($query, [$schoolid]);
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "SchoolID: " . $row["schoolid"] . " - Name: " . $row["first_name"] . " " . $row["last_name"] . "\n";
            }
        } else {
            echo "0 results";
        }
    }

    public function listStudents() {
        $query = "SELECT * FROM tblstudents";
        $result = $this->con->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "SchoolID: " . $row["schoolid"] . " - Name: " . $row["first_name"] . " " . $row["last_name"] . "\n";
            }
        } else {
            echo "0 results";
        }
    }
}

$host = "localhost";
$username = "root";
$password = "";
$database = "activity1";

$studRecords = new StudRecords($host, $username, $password, $database);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "POST":
        $studRecords->addStudent($_POST);
        break;

    case "PUT":
        parse_str(file_get_contents("php://input"), $_PUT);
        $studRecords->updateStudent($_PUT);
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $_DELETE);
        if (isset($_DELETE["schoolid"])) {
            $studRecords->deleteStudent($_DELETE["schoolid"]);
        } else {
            echo "No SchoolID given!";
        }
        break;

    default:
        $data = $_GET;
        if (isset($data['search'])) {
            $studRecords->searchStudent($data['search']);
        } else {
            $studRecords->listStudents();
        }
        break;
}

?>