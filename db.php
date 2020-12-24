<?php

// $mysqli = new mysqli('localhost', 'root', '', 'moodle');
// if ($mysqli->connect_error) {
//     die('Ошибка подключения (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
// }

$dsn = 'mysql:dbname=moodle;host=127.0.0.1';
$user = 'moodle';
$password = 'moodleloc';

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
    exit;
}
