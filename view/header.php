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
            <div class="btn-group mb-4">
                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        <?php else: ?>
            <h1 class="mx-auto my-4">Moodle hack</h1>
        <?php endif; ?>
        </div>