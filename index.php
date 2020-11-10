<?php

namespace App;
error_reporting(E_ALL);

require_once('db.php');
require_once('vendor/simple_html_dom/simple_html_dom.php');
require_once('classes/Moodle.php');
require_once('classes/User.php');
require_once('classes/Api.php');
require_once('helpers.php');
require_once('router.php');

/**************************
 *       Just Do It
***************************/

// $moodle = new Moodle();

// var_export(
    // $moodle
        // ->login('ekaterina', 'Eb1445140//')
        // ->token('51kk9aqngol8b0n85ublp35ntm')
        // ->logout()

        // ->checkToken()
        // ->token()
// );

// echo '<hr><pre>';
// var_export($moodle->get_theme_test_link(21179));
// echo '</pre>';