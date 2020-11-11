<?php global $USERS; ?>
<table class="table bg-light">
    <thead>
        <tr>
            <th>Владелец</th>
            <th>Логин</th>
            <th>Токен</th>
            <th>Пароль</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($USERS as $user): ?>
        <tr<?php if (!$user['active']): ?> class="bg-secondary"<?php endif ?>>
            <?php $active = (new App\Moodle($user['token']))->checkToken(); ?>
            <?php if (!$active) App\User::token($user['id'], ''); ?>
            <td><?=$user['name'] ?></td>
            <td><?=$user['login'] ?></td>
            <td><?=($active ? $user['token'] : '') ?></td>
            <td><?=($user['active'] ? 'Да' : 'Нет') ?></td>
            <td><? if ($user['active']): ?><a href="/user/<?=$user['id'] ?>/courses/">Курсы</a><? endif ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
