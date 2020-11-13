<?php

namespace App;

use Exception;

class Moodle
{
    private $useragent = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Mobile Safari/537.36';
    private $cookies = [];

    public function __construct($token = '') {
        if (!empty($token)) $this->token($token);
    }

    public function __toString(): string {
        return $this->token();
    }

    public function __invoke($token = '') {
        return $this->token($token);
    }

    /**
     * Проверка доступности сайта
     *
     * @return boolean
     */
    public static function isDown(): bool {
        try {
            (new self())->http('GET', 'http://moodle.dahluniver.ru/');
            return false;
        } catch (\Error $e) {
            return true;
        }
    }

    /**
     * set/get Токен авторизации
     *
     * @param string $token Токен авторизации
     * @return mixed Если передан токен, возвращает экземпляр класса; Если ничего не передано, возвращает используемый токен
     */
    public function token(string $token = '') {
        // set
        if (!empty($token)) {
            $this->cookies['MoodleSession'] = $token;
            return $this;
        }
        // get
        return $this->cookies['MoodleSession'] ?? '';
    }

    /**
     * Проверка валидности токена авторизации
     *
     * @return boolean Валидность - true/false
     */
    public function checkToken(): bool {
        if (!$this->token()) return false;
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/login/index.php')->body;
        $logged_in = strpos($body, 'logout.php') !== false;
        return $logged_in;
    }

