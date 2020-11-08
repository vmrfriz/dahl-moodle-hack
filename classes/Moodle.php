<?php

namespace App;

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
        return $this->cookies['MoodleSession'];
    }

    /**
     * Проверка валидности токена авторизации
     *
     * @return boolean Валидность - true/false
     */
    public function checkToken(): bool {
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
        $data = $this->http('POST', 'http://moodle.dahluniver.ru/login/index.php', [
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
    public function get_course_tests(int $course_id): array {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/grade/report/user/index.php?id=' . $course_id)->body;
        $rows = str_get_html($body)->find('table tbody tr');
        $data = array();
        foreach ($rows as $row) {
            $img = $row->find('th a img', 0);
            if (!$img || mb_strtolower($img->alt) !== 'тест') continue;
            $link = $row->find('th a', 0);
            $percentage = floatval(preg_replace(['/[^\d,]/', '/,/'], ['', '.'], $row->find('td.column-percentage', 0)->plaintext));
            $grade = floatval(preg_replace(['/[^\d,]/', '/,/'], ['', '.'], $row->find('td.column-grade', 0)->plaintext));
            $range = $row->find('td.column-range', 0)->plaintext;
            preg_match('/\Wid=(\d+)/', $link->href, $id_match);
            $data[] = [
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