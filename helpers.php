<?php

function view($name) {
    include('view/header.php');
    include("view/{$name}.php");
    include('view/footer.php');
}