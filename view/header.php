<?php global $USER; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moodle hack</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="text-center">
        <?php if ($USER): ?>
            <h1 class="mt-4">
                <a href="/" title="На главную" style="text-decoration:none!important">
                    Moodle hack
                </a>
            </h1>
            <a href="/login/<?=$USER->id ?>" class="mb-4 badge badge-secondary"><?=$USER->name ?></a>
        <?php else: ?>
            <h1 class="mx-auto my-4">Moodle hack</h1>
        <?php endif; ?>
        </div>