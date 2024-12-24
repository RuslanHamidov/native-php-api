<?php

require_once 'StudentsRestController.php';

$url = $_SERVER['REQUEST_URI'];

$matches = [];
if(preg_match("#/api/students/?(\d+)?#", $url, $matches)) {
    $id = $matches[1] ?? null;
    $controller = new StudentsRestController();
    $controller->process($id);
}

