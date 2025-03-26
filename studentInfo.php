<?php
session_start();

// File for storing student data
const DATA_FILE = 'students.json';

// Student Class
class Student {
    public $id;
    public $name;
    public $age;
    public $class;

    public function __construct($id, $name, $age, $class) {
        $this->id = $id;
        $this->name = htmlspecialchars($name);
        $this->age = filter_var($age, FILTER_VALIDATE_INT);
        $this->class = htmlspecialchars($class);
    }
}

// Student Manager Class
class StudentManager {
    private static function loadData() {
        return file_exists(DATA_FILE) ? json_decode(file_get_contents(DATA_FILE), true) : [];
    }

    private static function saveData($data) {
        file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function getAllStudents() {
        return self::loadData();
    }

    public static function addStudent($name, $age, $class) {
        $students = self::loadData();
        $id = uniqid();
        $student = new Student($id, $name, $age, $class);
        $students[$id] = (array) $student;
        self::saveData($students);
        return ["success" => true];
    }

    public static function deleteStudent($id) {
        $students = self::loadData();
        if (isset($students[$id])) {
            unset($students[$id]);
            self::saveData($students);
            return ["success" => true];
        }
        return ["success" => false];
    }

    public static function updateStudent($id, $name, $age, $class) {
        $students = self::loadData();
        if (isset($students[$id])) {
            $students[$id]['name'] = htmlspecialchars($name);
            $students[$id]['age'] = filter_var($age, FILTER_VALIDATE_INT);
            $students[$id]['class'] = htmlspecialchars($class);
            self::saveData($students);
            return ["success" => true];
        }
        return ["success" => false];
    }
}

// Handle Requests
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $class = $_POST['class'] ?? '';
    if (isset($_POST['edit'])) {
        echo json_encode(StudentManager::updateStudent($_POST['edit'], $name, $age, $class));
    } else {
        echo json_encode(StudentManager::addStudent($name, $age, $class));
    }
    exit();
}

if (isset($_GET['delete'])) {
    echo json_encode(StudentManager::deleteStudent($_GET['delete']));
    exit();
} elseif (isset($_GET['edit'])) {
    echo json_encode(StudentManager::getAllStudents()[$_GET['edit']] ?? null);
    exit();
}

echo json_encode(StudentManager::getAllStudents());
