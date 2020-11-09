<?php

namespace App;

class User
{
    public static function all() {
        return self::query('SELECT * FROM `users`');
    }

    public static function id($id) {
        return self::query('SELECT * FROM `users` WHERE `id` = ?', [$id]);
    }

    public static function login($login) {
        return self::query('SELECT * FROM `users` WHERE `login` LIKE ?', [$login]);
    }

    public static function token($token) {
        return self::query('SELECT * FROM `users` WHERE `token` LIKE ?', [$token]);
    }

    private static function query(string $statement, array $input_parameters = []): array {
        global $dbh;

        if ($input_parameters) {
            $stmt = $dbh->prepare($statement);
            $isAssoc = array_keys($input_parameters) !== range(0, count($input_parameters) - 1);
            foreach ($input_parameters as $k => $v) {
                if (!$isAssoc) $k++;
                $stmt->bindValue($k, $v, is_numeric($v) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $stmt->execute();
        } else {
            $stmt = $dbh->query($statement);
        }

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($result) === 1) $result = $result[0];

        return $result ?: [];
    }
}