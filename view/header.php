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
        <header class="text-center row p-4">
        <?php if ($_SERVER['REQUEST_URI'] === '/'): ?>
            <div class="h1 mx-auto my-4 col-md-4 offset-md-4">Moodle hack</div>
        <?php else: ?>
            <div class="h1 mt-4 col-md-4 offset-md-4">
                <a href="/" title="На главную" style="text-decoration:none!important">
                    Moodle hack
                </a>
            </div>
            <? if ($USER): ?>
            <div class="col-md-4 pt-3 text-right">
                <div class="btn-group mt-3">
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
            </div>
            <? endif; ?>
        <?php endif; ?>
        </header>
