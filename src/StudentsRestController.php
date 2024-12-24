<?php

class StudentsRestController
{

    public PDO $pdo;

    function __construct()
    {
        $this->pdo = new PDO("mysql:host=mysql-db;dbname=api;charset=utf8", "root", "rootpassword");
    }

    public function process($id = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $data = [];

        if ($id) {
            if ($method == 'GET') {
                $data = $this->retrieve($id);
            } elseif ($method == 'PUT') {
                $data = $this->update($id);
            } elseif ($method == 'DELETE') {
                $data = $this->remove($id);
            }
        } else {
            if ($method == 'GET') {
                $data = $this->list();
            } elseif ($method == 'POST') {
                $data = $this->create();
            }
        }

        header('Content-type: application/json');
        echo json_encode($data ?? []);
    }

    public function list()
    {
        $query = $this->pdo->query("SELECT * FROM `students` LIMIT 10");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        $json_message = file_get_contents('php://input');
        $data = json_decode($json_message, true, 512, JSON_THROW_ON_ERROR);
        
        $firstname = $data['firstname'] ?? '';
        $lastname = $data['lastname'] ?? '';
        $sex = $data['sex'] ?? '';
        
        $query = $this->pdo->prepare("INSERT INTO students(firstname, lastname, sex) VALUES (:firstname, :lastname, :sex)");
        $query->bindValue("firstname", $firstname);
        $query->bindValue("lastname", $lastname);
        $query->bindValue("sex", $sex);

        $query->execute();
        $id = $this->pdo->lastInsertId();

        return ['message' => 'Student created', "id" => $id];
    }

    public function retrieve($id)
    {
        $query = $this->pdo->prepare("SELECT * FROM `students` WHERE `id` = :id");
        $query->bindValue("id", $id);
        $query->execute();
        $count = $query->fetchColumn();

        if ($count == 0) {
            http_response_code(404);
            return ["status" => false ,"message" => "User not found"];
        }
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id)
    {
        $json_message = file_get_contents('php://input');
        $data = json_decode($json_message, true, 512, JSON_THROW_ON_ERROR);

        $query = $this->pdo->prepare("SELECT * FROM `students` WHERE `id` = :id");
        $query->bindValue("id", $id);
        $query->execute();
        $student = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->pdo->prepare("UPDATE students SET firstname=:firstname, lastname=:lastname, sex=:sex WHERE id = :id");
        $query->bindValue("id", $id);
        $query->bindValue("firstname", $data['firstname'] ?? $student['firstname']);
        $query->bindValue("lastname", $data['lastname'] ?? $student['lastname']);
        $query->bindValue("sex", $data['sex'] ?? $student['sex']);
        $query->execute();

        return ['message' => 'Student updated', 'id' => $id];
    }

    public function remove($id)
    {
        $query = $this->pdo->prepare("DELETE FROM students WHERE id = :id");
        $query->bindValue("id", $id);
        $query->execute();

        return ["delete" => true,'message' => 'Students removed', 'id' => $id];
    }
}
