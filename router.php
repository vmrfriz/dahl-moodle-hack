<?php

// namespace App;
use App\User;
use App\Moodle;
use App\Cache;
use App\Api;

$uri = $_SERVER['REQUEST_URI'];
// Cache::clear($uri);

$URI = array_values(array_filter(explode('/', $uri)));
if (Cache::isActualCache($uri)) {
    echo Cache::get($uri);
    exit;
}

if (Moodle::isDown()) {
    if (Cache::isCached($uri))
        die(Cache::get($uri));
    else {
        define('TITLE', ':(');
        die(view('down'));
    }
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
        define('TITLE', $USER->name);
        view('login');
    break;

    case 'user':
        $cache = Cache::start(86400 * 30);
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
        define('TITLE', $TEST['title']);
        view('test');
        $cache->save();
    break;

    case 'completed-tests':
        $cache = Cache::start(86400 * 30);
        $users = User::all();
        $users_complete_test = [];
        foreach ($users as $u) {
            $data = helper_get_moodle_user($u);
            if (!$data) continue;
            list('user' => $u, 'moodle' => $moodle) = $data;

            $is_complete = $moodle->check_complete_test($URI[1]);
            if ($is_complete) $users_complete_test[$u['id']] = $u['name'];
        }
        view('completed-tests');
        $cache->save();
    break;

	case 'task':
		$cache = Cache::start(86400 * 30);
		$title = null;
		$users = User::all();
        $completed_tasks = [];
        foreach ($users as $u) {
            $data = helper_get_moodle_user($u);
            if (!$data) continue;
            list('user' => $u, 'moodle' => $moodle) = $data;

            $task = $moodle->get_task_data($URI[1]);
			if (!isset($task['files'])) continue;
			if (!$title) $title = $task['title'];
            $completed_tasks[] = [
				'user' => $u,
				'task' => $task
			];
        }
		view('completed-tasks');
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
        $cache = Cache::start(300);
        $USERS = User::all();
        define('TITLE', 'Пользователи');
        view('index');
        $cache->save();
    break;
}

function user_methods($URI) {
    global $MOODLE, $USER, $cache;

	if ($URI[0] !== 'courses' && !$URI[1]) {
		header("location: /user/{$USER->id}/courses/");
	}

    switch ($URI[0]) {
        case 'courses':
            global $COURSES;
            $COURSES = $MOODLE->get_courses();
            define('TITLE', $COURSES['title'] ?? '');
            view('courses');
        break;

        case 'themes':
            global $DATA, $cache;
            $cache->expires(3600);
            $DATA = $MOODLE->get_course_themes($URI[1]);
            define('TITLE', $DATA['title'] ?? '');
            view('themes');
        break;

        case 'test':
            global $TEST;
            $TEST = $MOODLE->get_test_data($URI[1]);
            define('TITLE', $TEST['title'] ?? '');
            view('test');
        break;

        case 'task':
            global $TASK;
            $TASK = $MOODLE->get_task_data($URI[1]);
            define('TITLE', $TASK['title'] ?? '');
            view('task');
        break;

        case 'download':
            $cache->disable();
            $MOODLE->get_page($_GET['url']);
        break;

        default:
            header('location: /');
        break;
    }
}

