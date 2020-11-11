<?php

function view($name) {
    include('view/header.php');
    include("view/{$name}.php");
    include('view/footer.php');
}

function helper_get_moodle_user($user) {
    $user = (array) $user;
    if (!$user['active']) return false;

    $moodle = new App\Moodle($user['token']);
    if (!$moodle->checkToken()) {
        $moodle->login($user['login'], $user['password']);
        if ($moodle->checkToken()) {
            $user['token'] = $moodle->token();
            App\User::token($user['id'], $user['token']);
        } else {
            $user['active'] = false;
            App\User::active($user['id'], false);
            return false;
        }
    }

    return [
        'user' => $user,
        'moodle' => $moodle,
    ];
}
