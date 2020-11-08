<?php

namespace App;
error_reporting(E_ALL);

require_once('db.php');
require_once('classes/Moodle.php');
require_once('classes/Api.php');
require_once('helpers.php');
require_once('router.php');

/**************************
 *       Just Do It
***************************/

$moodle = new Moodle();
// var_export(
    // $moodle
        // ->login('ekaterina', 'Eb1445140//')
        // ->token('ur11qvikuumbsp9h07fu9dkj06')
        // ->logout()

        // ->checkToken()
        // ->token()
// );
