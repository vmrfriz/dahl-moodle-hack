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
if (App\Moodle::isDown()) {
    view('down');
    exit;
}

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

    // case 'test':
    //     global $TEST;
    //     if (!$URI[1]) header("location: " . ($_SERVER['HTTP_REFERER'] ?: '/'));

    //     $users = App\User::all();
    //     foreach ($users as $u) {
    //         $moodle = new App\Moodle($u['token']);
    //         $curr_test = $moodle->get_test_data($URI[1]);
    //         if (!$TEST) $TEST = $curr_test;
    //         else foreach ($TEST)
    //     }
    // break;

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
