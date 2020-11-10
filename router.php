<?php

// namespace App;

// class Router
// {
//     public function __construct() {
//         $_SERVER['REQUEST_URI'];
//     }
// }

$uri = $_SERVER['REQUEST_URI'];
$URI = array_values(array_filter(explode('/', $uri)));

switch ($URI[0] ?? false) {
    case 'api':
        new App\Api($dbh);
        exit;
    break;

    case 'login':
        $USER = App\User::id($URI[1]);
        include('login.php');
        view('login');
    break;

    case 'user':
        $USER = App\User::id($URI[1]);
        $MOODLE = new App\Moodle($USER->token);
        if ($MOODLE->checkToken() === false) header('location: /login/' . $USER->id . '/?redirect=' . $uri);
        user_methods(array_slice($URI, 2));
    break;

    default:
        if ($uri !== '/')
            header('location: /');
        $USERS = App\User::all();
        view('index');
    break;
}

function user_methods($URI) {
    global $MOODLE, $USER;

    switch ($URI[0]) {
        case 'courses':
            global $COURSES;
            $COURSES = $MOODLE->get_courses();
            view('courses');
        break;

        case 'themes':
            global $THEMES;
            if (!$URI[1]) header("location: /user/{$USER->id}/courses/");
            $THEMES = $MOODLE->get_course_themes($URI[1]);
            view('themes');
        break;

        case 'tests':
            if (!$URI[1]) header("location: /user/{$USER->id}/courses/");
            $link = $MOODLE->get_theme_test_link($URI[1]);
            preg_match('/review\.php\?attempt=(\d+)/', $link, $id_match);
            header("location: /user/{$USER->id}/test/{$id_match[1]}");
        break;

        case 'test':
            global $TEST;
            if (!$URI[1]) header("location: /user/{$USER->id}/courses/");
            $TEST = $MOODLE->get_test_data($URI[1]);
            view('test');
        break;

        default:
            header('location: /');
        break;
    }
}
