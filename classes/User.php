<?php

namespace App;

class User
{
    private static $table = 'users';

    public static function all() {
        return self::query('SELECT * FROM `'. self::$table .'`');
    }

    public static function id($id) {
        return self::query('SELECT * FROM `'. self::$table .'` WHERE `id` = ?', [$id]);
    }

    public static function login($login) {
        return self::query('SELECT * FROM `'. self::$table .'` WHERE `login` LIKE ?', [$login]);
    }

    public static function token($id, $token) {
        global $dbh;
        $stmt = $dbh->prepare('UPDATE `'. self::$table .'` SET `token` = ? WHERE `id` = ?');
        $stmt->bindValue(1, $token);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function active($id, $active) {
        global $dbh;
        $stmt = $dbh->prepare('UPDATE `'. self::$table .'` SET `active` = ? WHERE `id` = ?');
        $stmt->bindValue(1, $active, \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    private static function query(string $statement, array $input_parameters = []): object {
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

        return (object) ($result ?: []);
    }
}