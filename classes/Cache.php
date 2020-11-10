<?php

namespace App;


class Cache
{
    private $db;
    private $time;
    private $hash;
    private $path = 'cache/';
    private $exists = null;
    private $actual = null;
    private static $disable = false;

    public function __construct($time = 3600) {
        global $dbh;
        $this->db = $dbh;
        $this->time = $time;
        $this->hash = md5($_SERVER['REQUEST_URI']);

        if ($this::$disable === false)
        if ($this->exists() && $this->actual()) {
            $html = "<a href=\"/clearcache/?page={$_SERVER['REQUEST_URI']}\" title=\"Сбросить кэш\" class=\"badge badge-success\" style=\"position:absolute;top:10px;right:10px;\">cached</a>";
            $html .= file_get_contents($this->path . $this->hash);
            die($html);

        } else {
            if ($this->exists())
                unlink($this->path . $this->hash);
            $this->storage($this->hash, null);
            ob_start();
        }
    }

    public static function clear($request_uri) {
        global $dbh;
        $hash = md5($request_uri);
        @unlink('cache/' . $hash);
        $stmt = $dbh->prepare('DELETE FROM `cache` WHERE `name` = ?');
        $stmt->execute([$hash]);
        return true;
    }

    public function disable() {
        $this::$disable = true;
    }

    public static function disableCache() {
        self::$disable = true;
    }

    public function save() {
        $html = ob_get_clean();
        if ($this::$disable === false) {
            file_put_contents($this->path . $this->hash, $html);
            $this->storage($this->hash, date('Y-m-d H:i:s'));
        }
        echo $html;
    }

    private function storage($name, $new_value = '') {
        if (is_null($new_value)) {
            $stmt = $this->db->prepare('DELETE FROM `cache` WHERE `name` = ?');
            $value = $stmt->execute([$name]);
            return $value;

        } else if ($new_value == '') {
            $stmt = $this->db->prepare('SELECT UNIX_TIMESTAMP(`timestamp`) AS `timestamp` FROM `cache` WHERE `name` = ?');
            $stmt->execute([$name]);
            $value = $stmt->fetch(\PDO::FETCH_ASSOC)['timestamp'];
            return $value;

        } else {
            $stmt = $this->db->prepare('INSERT INTO `cache` (`name`, `timestamp`) VALUES (:name, :timestamp) ON DUPLICATE KEY UPDATE `timestamp` = :timestamp');
            $value = $stmt->execute([':name' => $name, ':timestamp' => intval($new_value)]);
            return $value;
        }
    }

    private function exists() {
        if (is_null($this->exists))
            $this->exists = file_exists($this->path . $this->hash);
        return $this->exists;
    }

    private function actual() {
        if (is_null($this->actual)) {
            $hash = $this->hash;
            $created_at = ($this->storage($hash) ?? 0);
            $expires_at = ($created_at + $this->time);
            $this->actual = $created_at !== 0 && $expires_at < time();
        }
        return $this->actual;
    }
}
