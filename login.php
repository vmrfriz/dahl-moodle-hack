<?php
global $USER;

$MOODLE = new App\Moodle($USER->token);
if (!$MOODLE->checkToken()) {
    $MOODLE->login($USER->login, $USER->password);
    if (!$MOODLE->checkToken()) {
        App\User::active($USER->id, 0);
        $USER->active = 0;
    } else {
        $token = $MOODLE->token();
        App\User::token($USER->id, $token);
        $USER->token = $token;
        $USER->active = 1;
    }
} else {
    $USER->active = 1;
}

if ($_GET['redirect'] ?? false) {
    header('location: ' . $_GET['redirect']);
}