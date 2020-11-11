<?php

// namespace App;
use App\User;
use App\Moodle;
use App\Cache;
use App\Api;

$uri = $_SERVER['REQUEST_URI'];

$URI = array_values(array_filter(explode('/', $uri)));
if (Moodle::isDown() && !Cache::isCached($uri)) {
    view('down');
    exit;
}

switch ($URI[0] ?? false) {
    case 't':
        echo '<pre>';
        var_export($_SERVER);
        echo '</pre>';
    break;

    case 'api':
        new Api($dbh);
        exit;
    break;

    case 'login':
        $USER = User::id($URI[1]);
        include('login.php');
        Cache::clear('/');
        view('login');
    break;

    case 'user':
        $cache = Cache::start(86400);
        $USER = User::id($URI[1]);
        $MOODLE = new Moodle($USER->token);
        if ($MOODLE->checkToken() === false) header('location: /login/' . $USER->id . '/?redirect=' . $uri);
        user_methods(array_slice($URI, 2));
        $cache->save();
    break;

    case 'test':
        global $TEST;
        $cache = Cache::start();
        if (!$URI[1]) header("location: " . ($_SERVER['HTTP_REFERER'] ?: '/'));
        $TEST = get_correct_answers();
        view('test');
        $cache->save();
    break;

    case 'completed-tests':
        $cache = Cache::start();
        $users = User::all();
        $users_complete_test = [];
        foreach ($users as $u) {
            $data = helper_get_moodle_user($u);
            if (!$data) continue;
            list('user' => $u, 'moodle' => $moodle) = $data;

            $test_link = $moodle->get_theme_test_link($URI[1]);
            $user_id = $u['id'];
            if ($test_link) $users_complete_test[$user_id] = $u['name'];
        }
        view('completed-tests');
        $cache->save();
    break;

    case 'clearcache':
        if (!$_GET['page']) header('Location: ' . $_SERVER['HTTP_REFERER']);
        Cache::clear($_GET['page']);
        header('Location: ' . $_GET['page']);
    break;

    default:
        if ($uri !== '/')
            header('location: /');
        $cache = Cache::start(180);
        $USERS = User::all();
        view('index');
        $cache->save();
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
            global $THEMES, $cache;
            $cache->expires(3600);
            if (!$URI[1]) header("location: /user/{$USER->id}/courses/");
            $THEMES = $MOODLE->get_course_themes($URI[1]);
            view('themes');
        break;

        case 'test':
            global $TEST;
            if (!$URI[1]) header("location: /user/{$USER->id}/courses/");
            $link = $MOODLE->get_theme_test_link($URI[1]);
            preg_match('/review\.php\?attempt=(\d+)/', $link, $id_match);
            $TEST = $MOODLE->get_test_data($id_match[1]);
            view('test');
        break;

        default:
            header('location: /');
        break;
    }
}

function get_correct_answers() {
    global $URI;

    $users = User::all();
    $result = []; // все полезные ответы в формате ответа $moodle->get_test_data(<id>)
    $required_answers = []; // список ID вопросов без максимальной оценки

    foreach ($users as $u) {

        // Получение рабочего экземпляра Moodle для текущего пользователя
        $data = helper_get_moodle_user($u);
        if (!$data) continue;
        list('user' => $u, 'moodle' => $moodle) = $data;

        // Получение ответов на тест текущего пользователя ($curr_test)
        $test_link = $moodle->get_theme_test_link($URI[1]);
        preg_match('/review\.php\?attempt=(\d+)/', $test_link, $id_match);
        if (!$id_match) continue;
        $curr_test = $moodle->get_test_data($id_match[1]);

        // Первый проход: заполнение $result и $required_answers
        if (!$result) {
            $result = $curr_test;
            foreach ($result as $ans_id => $ans_data) {
                if ($ans_data['grade'] === $ans_data['grade_max'])
                    continue;
                $required_answers[] = $ans_id;
            }
            continue;
        }

        // Выборка недостающих правильных ответов
        $new_keys = array_keys(array_diff_key($curr_test, $result));
        $required_answers = array_merge($required_answers, $new_keys);
        foreach ($required_answers as $index => $ans_id) {
            if (!isset($result[$ans_id]) && isset($curr_test[$ans_id])) {
                $result[$ans_id] = $curr_test[$ans_id];
                if ($curr_test[$ans_id]['grade'] === $curr_test[$ans_id]['grade_max'])
                    unset($required_answers[$index]);
                continue;
            }

            if (!isset($curr_test[$ans_id])) continue;

            if (($curr_test[$ans_id]['grade'] ?? 0) > ($result[$ans_id]['grade'] ?? 0)) {
                $result[$ans_id] = $curr_test[$ans_id];
                if ($curr_test[$ans_id]['grade'] === $curr_test[$ans_id]['grade_max'])
                    unset($required_answers[$index]);
            }
        }

        if (!count($required_answers)) break;
    }

    return $result;
}
