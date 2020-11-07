<?php

namespace App;

$moodle = new Moodle(['login' => '123', 'password' => '456']);

class Moodle
{
    private $useragent = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.183 Mobile Safari/537.36';
    private $cookies = [];
    private $token = '';

    public function __construct(array $params) {
        if ($params['login'] && $params['password']) {

        } else if ($params['token']) {

        } else {
            throw new \Error('new Moodle(<array>) must have `login` and `password` or `token`');
        }
    }

    private function get_login_token() {
        $body = $this->http('GET', 'http://moodle.dahluniver.ru/login/index.php')->headers;
        preg_match('/name=\"logintoken\" value=\"([\d\w]+)\"/', $body, $matches);
        return $matches[1];
    }

    public function get_token() {
        return $this->token;
    }

    public function login($login, $password): bool {
        $this->token = $this->http('POST', 'http://moodle.dahluniver.ru/login/index.php', [
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
        ])->cookies['MoodleSession'];
        return $this->token;
    }

    public function checkToken($token): bool {
        // $this->http('POST', '', [
        //     CURLOPT_REFERER => ''
        // ]);
        return true;
    }

    private function get() {

    }

    private function post() {

    }

    private function http($method, $url, $options = []) {
        $ch = curl_init();
        $params = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => strtoupper($method) === 'POST',
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
        ) + $options;
        curl_setopt_array($ch, $params);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $this->cookies = array_merge($this->cookies, $cookies);

        $result = (object) [
            'response_code' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
            'headers' => $headers,
            'body' => substr($response, $header_size),
            'cookies' => $cookies
        ];

        curl_close($ch);
        return $result;
    }
}