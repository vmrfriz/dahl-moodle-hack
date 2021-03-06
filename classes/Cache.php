<?php

namespace App;

use Carbon\Carbon;

class Cache
{
    private static $folder = 'cache/';
    private $time;
    private $path;
    private $disable = false;

    private function __construct(int $time, string $path) {
        $this->time = $time;
        $this->path = $path;
        if ($this::isActualCache($this->path)) {
            die( $this::get($this->path) );
        } else {
            ob_start();
        }
    }

    public function save() {
        global $dbh;
        $html = ob_get_clean();
        $html = $this->sanitize_output($html);
        if ($this->disable === false) {
            $hash = $this::hash($this->path);
            file_put_contents($this::$folder . $hash, $html);
            $stmt = $dbh->prepare('INSERT
                INTO
                    `cache` (`name`, `expires_at`, `created_at`)
                VALUES
                    (:name, FROM_UNIXTIME(:expires), FROM_UNIXTIME(:time))
                ON DUPLICATE KEY UPDATE
                    `expires_at` = FROM_UNIXTIME(:expires),
                    `created_at` = :time
                ');
            $stmt->execute([
                ':name' => $this::hash($this->path),
                ':expires' => time() + $this->time,
                ':time' => time(),
            ]);
        }
        echo '<!-- cache created -->' . $html;
    }

    public function expires(int $time): void {
        $this->time = $time;
    }

    public function disable() {
        $this->disable = true;
    }

    private function sanitize_output($buffer) {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(^ \[cached\] )(.|\s)*?-->/',// Remove HTML comments
            // '/<body>/'
        );
        $replace = array(
            '>',
            '<',
            '\\1',
            '',
            // '<body><!-- [cached] -->'
        );
        $buffer = preg_replace($search, $replace, $buffer);
        return $buffer;
    }

    public static function start(int $time = 3600, string $request_uri = ''): Cache {
        if ($request_uri === '') $request_uri = $_SERVER['REQUEST_URI'];
        $instance = new self($time, $request_uri);
        return $instance;
    }

    public static function clear(string $path): bool {
        global $dbh;
        $hash = self::hash($path);
        $stmt = $dbh->prepare('DELETE FROM `cache` WHERE `name` = ?');
        $result = $stmt->execute([$hash]);
        @unlink(self::$folder . $hash);
        return $result;
    }

    public static function get(string $path): string {
        global $dbh;
        $hash = self::hash($path);
        $html = file_get_contents(self::$folder . $hash);
        $stmt = $dbh->prepare('SELECT UNIX_TIMESTAMP(`created_at`) AS `created_at` FROM `cache` WHERE `name` = ?');
        $stmt->execute([$hash]);
        $created_at = (int) $stmt->fetch(\PDO::FETCH_ASSOC)['created_at'];
        $created_at = Carbon::now()->locale('ru')->longAbsoluteDiffForHumans(new Carbon($created_at));
        $cache_btn = '<a href="/clearcache/?page='. $_SERVER['REQUEST_URI'] .'" class="btn btn-sm btn-outline-secondary" title="Страница выглядела так '. $created_at .' назад. Нажмите, чтобы обновить">&#8635; '. $created_at .' назад</a>';
        $html = '<!-- from cache -->' . str_replace('<!-- [cached] -->', $cache_btn, $html);
        return $html;
    }

    public static function isCached(string $path): bool {
        return self::cache_exists($path);
    }

    public static function isActualCache(string $path): bool {
        $exists = self::cache_exists($path);
        $actual = self::cache_actual($path);
        return $exists && $actual;
    }

    private static function hash(string $path): string {
        return md5($path);
    }

    private static function cache_exists(string $path): bool {
        $hash = self::hash($path);
        $isExists = file_exists(self::$folder . $hash);
        return $isExists;
    }

    private static function cache_actual(string $path): bool {
        global $dbh;
        $hash = self::hash($path);
        $stmt = $dbh->prepare('SELECT UNIX_TIMESTAMP(`expires_at`) AS `expires_at` FROM `cache` WHERE `name` = ?');
        $stmt->execute([$hash]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) return false;
        $expires_at = $result['expires_at'];
        return intval($expires_at) > time();
    }

}