<?php global $USER; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <? if (defined('TITLE') && TITLE): ?>
    <title><?=TITLE?> - Moodle hack</title>
    <? else: ?>
    <title>Moodle hack</title>
    <? endif; ?>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="navbar navbar-dark bg-dark shadow-sm mb-3">
        <div class="container d-flex justify-content-between">
            <h5 class="mb-0 font-weight-normal">
            <?php if ($_SERVER['REQUEST_URI'] === '/'): ?>
                <span class="navbar-brand">Moodle hack</span>
            <? else: ?>
                <a href="/" class="navbar-brand">Moodle hack</a>
            <? endif ?>
            </h5>
            <div><!-- [cached] --></div>
            <nav class="ml-auto">
                <? if ($USER): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?=$USER->name ?>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/login/<?=$USER->id ?>">Страница профиля</a>
                        <div class="dropdown-divider"></div>
                        <?php $users = App\User::all(); ?>
                        <?php foreach ($users as $u): ?>
                        <?php if ($u['id'] == $USER->id) continue;?>
                        <?php $url = preg_replace('/^\/(user)\/\d+\/(.*)/', '/$1/' . $u['id'] . '/$2', $_SERVER['REQUEST_URI'], 1); ?>
                        <a class="dropdown-item<?php if (!$u['active']): ?> bg-danger<?php endif ?>" href="<?=$url ?>"><?=$u['name'] ?></a>
                        <?php endforeach ?>
                    </div>
                </div>
                <? endif ?>
            </nav>
        </div>
    </header>
    <div class="container">
