<?php

namespace App;

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
            $hash = $this::hash($this->path);
            $html = file_get_contents($this::$folder . $hash);
            $cache_btn = '<a href="/clearcache/?page='. $_SERVER['REQUEST_URI'] .'" class="badge badge-success" title="Сбросить кэш" style="position:absolute;top:10px;right:10px;">cached</a>';
            $html = str_replace('<!-- [cached] -->', $cache_btn, $html);
            echo '<!-- from cache -->' . $html;
            exit;
        } else {
            $this::clear($this->path);
            ob_start(array($this, 'sanitize_output'));
        }
    }

    public function save() {
        global $dbh;
        $html = ob_get_clean();
        $html = $this->sanitize_output($html);
        if ($this->disable === false) {
            $hash = $this::hash($this->path);
            file_put_contents($this::$folder . $hash, $html);
            $stmt = $dbh->prepare('INSERT INTO `cache` (`name`, `expires_at`) VALUES (:name, :expires) ON DUPLICATE KEY UPDATE `expires_at` = :expires');
            $stmt->execute([
                ':name' => $this::hash($this->path),
                ':expires' => date('Y-m-d H:i:s', time() + $this->time),
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
            '/<!--(.|\s)*?-->/',// Remove HTML comments
            '/<body>/'
        );
        $replace = array(
            '>',
            '<',
            '\\1',
            '',
            '<body><!-- [cached] -->'
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
        $expires_at = $stmt->fetch(\PDO::FETCH_ASSOC)['expires_at'];
        return intval($expires_at) > time();
    }

}