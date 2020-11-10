<?php
global $USER;

$MOODLE = new App\Moodle($USER->token);
if (!$MOODLE->checkToken()) {
    $MOODLE->login($USER->login, $USER->password);
    if (!$MOODLE->checkToken()) {
        App\User::active($USER->id, 0);
        $USER->active = 0;
    } else {
        $USER->token = $MOODLE->token();
        $USER->active = 1;
        App\User::token($USER->id, $USER->token);
    }
} else {
    $USER->active = 1;
}

if ($_GET['redirect'] ?? false) {
    $location = $USER->active ? $_GET['redirect'] : '/login/' . $USER->id;
    header("location: {$location}");
}
