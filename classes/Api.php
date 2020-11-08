<?php

namespace App;

class Api
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $uri = substr($_SERVER['REQUEST_URI'], 5);
        $class_method = str_replace(['_', '.'], ['', '_'], $uri);
        if (
            method_exists($this, $class_method) &&
            (new \ReflectionMethod($this, $class_method))->isPublic()
        ) {
            $this->$class_method();
        } else {
            $this->json([
                'ok' => false,
                'error' => "Method '{$uri}' does not exists."
            ]);
        }
    }

    public function user_check() {
        // $this->db->
        $this->json(['ok' => true, 'message' => 'В разработке']);
    }

    private function json($data) {
        header('Content-Type: application/json');
        try {
            echo json_encode($data);
        } catch (\Exception $e) {
            echo json_encode([ 'error' => $e ]);
        }
        exit;
    }
}