    /**
     * Получение токена авторизации от сайта. Хранится в $moodle->token()
     *
     * @param string $login     Логин для входа в moodle.dahluniver.ru
     * @param string $password  Пароль для входа в moodle.dahluniver.ru
     * @return self
     */
    public function login(string $login, string $password): self {
        $this->http('POST', 'http://moodle.dahluniver.ru/login/index.php', [
            CURLOPT_REFERER => 'http://moodle.dahluniver.ru/login/index.php',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'anchor' => '',
                'logintoken' => $this->get_login_token(),
                'username' => $login,
                'password' => $password,
            ]),
        ]);
        return $this;
    }

    /**
     * "Выход" из сайта (пометить токен устаревшим)
     *
     * @return self
     */
    public function logout(): self {
        $this->http('GET', 'http://moodle.dahluniver.ru/login/logout.php?sesskey=' . $this->get_logout_token(), [
            CURLOPT_REFERER => 'http://moodle.dahluniver.ru/my/',
        ]);
        return $this;
    }

    /**
     * Получение списка курсов
     *
     * @return array
     */
    public function get_courses(): array {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/my/')->body;
        $links = str_get_html($body)->find('ul#dropdownmain-navigation0 li a.dropdown-item');
        $data = array();
        foreach ($links as $link) {
            preg_match('/view\.php\?id=(\d+)/', $link->href, $id_match);
            $data[] = [
                'id' => $id_match[1],
                'href' => $link->href,
                'title' => $link->title
            ];
        }
        return $data;
    }

    /**
     * Получение результатов тестов по id курса
     *
     * @param integer $course_id
     * @return array
     */
    public function get_course_themes(int $course_id): array {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/grade/report/user/index.php?id=' . $course_id)->body;
        $rows = str_get_html($body)->find('table tbody tr');
        $data = array('TASKS' => [], 'TESTS' => []);

        foreach ($rows as $row) {

            $img = $row->find('th a img', 0);
			if (!$img) continue;

            $img_alt = mb_strtolower($img->alt);
            if (!in_array($img_alt, ['тест', 'задание'])) continue;

            $link = $row->find('th a', 0);
            $percentage = floatval(preg_replace(['/[^\d,]/', '/,/'], ['', '.'], $row->find('td.column-percentage', 0)->plaintext));
            $grade = floatval(preg_replace(['/[^\d,]/', '/,/'], ['', '.'], $row->find('td.column-grade', 0)->plaintext));
            $range = $row->find('td.column-range', 0)->plaintext;
            preg_match('/\Wid=(\d+)/', $link->href, $id_match);
            $section = $img_alt == 'тест' ? 'TESTS' : 'TASKS';
            $data[$section][] = [
                'id' => intval($id_match[1]),
                'title' => $link->plaintext,
                'href' => $link->href,
                'grade' => $grade,
                'range' => $range,
                'percentage' => $percentage,
            ];
        }
        return $data;
    }

    /**
     * Проверка, выполнен ли тест у пользователя
     *
     * @param  integer  $id  ID теста
     * @return boolean       true - выполнен, false - не выполнен
     */
	public function check_complete_test(int $id): bool {
		$body = $this->http('GET', 'http://moodle.dahluniver.ru/mod/quiz/view.php?id=' . $id)->body;
		$is_complete = (bool) str_get_html($body)->find('table a', 0);
		return $is_complete;
    }

    /**
     * Возвращает массив с названием теста, свойствами вопросов и выбранными ответами
     *
     * @param integer $id       ID темы
     * @return array            Массив со свойствами вопросов и выбранными ответами
     */
    public function get_test_data(int $id): array {
		list('id' => $test_id, 'title' => $title) = $this->get_theme_test_redirect($id);
		if ($test_id === false) return [
			'title' => $title,
			'questions' => [],
		];

        $url = 'http://moodle.dahluniver.ru/mod/quiz/review.php?attempt=' . $test_id;
        $body = $this->http('GET', $url)->body;

        $result = [];
        $questions = str_get_html($body)->find('form.questionflagsaveform div.que');
        foreach ($questions as $question) {
            $question_id = $question->id;
            $question_order = trim(strrchr($question_id, '-'), '- ');
            $question_classes = $question->class;
            $is_answered = strpos($question_classes, 'notanswered') === false;
            $is_multiple = strpos($question_classes, 'multichoice') !== false;
            $is_match = strpos($question_classes, 'match') !== false;

            $sGrade = $question->find('div.info div.grade', 0)->plaintext;
            $sGrade = str_replace(',', '.', $sGrade);
            $aGrade = array_values(
                array_filter(
                    explode(' ', $sGrade),
                    function ($val) {
                        return is_numeric($val);
                    }
                )
            );

            $question_text = trim($question->find('div.content div.qtext', 0)->plaintext);

            $selected_answers = [];
            $selector = 'div.ablock .answer ' . ($is_match ? 'tr' : 'div');
            $answers = $question->find($selector);
            foreach ($answers as $answer) {
                if ($is_match) {
                    $selected_answers[] = trim($answer->find('td', 0)->plaintext) . ' = ' .
                        trim($answer->find('select option[selected]', 0)->plaintext);
                } else {
                    if ($answer->find('input', 0)->checked) {
                        $selected_answers[] = trim($answer->plaintext);
                    }
                }
            }

            $result[md5($question_text)] = [
                'id'          => $question_id,
                'order'       => $question_order,
                'is_answered' => $is_answered,
                'is_multiple' => $is_multiple,
                'is_match'    => $is_match,
                'grade'       => ($is_answered ? floatval($aGrade[0]) : 0),
                'grade_max'   => floatval(end($aGrade)),
                'question'    => $question_text,
                'selected_answers' => $selected_answers,
            ];
        }

        return [
			'title' => $title,
			'questions' => $result,
		];
    }

    /**
     * Получение ID теста и название темы
     *
     * @param integer $test_id      ID темы
     * @return mixed                Ссылка на результаты теста или false, если тест не выполнен
     */
    private function get_theme_test_redirect(int $test_id): array {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/mod/quiz/view.php?id=' . $test_id)->body;
		$dom = str_get_html($body);
		$dom_title = $dom->find('h2', 0);
		$title = $dom_title ? trim($dom_title->plaintext) : '';
        $id = $dom->find('table a', 0);
		if ($id) {
			preg_match('/review\.php\?attempt=(\d+)/', $id, $id_match);
			$id = isset($id_match[1]) ? $id_match[1] : false;
		} else {
			$id = false;
		}
        return [
			'id' => $id,
			'title' => $title,
		];
    }

    /**
     * Получить одноразовый токен для входа по логину+паролю
     *
     * @return string Параметр logintoken формы авторизации
     */
    private function get_login_token(): string {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/login/index.php')->body;
        preg_match('/name=\"logintoken\" value=\"([\d\w]+)\"/', $body, $matches);
        return $matches[1];
    }

    /**
     * Получение токена выхода (для logout.php)
     *
     * @return Параметр sesskey для запроса на выход
     */
    private function get_logout_token(): string {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/my/')->body;
        preg_match('/\:\/\/moodle.dahluniver.ru\/login\/logout\.php\?sesskey=([\d\w]+)/', $body, $matches);
        return $matches ? $matches[1] : false;
    }

    /**
     * Отправка curl запроса
     *
     * @param string $method    HTTP-метод
     * @param string $url       URL-адрес для curl запроса
     * @param array  $options   Дополнительные параметры для функции curl_setopt
     * @return object           Объект с параметрами response_code, headers и body
     */
    private function http(string $method, string $url, array $options = []): object {
        $ch = curl_init();
        $params = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => strtoupper($method) === 'POST',
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 7,
            CURLOPT_COOKIE => $this->cookies ? http_build_query($this->cookies, '', '; ') : '',
        ) + $options;
        curl_setopt_array($ch, $params);
        $response = curl_exec($ch);
        if ($response == false) {
            throw new \Error("Moodle->http('{$method}', '{$url}', <array>) --> curl_exec returns false");
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $this->updateCookies($headers);

        $result = (object) [
            'response_code' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
            'headers' => $headers,
            'body' => substr($response, $header_size),
        ];

        curl_close($ch);
        return $result;
    }

    /**
     * Обновление $this->cookies из заголовков ответа
     *
     * @param string $headers Заголовки ответа curl
     * @return void
     */
    private function updateCookies($headers): void {
        preg_match_all('/^Set-Cookie:\s*([^;]*)(.{2,}?)(expires=[^;$]+)?/mi', $headers, $matches);
        $cookies = array();
        foreach($matches[1] as $index => $cookie_string) {
            if (!empty($matches[3][$index])) {
                $datetime = strtotime(substr($matches[3][$index], 8));
                if ($datetime < time()) {
                    $cookie_key = explode('=', $matches[1][$index])[0];
                    unset($this->cookies[$cookie_key]);
                    continue;
                }
            }
            parse_str($cookie_string, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->cookies = array_merge($this->cookies, $cookies);
    }
}