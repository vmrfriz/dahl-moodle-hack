<?php

namespace App;

error_reporting(E_ALL);
ini_set('short_open_tag', 'On');
// ini_set('error_log', 'php-errors.log');
// ini_set('html_errors', true);
// ini_set('display_startup_errors', true);
// ini_set('display_errors', true);

include('vendor/autoload.php');
require_once('db.php');
require_once('vendor/simple_html_dom/simple_html_dom.php');
require_once('classes/Moodle.php');
require_once('classes/Cache.php');
require_once('classes/User.php');
require_once('classes/Api.php');
require_once('helpers.php');
require_once('router.php');