function get_correct_answers() {
    global $URI;

    $users = User::all();
    /**
     * @var array $best_answers Все полезные ответы
     * array(
     *   'md5(Вопрос теста)' => array(
     *     'id'               => (string) ID вопроса
     *     'order'            => (int) Порядковый номер вопроса
     *     'is_answered'      => (bool) Есть ответ
     *     'is_multiple'      => (bool) Возможно несколько ответов (checkbox)
     *     'is_match'         => (bool) Это совпадения <select>
     *     'is_essay'         => (bool) Это эссе <textarea>
     *     'grade'            => (int) Оценка за текущий ответ
     *     'grade_max'        => (int) Максимальная оценка за текущий ответ
     *     'question'         => (string) Текст вопроса
     *     'selected_answers' => (array) Массив ответов
     *   ),
     *   'md5(Вопрос теста2)' => array(
     *     ...
     *   )
     * )
     */
    $best_answers = [];

    /**
     * @var array Массив вопросов, на которые ещё нет 100% верных ответов
     * array(
     *   'md5(Вопрос теста)' => array(
     *     'id пользователя' => array(
     *       'id'               => (string) ID вопроса
     *       'order'            => (int) Порядковый номер вопроса
     *       'is_answered'      => (bool) Есть ответ
     *       'is_multiple'      => (bool) Возможно несколько ответов (checkbox)
     *       'is_match'         => (bool) Это совпадения <select>
     *       'is_essay'         => (bool) Это эссе <textarea>
     *       'grade'            => (int) Оценка за текущий ответ
     *       'grade_max'        => (int) Максимальная оценка за текущий ответ
     *       'question'         => (string) Текст вопроса
     *       'selected_answers' => (array) Массив ответов
     *     ),
     *     'id пользователя2' => array(
     *       ...
     *     ),
     *   )
     * )
     */
    $wrong_answers = []; // список ID вопросов без максимальной оценки

    // Название теста
	$title = '';

    /**
     * Сбор результатов теста у всех пользователей
     */
    foreach ($users as $u) {

        // Получение рабочего экземпляра Moodle для текущего пользователя
        $data = helper_get_moodle_user($u);
        if (!$data) continue;
        list('user' => $u, 'moodle' => $moodle) = $data;
        // if (!$u['active']) continue;
        // $moodle = new App\Moodle($u['token']);

        // Получение ответов на тест текущего пользователя ($curr_test)
        list(
			'title' => $title,
			'questions' => $curr_test
		) = $moodle->get_test_data($URI[1]);

        // Первый проход: заполнение $best_answers и $wrong_answers
        if ($best_answers === []) {
            $best_answers = $curr_test;
            foreach ($best_answers as $ans_id => $ans_data) {
                if ($ans_data['grade'] === $ans_data['grade_max'])
                    continue;
                if (!is_array($wrong_answers[$ans_id] ?? false))
                    $wrong_answers[$ans_id] = [];
                $wrong_answers[$ans_id][ $u['id'] ] = $ans_data['selected_answers'];
            }
            continue;
        }

        /**
         * Если у текущего пользователя есть вопросы, которых ещё нет в списке,
         * проверяем их правильность и добавляем в $best_answers и $wrong_answers
         */
        $new_keys = array_keys(array_diff_key($curr_test, $best_answers));
        foreach ($new_keys as $new_key) {
            $best_answers[$new_key] = $curr_test[$new_key];
            if ($curr_test[$new_key]['grade'] !== $curr_test[$new_key]['grade_max']) {
                $wrong_answers[$ans_id][ $u['id'] ] = $curr_test[$new_key];
            }
        }

        /**
         * Ищем лучшие ответы, чем содержатся во $wrong_answers
         */
        if ($wrong_answers)
        foreach ($wrong_answers as $ans_id => $ans_data) {
            $curr = &$curr_test[$ans_id] ?? false;
            $best = &$best_answers[$ans_id];

            if (!$curr) continue;
            if (($best['grade'] ?? 0) === ($best['grade_max'] ?? 0)) continue;
            if (($curr['grade'] ?? 0) === ($curr['grade_max'] ?? 0)) {
                $best = $curr;
                unset($wrong_answers[$ans_id]);
                continue;
            }
            if (($curr['grade'] ?? 0) > ($best['grade'] ?? 0)) {
                $best = $curr;
            }

            $wrong_answers[$ans_id][ $u['id'] ] = $curr;
        }
    }

    /**
     * @var array Список неправильных ответов с вероятностью неправильности
     */
    $exactly_wrong = [];
    foreach ($wrong_answers as $ans_id => $users) {

        /** @var int Количество правильных ответов в тесте */
        $correct_count = 0;
        /** @var int Точность определения количества правильных ответов в тесте в процентах */
        $exactly_correct = 0;

        foreach ($users as $user_id => $ans_data) {

            // Пропускаем эссе
            if ($ans_data['is_essay']) {
                unset($wrong_answers[$ans_id]);
                break;
            }

            // Удаляем ответ пользователя из очереди, если его ответ пуст
            if (($ans_data['answered'] ?? false) === false) {
                unset($users[$user_id]);
                continue;
            }

            // Заполняем точно неправильный ответ, если к этому вопросу он пуст
            if (!($exactly_wrong[$ans_id] ?? true)) {
                $exactly_wrong[$ans_id] = $ans_data;
                $exactly_wrong[$ans_id]['wrong_answers'] = [];
                unset($exactly_wrong[$ans_id]['selected_answers']);
            }

            $answers = &$ans_data['selected_answers'];
            $count = count($answers);
            $exactly_wrong_answers = &$exactly_wrong[$ans_id]['wrong_answers'];

            // Ответы <select>
            if ($ans_data['is_match']) {


            // Ответы checkbox
            } else if ($ans_data['is_multiple']) {
                if ($ans_data['grade'] === 0) {
                    // Добавить все ответы в точно неверные
                    foreach ($answers as $answer) {
                        $exactly_wrong_answer = array(
                            'answer' => $answer,
                            'chance' => 0
                        );
                        $ans_hash = md5($answer);
                        if (!isset($exactly_wrong_answers[$ans_hash])) {
                            $exactly_wrong_answers[$ans_hash] = $exactly_wrong_answer;
                        }
                    }
                }

                if (
                    $exactly_correct < 100 &&
                    $count === 1 &&
                    $ans_data['grade'] > 0
                ) {
                    $correct_count = round($ans_data['grade_max'] / $ans_data['grade']);
                    $exactly_correct = 100;
                }


            // Ответы radio
            } else {
                $exactly_wrong_answer = array(
                    'answer' => $answers[0],
                    'chance' => 0
                );
                $ans_hash = md5($answers[0]);
                if (!isset($exactly_wrong_answers[$ans_hash])) {
                    $exactly_wrong_answers[$ans_hash] = $exactly_wrong_answer;
                }
            }
        }
        if ($wrong_answers[$ans_id] ?? false) break;
    }

    return [
		'title' => $title,
        'questions' => $best_answers,
        'wrong' => $exactly_wrong
	];
}
