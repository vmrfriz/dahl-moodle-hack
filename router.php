<?php

// namespace App;

// class Router
// {
//     public function __construct() {
//         $_SERVER['REQUEST_URI'];
//     }
// }

$uri = $_SERVER['REQUEST_URI'];
// echo $uri;

if (substr($uri, 0, 5) === '/api/') {
    new App\Api($dbh);
} else {
    view('index');
}